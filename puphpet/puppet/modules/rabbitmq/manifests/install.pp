# Class rabbitmq::install
# Ensures that rabbitmq-server exists
class rabbitmq::install {

  $package_ensure   = $rabbitmq::package_ensure
  $package_name     = $rabbitmq::package_name
  $rabbitmq_group   = $rabbitmq::rabbitmq_group

  package { 'rabbitmq-server':
    ensure => $package_ensure,
    name   => $package_name,
    notify => Class['rabbitmq::service'],
  }

  if $rabbitmq::environment_variables['MNESIA_BASE'] {
    file { $rabbitmq::environment_variables['MNESIA_BASE']:
      ensure  => 'directory',
      owner   => 'root',
      group   => $rabbitmq_group,
      mode    => '0775',
      require => Package['rabbitmq-server'],
    }
  }
}
