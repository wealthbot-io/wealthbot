# Class for installing rabbitmq
#
class puphpet::rabbitmq::install
  inherits puphpet::rabbitmq::params
{

  $rabbitmq = $puphpet::params::hiera['rabbitmq']
  $locales  = $puphpet::params::hiera['locales']
  $php      = $puphpet::params::hiera['php']

  if $::operatingsystem == 'debian' {
     fail('RabbitMQ is not supported on Debian. librabbitmq-dev is too old.')
  }

  Class['erlang']
  -> Class['rabbitmq']

  include ::erlang

  $lc_all = array_true($locales, 'lc_all') ? {
    true    => $locales['lc_all'],
    default => 'en_US.UTF-8',
  }

  $settings = merge({
    'delete_guest_user'     => true,
    'repos_ensure'          => false,
    'environment_variables' => {
      'LC_ALL' => $lc_all,
    },
  }, $rabbitmq['settings'], {
    'port' => Integer($rabbitmq['settings']['port']),
  })

  create_resources('class', { 'rabbitmq' => $settings })

  include ::puphpet::rabbitmq::repos

  puphpet::rabbitmq::plugins { 'from puphpet::rabbitmq::install': }
  puphpet::rabbitmq::vhosts { 'from puphpet::rabbitmq::install': }
  puphpet::rabbitmq::users { 'from puphpet::rabbitmq::install': }

  if array_true($php, 'install') and ! defined(Puphpet::Php::Module::Pecl['amqp']) {
    if ! defined(Package[$puphpet::rabbitmq::params::rabbitmq_dev_pkg]) {
      package { $puphpet::rabbitmq::params::rabbitmq_dev_pkg:
        ensure => present,
      }
    }

    if $::osfamily == 'debian' {
      if ! defined(Puphpet::Php::Module::Package[$puphpet::rabbitmq::params::php_pkg]) {
        puphpet::php::module::package { $puphpet::rabbitmq::params::php_pkg:
          service_autorestart => $puphpet::rabbitmq::params::webserver_restart,
          prefix              => 'php-',
          require             => [
            Package['rabbitmq-server'],
            Package[$puphpet::rabbitmq::params::rabbitmq_dev_pkg],
          ]
        }
      }
    }

    if $::osfamily == 'redhat' {
      if ! defined(Puphpet::Php::Module::Pecl[$puphpet::rabbitmq::params::pecl_pkg]) {
        puphpet::php::module::pecl { $puphpet::rabbitmq::params::pecl_pkg:
          service_autorestart => $puphpet::rabbitmq::params::webserver_restart,
          require             => [
            Package['rabbitmq-server'],
            Package[$puphpet::rabbitmq::params::rabbitmq_dev_pkg],
          ]
        }
      }
    }

  }

  if ! defined(Puphpet::Firewall::Port["${settings['port']}"]) {
    puphpet::firewall::port { "${settings['port']}": }
  }

}
