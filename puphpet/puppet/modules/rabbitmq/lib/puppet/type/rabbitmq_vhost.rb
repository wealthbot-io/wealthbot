Puppet::Type.newtype(:rabbitmq_vhost) do
  desc <<-DESC
Native type for managing rabbitmq vhosts

@example query all current vhosts
 $ puppet resource rabbitmq_vhost`

@example Create a rabbitmq_vhost
 rabbitmq_vhost { 'myvhost':
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

  autorequire(:service) { 'rabbitmq-server' }

  newparam(:name, namevar: true) do
    desc 'The name of the vhost to add'
    newvalues(%r{^\S+$})
  end
end
