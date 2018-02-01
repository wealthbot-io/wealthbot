require File.expand_path(File.join(File.dirname(__FILE__), '..', 'mongodb'))
Puppet::Type.type(:mongodb_shard).provide(:mongo, parent: Puppet::Provider::Mongodb) do
  desc 'Manage mongodb sharding.'

  confine true:     begin
      require 'json'
      true
    rescue LoadError
      false
    end

  mk_resource_methods

  commands mongo: 'mongo'

  def initialize(value = {})
    super(value)
    @property_flush = {}
  end

  def destroy
    @property_flush[:ensure] = :absent
  end

  def create
    @property_flush[:ensure] = :present
    @property_flush[:member] = resource.should(:member)
    @property_flush[:keys]   = resource.should(:keys)
  end

  def sh_addshard(member)
    mongo_command("sh.addShard(\"#{member}\")", '127.0.0.1:27017')
  end

  def sh_shardcollection(shard_key)
    collection = shard_key.keys.first
    keys = shard_key.values.first.map do |key, value|
      "\"#{key}\": #{value}"
    end

    mongo_command("sh.shardCollection(\"#{collection}\", {#{keys.join(',')}})", '127.0.0.1:27017')
  end

  def sh_enablesharding(member)
    mongo_command("sh.enableSharding(\"#{member}\")", '127.0.0.1:27017')
  end

  def self.prefetch(resources)
    instances.each do |prov|
      resource = resources[prov.name]
      resource.provider = prov if resource
    end
  end

  def flush
    set_member
    @property_hash = self.class.shard_properties(resource[:name])
  end

  def set_member
    if @property_flush[:ensure] == :absent
      # a shard can't be removed easily at this time
      return
    end

    return unless @property_flush[:ensure] == :present && @property_hash[:ensure] != :present

    Puppet.debug "Adding the shard #{name}"
    output = sh_addshard(@property_flush[:member])
    raise Puppet::Error, "sh.addShard() failed for shard #{name}: #{output['errmsg']}" if output['ok'].zero?
    output = sh_enablesharding(name)
    raise Puppet::Error, "sh.enableSharding() failed for shard #{name}: #{output['errmsg']}" if output['ok'].zero?

    return unless @property_flush[:keys]

    @property_flush[:keys].each do |key|
      output = sh_shardcollection(key)
      raise Puppet::Error, "sh.shardCollection() failed for shard #{name}: #{output['errmsg']}" if output['ok'].zero?
    end
  end

  def self.instances
    shards_properties.map do |shard|
      new shard
    end
  end

  def self.shard_collection_details(obj, shard_name)
    collection_array = []
    obj.each do |database|
      next unless database['_id'].eql?(shard_name) && !database['shards'].nil?
      collection_array = database['shards'].map do |collection|
        { collection.keys.first => collection.values.first['shardkey'] }
      end
    end
    collection_array
  end

  def self.shard_properties(shard)
    properties = {}
    output = mongo_command('sh.status()')
    output['shards'].each do |s|
      next unless s['_id'] == shard
      properties = {
        name: s['_id'],
        ensure: :present,
        member: s['host'],
        keys: shard_collection_details(output['databases'], s['_id']),
        provider: :mongo
      }
    end
    properties
  end

  def self.shards_properties
    output = mongo_command('sh.status()')
    properties = if !output['shards'].empty?
                   output['shards'].map do |shard|
                     {
                       name: shard['_id'],
                       ensure: :present,
                       member: shard['host'],
                       keys: shard_collection_details(output['databases'], shard['_id']),
                       provider: :mongo
                     }
                   end
                 else
                   []
                 end
    Puppet.debug("MongoDB shard properties: #{properties.inspect}")
    properties
  end

  def exists?
    @property_hash[:ensure] == :present
  end

  def mongo_command(command, host, retries = 4)
    self.class.mongo_command(command, host, retries)
  end

  def self.mongo_command(command, host = nil, _retries = 4)
    # Allow waiting for mongod to become ready
    # Wait for 2 seconds initially and double the delay at each retry
    wait = 2
    begin
      args = []
      args << '--quiet'
      args << ['--host', host] if host
      args << ['--eval', "printjson(#{command})"]
      output = mongo(args.flatten)
    rescue Puppet::ExecutionFailure => e
      raise unless e =~ %r{Error: couldn't connect to server} && wait <= (2**max_wait)

      info("Waiting #{wait} seconds for mongod to become available")
      sleep wait
      wait *= 2
      retry
    end

    # NOTE (spredzy) : sh.status()
    # does not return a json stream
    # we jsonify it so it is easier
    # to parse and deal with it
    if command == 'sh.status()'
      myarr = output.split("\n")
      myarr.shift
      myarr.pop
      myarr.pop
      final_stream = []
      prev_line = nil
      in_shard_list = 0
      in_chunk = 0
      myarr.each do |line|
        line.gsub!(%r{sharding version:}, '{ "sharding version":')
        line.gsub!(%r{shards:}, ',"shards":[')
        line.gsub!(%r{databases:}, '], "databases":[')
        line.gsub!(%r{"clusterId" : ObjectId\("(.*)"\)}, '"clusterId" : "ObjectId(\'\1\')"')
        line.gsub!(%r{\{  "_id" :}, ',{  "_id" :') if %r{_id} =~ prev_line
        # Modification for shard
        line = '' if line =~ %r{on :.*Timestamp}
        if line =~ %r{_id} && in_shard_list == 1
          in_shard_list = 0
          last_line = final_stream.pop.strip
          proper_line = "#{last_line}]},"
          final_stream << proper_line
        end
        if line =~ %r{shard key} && in_shard_list == 1
          shard_name = final_stream.pop.strip
          proper_line = ",{\"#{shard_name}\":"
          final_stream << proper_line
        end
        if line =~ %r{shard key} && in_shard_list.zero?
          in_shard_list = 1
          shard_name = final_stream.pop.strip
          id_line = "#{final_stream.pop[0..-2]}, \"shards\": "
          proper_line = "[{\"#{shard_name}\":"
          final_stream << id_line
          final_stream << proper_line
        end
        if in_chunk == 1
          in_chunk = 0
          line = "\"#{line.strip}\"}}"
        end
        in_chunk = 1 if line =~ %r{chunks} && in_chunk.zero?
        line.gsub!(%r{shard key}, '{"shard key"')
        line.gsub!(%r{chunks}, ',"chunks"')
        final_stream << line unless line.empty?
        prev_line = line
      end
      final_stream << ' ] }' if in_shard_list == 1
      final_stream << ' ] }'
      output = final_stream.join("\n")
    end

    # Hack to avoid non-json empty sets
    output = '{}' if output == "null\n"
    output.gsub!(%r{\s*}, '')
    JSON.parse(output)
  end
end
