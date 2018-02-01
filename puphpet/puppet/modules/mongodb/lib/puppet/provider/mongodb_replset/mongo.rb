#
# Author: Francois Charlier <francois.charlier@enovance.com>
#

require File.expand_path(File.join(File.dirname(__FILE__), '..', 'mongodb'))
Puppet::Type.type(:mongodb_replset).provide(:mongo, parent: Puppet::Provider::Mongodb) do
  desc 'Manage hosts members for a replicaset.'

  confine true:     begin
      require 'json'
      true
    rescue LoadError
      false
    end

  mk_resource_methods

  def initialize(resource = {})
    super(resource)
    @property_flush = {}
  end

  def members=(hosts)
    @property_flush[:members] = hosts
  end

  def self.instances
    instance = replset_properties
    if instance
      # There can only be one replset per node
      [new(instance)]
    else
      []
    end
  end

  def self.prefetch(resources)
    instances.each do |prov|
      resource = resources[prov.name]
      resource.provider = prov if resource
    end
  end

  def exists?
    @property_hash[:ensure] == :present
  end

  def create
    @property_flush[:ensure] = :present
    @property_flush[:members] = resource.should(:members)
  end

  def destroy
    @property_flush[:ensure] = :absent
  end

  def flush
    set_members
    @property_hash = self.class.replset_properties
  end

  private

  def db_ismaster(host)
    mongo_command('db.isMaster()', host)
  end

  def rs_initiate(conf, master)
    if auth_enabled && auth_enabled != 'disabled'
      mongo_command("rs.initiate(#{conf})", initialize_host)
    else
      mongo_command("rs.initiate(#{conf})", master)
    end
  end

  def rs_status(host)
    mongo_command('rs.status()', host)
  end

  def rs_add(host, master)
    mongo_command("rs.add('#{host}')", master)
  end

  def rs_remove(host, master)
    mongo_command("rs.remove('#{host}')", master)
  end

  def rs_arbiter
    @resource[:arbiter]
  end

  def rs_add_arbiter(host, master)
    mongo_command("rs.addArb('#{host}')", master)
  end

  def auth_enabled
    self.class.auth_enabled
  end

  def initialize_host
    @resource[:initialize_host]
  end

  def master_host(hosts)
    hosts.each do |host|
      status = db_ismaster(host)
      return status['primary'] if status.key?('primary')
    end
    false
  end

  def self.replset_properties
    conn_string = conn_string
    begin
      output = mongo_command('rs.conf()', conn_string)
    rescue Puppet::ExecutionFailure
      output = {}
    end
    if output['members']
      members = output['members'].map do |val|
        val['host']
      end
      props = {
        name: output['_id'],
        ensure: :present,
        members: members,
        provider: :mongo
      }
    else
      props = nil
    end
    Puppet.debug("MongoDB replset properties: #{props.inspect}")
    props
  end

  def alive_members(hosts)
    alive = []
    hosts.select do |host|
      begin
        Puppet.debug "Checking replicaset member #{host} ..."
        status = rs_status(host)
        if status.key?('errmsg') && status['errmsg'] == 'not running with --replSet'
          raise Puppet::Error, "Can't configure replicaset #{name}, host #{host} is not supposed to be part of a replicaset."
        end

        if auth_enabled && status.key?('errmsg') && (status['errmsg'].include?('unauthorized') || status['errmsg'].include?('not authorized'))
          Puppet.warning "Host #{host} is available, but you are unauthorized because of authentication is enabled: #{auth_enabled}"
          alive.push(host)
        end

        if status.key?('set')
          if status['set'] != name
            raise Puppet::Error, "Can't configure replicaset #{name}, host #{host} is already part of another replicaset."
          end

          # This node is alive and supposed to be a member of our set
          Puppet.debug "Host #{host} is available for replset #{status['set']}"
          alive.push(host)
        elsif status.key?('info')
          Puppet.debug "Host #{host} is alive but unconfigured: #{status['info']}"
          alive.push(host)
        end
      rescue Puppet::ExecutionFailure
        Puppet.warning "Can't connect to replicaset member #{host}."
      end
    end
    alive
  end

  def set_members
    if @property_flush[:ensure] == :absent
      # TODO: I don't know how to remove a node from a replset; unimplemented
      # Puppet.debug "Removing all members from replset #{self.name}"
      # @property_hash[:members].collect do |member|
      #  rs_remove(member, master_host(@property_hash[:members]))
      # end
      return
    end

    if !@property_flush[:members].empty?
      # Find the alive members so we don't try to add dead members to the replset
      alive_hosts = alive_members(@property_flush[:members])
      dead_hosts  = @property_flush[:members] - alive_hosts
      Puppet.debug "Alive members: #{alive_hosts.inspect}"
      Puppet.debug "Dead members: #{dead_hosts.inspect}" unless dead_hosts.empty?
      raise Puppet::Error, "Can't connect to any member of replicaset #{name}." if alive_hosts.empty?
    else
      alive_hosts = []
    end

    if @property_flush[:ensure] == :present && @property_hash[:ensure] != :present && !master_host(alive_hosts)
      Puppet.debug "Initializing the replset #{name}"

      # Create a replset configuration
      hostconf = alive_hosts.each_with_index.map do |host, id|
        arbiter_conf = ''
        arbiter_conf = ', arbiterOnly: "true"' if rs_arbiter == host
        "{ _id: #{id}, host: \"#{host}\"#{arbiter_conf} }"
      end.join(',')
      conf = "{ _id: \"#{name}\", members: [ #{hostconf} ] }"

      # Set replset members with the first host as the master
      output = rs_initiate(conf, alive_hosts[0])
      if output['ok'].zero?
        raise Puppet::Error, "rs.initiate() failed for replicaset #{name}: #{output['errmsg']}"
      end

      # Check that the replicaset has finished initialization
      retry_limit = 10
      retry_sleep = 3

      retry_limit.times do |n|
        begin
          if db_ismaster(alive_hosts[0])['ismaster']
            Puppet.debug 'Replica set initialization has successfully ended'
            return true
          else
            Puppet.debug "Wainting for replica initialization. Retry: #{n}"
            sleep retry_sleep
            next
          end
        end
      end
      raise Puppet::Error, "rs.initiate() failed for replicaset #{name}: host #{alive_hosts[0]} didn't become master"

    else
      # Add members to an existing replset
      Puppet.debug "Adding member to existing replset #{name}"

      master = master_host(alive_hosts)
      raise Puppet::Error, "Can't find master host for replicaset #{name}." unless master

      master_data = db_ismaster(master)
      current_hosts = master_data['hosts']
      current_hosts += master_data['arbiters'] if master_data.key?('arbiters')
      Puppet.debug "Current Hosts are: #{current_hosts.inspect}"
      newhosts = alive_hosts - current_hosts
      Puppet.debug "New Hosts are: #{newhosts.inspect}"

      newhosts.each do |host|
        output = {}
        output = rs_arbiter == host ? rs_add_arbiter(host, master) : rs_add(host, master)
        raise Puppet::Error, "rs.add() failed to add host to replicaset #{name}: #{output['errmsg']}" if output['ok'].zero?
      end
    end
  end

  def mongo_command(command, host, retries = 4)
    self.class.mongo_command(command, host, retries)
  end

  def self.mongo_command(command, host = nil, retries = 4)
    begin
      output = mongo_eval("printjson(#{command})", 'admin', retries, host)
    rescue Puppet::ExecutionFailure => e
      Puppet.debug "Got an exception: #{e}"
      raise
    end

    # Dirty hack to remove JavaScript objects
    output.gsub!(%r{\w+\((\d+).+?\)}, '\1') # Remove extra parameters from 'Timestamp(1462971623, 1)' Objects
    output.gsub!(%r{\w+\((.+?)\)}, '\1')

    # Hack to avoid non-json empty sets
    output = '{}' if output == "null\n"
    output = '{}' if output == "\nnull\n"

    # Parse the JSON output and return
    JSON.parse(output)
  end
end
