Puppet::Type.newtype(:rabbitmq_binding) do
  desc <<-DESC
Native type for managing rabbitmq bindings

@example Create a rabbitmq_binding
 rabbitmq_binding { 'myexchange@myqueue@myvhost':
   user             => 'dan',
   password         => 'bar',
   destination_type => 'queue',
   routing_key      => '#',
   arguments        => {},
   ensure           => present,
 }

@example Create bindings with same source / destination / vhost but different routing key using individual parameters
rabbitmq_binding { 'binding 1':
  ensure           => present,
  source           => 'myexchange',
  destination      => 'myqueue',
  vhost            => 'myvhost',
  user             => 'dan',
  password         => 'bar',
  destination_type => 'queue',
  routing_key      => 'key1',
  arguments        => {},
}

rabbitmq_binding { 'binding 2':
  ensure           => present,
  source           => 'myexchange',
  destination      => 'myqueue',
  vhost            => 'myvhost',
  user             => 'dan',
  password         => 'bar',
  destination_type => 'queue',
  routing_key      => 'key2',
  arguments        => {},
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

  # Match patterns without '@' as arbitrary names; match patterns with
  # src@destination@vhost to their named params for backwards compatibility.
  def self.title_patterns
    [
      [
        %r{(^([^@]*)$)}m,
        [
          [:name]
        ]
      ],
      [
        %r{^((\S+)@(\S+)@(\S+))$}m,
        [
          [:name],
          [:source],
          [:destination],
          [:vhost]
        ]
      ]
    ]
  end

  newparam(:name) do
    desc 'resource name, either source@destination@vhost or arbitrary name with params'

    isnamevar
  end

  newproperty(:source) do
    desc 'source of binding'

    newvalues(%r{^\S+$})
    isnamevar
  end

  newproperty(:destination) do
    desc 'destination of binding'

    newvalues(%r{^\S+$})
    isnamevar
  end

  newproperty(:vhost) do
    desc 'vhost'

    newvalues(%r{^\S+$})
    defaultto('/')
    isnamevar
  end

  newproperty(:routing_key) do
    desc 'binding routing_key'

    newvalues(%r{^\S*$})
    isnamevar
  end

  newproperty(:destination_type) do
    desc 'binding destination_type'
    newvalues(%r{queue|exchange})
    defaultto('queue')
  end

  newproperty(:arguments) do
    desc 'binding arguments'
    defaultto {}
    validate do |value|
      resource.validate_argument(value)
    end
  end

  newparam(:user) do
    desc 'The user to use to connect to rabbitmq'
    defaultto('guest')
    newvalues(%r{^\S+$})
  end

  newparam(:password) do
    desc 'The password to use to connect to rabbitmq'
    defaultto('guest')
    newvalues(%r{\S+})
  end

  autorequire(:rabbitmq_vhost) do
    setup_autorequire('vhost')
  end

  autorequire(:rabbitmq_exchange) do
    setup_autorequire('exchange')
  end

  autorequire(:rabbitmq_queue) do
    setup_autorequire('queue')
  end

  autorequire(:rabbitmq_user) do
    [self[:user]]
  end

  autorequire(:rabbitmq_user_permissions) do
    [
      "#{self[:user]}@#{self[:source]}",
      "#{self[:user]}@#{self[:destination]}"
    ]
  end

  def setup_autorequire(type)
    destination_type = value(:destination_type)
    if type == 'exchange'
      rval = ["#{self[:source]}@#{self[:vhost]}"]
      if destination_type == type
        rval.push("#{self[:destination]}@#{self[:vhost]}")
      end
    else
      rval = if destination_type == type
               ["#{self[:destination]}@#{self[:vhost]}"]
             else
               []
             end
    end
    rval
  end

  def validate_argument(argument)
    raise ArgumentError, 'Invalid argument' unless [Hash].include?(argument.class)
  end

  # Validate that we have both source and destination now that these are not
  # necessarily only coming from the resource title.
  validate do
    if !self[:source] && !defined? provider.source
      raise ArgumentError, '`source` must be defined'
    end

    if !self[:destination] && !defined? provider.destination
      raise ArgumentError, '`destination` must be defined'
    end
  end
end
