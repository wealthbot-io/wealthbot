require 'json'
require 'puppet/util/package'

require File.expand_path(File.join(File.dirname(__FILE__), '..', 'rabbitmqctl'))
Puppet::Type.type(:rabbitmq_parameter).provide(:rabbitmqctl, parent: Puppet::Provider::Rabbitmqctl) do
  confine feature: :posix

  # cache parameters
  def self.parameters(name, vhost)
    @parameters = {} unless @parameters
    unless @parameters[vhost]
      @parameters[vhost] = {}
      parameter_list = run_with_retries do
        rabbitmqctl('list_parameters', '-q', '-p', vhost)
      end
      parameter_list.split(%r{\n}).each do |line|
        raise Puppet::Error, "cannot parse line from list_parameter:#{line}" unless line =~ %r{^(\S+)\s+(\S+)\s+(\S+)$}
        @parameters[vhost][Regexp.last_match(2)] = {
          component_name: Regexp.last_match(1),
          value: JSON.parse(Regexp.last_match(3))
        }
      end
    end
    @parameters[vhost][name]
  end

  def parameters(name, vhost)
    self.class.parameters(vhost, name)
  end

  def should_parameter
    @should_parameter ||= resource[:name].rpartition('@').first
  end

  def should_vhost
    @should_vhost ||= resource[:name].rpartition('@').last
  end

  def create
    set_parameter
  end

  def destroy
    rabbitmqctl('clear_parameter', '-p', should_vhost, 'shovel', should_parameter)
  end

  def exists?
    parameters(should_vhost, should_parameter)
  end

  def component_name
    parameters(should_vhost, should_parameter)[:component_name]
  end

  def component_name=(_component_name)
    set_parameter
  end

  def value
    parameters(should_vhost, should_parameter)[:value]
  end

  def value=(_value)
    set_parameter
  end

  def set_parameter
    return if @set_parameter

    @set_parameter = true
    resource[:value] ||= value
    resource[:component_name] ||= component_name
    rabbitmqctl(
      'set_parameter',
      '-p', should_vhost,
      resource[:component_name],
      should_parameter,
      resource[:value].to_json
    )
  end
end
