class puphpet::mariadb::php
 inherits puphpet::mariadb::params {

  include puphpet::php::params

  $mariadb = $puphpet::params::hiera['mariadb']
  $php     = $puphpet::params::hiera['php']

  if array_true($php, 'install') {
    $php_package = 'php'
  } else {
    $php_package = false
  }

  if $php_package == 'php' {
    $php_module = $::osfamily ? {
      'debian' => 'mysqlnd',
      'redhat' => 'mysql',
    }

    if ! defined(Puphpet::Php::Module::Package[$php_module]) {
      puphpet::php::module::package { $php_module:
        service_autorestart => true,
      }
    }
  }

}
