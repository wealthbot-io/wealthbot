Puppet::Type.newtype(:rabbitmq_plugin) do
  desc <<-DESC
manages rabbitmq plugins

@example query all currently enabled plugins
 $ puppet resource rabbitmq_plugin

@example Ensure a rabbitmq_plugin resource
 rabbitmq_plugin {'rabbitmq_stomp':
   ensure => present,
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
    desc 'The name of the plugin to enable'
    newvalues(%r{^\S+$})
  end

  newparam(:umask) do
    desc 'Sets the octal umask to be used while creating this resource'
    defaultto '0022'
    munge do |value|
      raise Puppet::Error, "The umask specification is invalid: #{value.inspect}" unless value =~ %r{^0?[0-7]{1,3}$}
      return value.to_i(8)
    end
  end
end
