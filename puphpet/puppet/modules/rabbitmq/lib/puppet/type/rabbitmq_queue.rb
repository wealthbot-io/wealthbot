Puppet::Type.newtype(:rabbitmq_queue) do
  desc <<-DESC
Native type for managing rabbitmq queue

@example Create a rabbitmq_queue
 rabbitmq_queue { 'myqueue@myvhost':
   ensure      => present,
   user        => 'dan',
   password    => 'bar',
   durable     => true,
   auto_delete => false,
   arguments   => {
     x-message-ttl          => 123,
     x-dead-letter-exchange => 'other'
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

  newparam(:name, namevar: true) do
    desc 'Name of queue'
    newvalues(%r{^\S*@\S+$})
  end

  newparam(:durable) do
    desc 'Queue is durable'
    newvalues(%r{true|false})
    defaultto('true')
  end

  newparam(:auto_delete) do
    desc 'Queue will be auto deleted'
    newvalues(%r{true|false})
    defaultto('false')
  end

  newparam(:arguments) do
    desc 'Queue arguments example: {x-message-ttl => 60, x-expires => 10}'
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
    [self[:name].split('@')[1]]
  end

  autorequire(:rabbitmq_user) do
    [self[:user]]
  end

  autorequire(:rabbitmq_user_permissions) do
    ["#{self[:user]}@#{self[:name].split('@')[1]}"]
  end

  def validate_argument(argument)
    raise ArgumentError, 'Invalid argument' unless [Hash].include?(argument.class)
  end
end
