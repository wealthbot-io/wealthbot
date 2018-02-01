Puppet::Type.newtype(:rabbitmq_parameter) do
  desc <<-DESC
Type for managing rabbitmq parameters

@example Create some rabbitmq_parameter resources
   rabbitmq_parameter { 'documentumShovel@/':
     component_name => '',
     value          => {
         'src-uri'    => 'amqp://',
         'src-queue'  => 'my-queue',
         'dest-uri'   => 'amqp://remote-server',
         'dest-queue' => 'another-queue',
     },
   }
   rabbitmq_parameter { 'documentumFed@/':
     component_name => 'federation-upstream',
     value          => {
         'uri'     => 'amqp://myserver',
         'expires' => '360000',
     },
   }
DESC

  ensurable do
    defaultto(:present)
    newvalue(:present) do
      provider.create
    end
    newvalue(:absent) do
      provider.destroy
    end
  end

  autorequire(:service) { 'rabbitmq-server' }

  validate do
    raise('component_name parameter is required.') if self[:ensure] == :present && self[:component_name].nil?
    raise('value parameter is required.') if self[:ensure] == :present && self[:value].nil?
  end

  newparam(:name, namevar: true) do
    desc 'combination of name@vhost to set parameter for'
    newvalues(%r{^\S+@\S+$})
  end

  newproperty(:component_name) do
    desc 'The component_name to use when setting parameter, eg: shovel or federation'
    validate do |value|
      resource.validate_component_name(value)
    end
  end

  newproperty(:value) do
    desc 'A hash of values to use with the component name you are setting'
    validate do |value|
      resource.validate_value(value)
    end
    munge do |value|
      resource.munge_value(value)
    end
  end

  autorequire(:rabbitmq_vhost) do
    [self[:name].split('@')[1]]
  end

  def validate_component_name(value)
    raise ArgumentError, 'component_name must be defined' if value.empty?
  end

  def validate_value(value)
    raise ArgumentError, 'Invalid value' unless [Hash].include?(value.class)
    value.each do |_k, v|
      unless [String, TrueClass, FalseClass].include?(v.class)
        raise ArgumentError, 'Invalid value'
      end
    end
  end

  def munge_value(value)
    value.each do |k, v|
      value[k] = v.to_i if v =~ %r{\A[-+]?[0-9]+\z}
    end
    value
  end
end
