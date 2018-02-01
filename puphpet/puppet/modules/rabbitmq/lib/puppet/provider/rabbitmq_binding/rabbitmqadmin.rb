require 'json'
require 'puppet'
require 'digest'

Puppet::Type.type(:rabbitmq_binding).provide(:rabbitmqadmin) do
  has_command(:rabbitmqctl, 'rabbitmqctl') do
    environment HOME: '/tmp'
  end
  has_command(:rabbitmqadmin, '/usr/local/bin/rabbitmqadmin') do
    environment HOME: '/tmp'
  end

  confine feature: :posix

  # Without this, the composite namevar stuff doesn't work properly.
  mk_resource_methods

  def should_vhost
    if @should_vhost
      @should_vhost
    else
      @should_vhost = resource[:vhost]
    end
  end

  def self.all_vhosts
    vhosts = []
    rabbitmqctl('list_vhosts', '-q').split(%r{\n}).map do |vhost|
      vhosts.push(vhost)
    end
    vhosts
  end

  def self.all_bindings(vhost)
    rabbitmqctl('list_bindings', '-q', '-p', vhost, 'source_name', 'destination_name', 'destination_kind', 'routing_key', 'arguments').split(%r{\n})
  end

  def self.instances
    resources = []
    all_vhosts.each do |vhost|
      all_bindings(vhost).map do |line|
        source_name, destination_name, destination_type, routing_key, arguments = line.split(%r{\t})
        # Convert output of arguments from the rabbitmqctl command to a json string.
        if !arguments.nil?
          arguments = arguments.gsub(%r{^\[(.*)\]$}, '').gsub(%r{\{("(?:.|\\")*?"),}, '{\1:').gsub(%r{\},\{}, ',')
          arguments = '{}' if arguments == ''
        else
          arguments = '{}'
        end
        hashed_name = Digest::SHA256.hexdigest format('%s@%s@%s@%s', source_name, destination_name, vhost, routing_key)
        next if source_name.empty?
        binding = {
          source: source_name,
          destination: destination_name,
          vhost: vhost,
          destination_type: destination_type,
          routing_key: routing_key,
          arguments: JSON.parse(arguments),
          ensure: :present,
          name: hashed_name
        }
        resources << new(binding) if binding[:name]
      end
    end
    resources
  end

  # see
  # https://github.com/puppetlabs/puppetlabs-netapp/blob/d0a655665463c69c932f835ba8756be32417a4e9/lib/puppet/provider/netapp_qtree/sevenmode.rb#L66-L73
  def self.prefetch(resources)
    bindings = instances
    resources.each do |name, res|
      if (provider = bindings.find { |binding| binding.source == res[:source] && binding.destination == res[:destination] && binding.vhost == res[:vhost] && binding.routing_key == res[:routing_key] })
        resources[name].provider = provider
      end
    end
  end

  def exists?
    @property_hash[:ensure] == :present
  end

  def create
    vhost_opt = should_vhost ? "--vhost=#{should_vhost}" : ''
    arguments = resource[:arguments]
    arguments = {} if arguments.nil?
    rabbitmqadmin('declare',
                  'binding',
                  vhost_opt,
                  "--user=#{resource[:user]}",
                  "--password=#{resource[:password]}",
                  '-c',
                  '/etc/rabbitmq/rabbitmqadmin.conf',
                  "source=#{resource[:source]}",
                  "destination=#{resource[:destination]}",
                  "arguments=#{arguments.to_json}",
                  "routing_key=#{resource[:routing_key]}",
                  "destination_type=#{resource[:destination_type]}")
    @property_hash[:ensure] = :present
  end

  def destroy
    vhost_opt = should_vhost ? "--vhost=#{should_vhost}" : ''
    rabbitmqadmin('delete', 'binding', vhost_opt, "--user=#{resource[:user]}", "--password=#{resource[:password]}", '-c', '/etc/rabbitmq/rabbitmqadmin.conf', "source=#{resource[:source]}", "destination_type=#{resource[:destination_type]}", "destination=#{resource[:destination]}", "properties_key=#{resource[:routing_key]}")
    @property_hash[:ensure] = :absent
  end
end
