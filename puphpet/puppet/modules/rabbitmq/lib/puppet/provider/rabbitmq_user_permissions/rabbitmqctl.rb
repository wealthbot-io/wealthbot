require File.expand_path(File.join(File.dirname(__FILE__), '..', 'rabbitmqctl'))
Puppet::Type.type(:rabbitmq_user_permissions).provide(:rabbitmqctl, parent: Puppet::Provider::Rabbitmqctl) do
  if Puppet::PUPPETVERSION.to_f < 3
    commands rabbitmqctl: 'rabbitmqctl'
  else
    has_command(:rabbitmqctl, 'rabbitmqctl') do
      environment HOME: '/tmp'
    end
  end

  confine feature: :posix

  # cache users permissions
  def self.users(name, vhost)
    @users = {} unless @users
    unless @users[name]
      @users[name] = {}
      user_permission_list = run_with_retries do
        rabbitmqctl('-q', 'list_user_permissions', name)
      end
      user_permission_list.split(%r{\n}).each do |line|
        line = strip_backslashes(line)
        raise Puppet::Error, "cannot parse line from list_user_permissions:#{line}" unless line =~ %r{^(\S+)\s+(\S*)\s+(\S*)\s+(\S*)$}
        @users[name][Regexp.last_match(1)] =
          { configure: Regexp.last_match(2), read: Regexp.last_match(4), write: Regexp.last_match(3) }
      end
    end
    @users[name][vhost]
  end

  def users(name, vhost)
    self.class.users(name, vhost)
  end

  def should_user
    if @should_user
      @should_user
    else
      @should_user = resource[:name].split('@')[0]
    end
  end

  def should_vhost
    if @should_vhost
      @should_vhost
    else
      @should_vhost = resource[:name].split('@')[1]
    end
  end

  def create
    resource[:configure_permission] ||= "''"
    resource[:read_permission]      ||= "''"
    resource[:write_permission]     ||= "''"
    rabbitmqctl('set_permissions', '-p', should_vhost, should_user, resource[:configure_permission], resource[:write_permission], resource[:read_permission])
  end

  def destroy
    rabbitmqctl('clear_permissions', '-p', should_vhost, should_user)
  end

  # I am implementing prefetching in exists b/c I need to be sure
  # that the rabbitmq package is installed before I make this call.
  def exists?
    users(should_user, should_vhost)
  end

  def configure_permission
    users(should_user, should_vhost)[:configure]
  end

  def configure_permission=(_perm)
    set_permissions
  end

  def read_permission
    users(should_user, should_vhost)[:read]
  end

  def read_permission=(_perm)
    set_permissions
  end

  def write_permission
    users(should_user, should_vhost)[:write]
  end

  def write_permission=(_perm)
    set_permissions
  end

  # implement memoization so that we only call set_permissions once
  def set_permissions
    return if @permissions_set

    @permissions_set = true
    resource[:configure_permission] ||= configure_permission
    resource[:read_permission]      ||= read_permission
    resource[:write_permission]     ||= write_permission
    rabbitmqctl(
      'set_permissions',
      '-p', should_vhost,
      should_user,
      resource[:configure_permission],
      resource[:write_permission],
      resource[:read_permission]
    )
  end

  def self.strip_backslashes(string)
    # See: https://github.com/rabbitmq/rabbitmq-server/blob/v1_7/docs/rabbitmqctl.1.pod#output-escaping
    string.gsub(%r{\\\\}, '\\')
  end
end
