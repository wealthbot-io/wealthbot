require 'puppet'
require 'set'
Puppet::Type.type(:rabbitmq_erlang_cookie).provide(:ruby) do
  confine feature: :posix

  def exists?
    # Hack to prevent the create method from being called.
    # We never need to create or destroy this resource, only change its value
    true
  end

  def content=(value)
    raise('The current erlang cookie needs to change. In order to do this the RabbitMQ database needs to be wiped.  Please set force => true to allow this to happen automatically.') unless resource[:force] == :true # Danger!

    Puppet::Type.type(:service).new(name: resource[:service_name]).provider.stop
    FileUtils.rm_rf(resource[:rabbitmq_home] + File::SEPARATOR + 'mnesia')
    File.open(resource[:path], 'w') do |cookie|
      cookie.chmod(0o400)
      cookie.write(value)
    end
    FileUtils.chown(resource[:rabbitmq_user], resource[:rabbitmq_group], resource[:path])
  end

  def content
    if File.exist?(resource[:path])
      File.read(resource[:path])
    else
      ''
    end
  end
end
