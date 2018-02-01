class puphpet::rabbitmq::params
  inherits ::puphpet::params
{

  $repo_location = $::osfamily ? {
    'debian' => 'https://packagecloud.io/rabbitmq/rabbitmq-server',
    'redhat' => "https://packagecloud.io/rabbitmq/rabbitmq-server/el/${facts['os'][release][major]}/\$basearch",
  }

  $gpg_key     = '418A7F2FB0E1E6E7EABF6FE8C2E73424D59097AB'

  if array_true($puphpet::params::hiera['apache'], 'install') or
     array_true($puphpet::params::hiera['nginx'], 'install')
  {
    $webserver_restart = true
  } else {
    $webserver_restart = false
  }

  $rabbitmq_dev_pkg = $::osfamily ? {
    'debian' => 'librabbitmq-dev',
    'redhat' => 'librabbitmq-devel',
  }

  $pecl_pkg = 'amqp'
  $php_pkg  = 'amqp'

  # config file could contain no plugins key
  $plugins = array_true($puphpet::params::hiera['rabbitmq'], 'plugins') ? {
    true    => $puphpet::params::hiera['rabbitmq']['plugins'],
    default => []
  }

  # config file could contain no vhosts key
  $vhosts = array_true($puphpet::params::hiera['rabbitmq'], 'vhosts') ? {
    true    => $puphpet::params::hiera['rabbitmq']['vhosts'],
    default => []
  }

  # config file could contain no users key
  $users = array_true($puphpet::params::hiera['rabbitmq'], 'users') ? {
    true    => $puphpet::params::hiera['rabbitmq']['users'],
    default => { }
  }

}
