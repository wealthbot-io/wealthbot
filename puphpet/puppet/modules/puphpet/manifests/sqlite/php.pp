class puphpet::sqlite::php
 inherits puphpet::sqlite::params {

  $sqlite = $puphpet::params::hiera['sqlite']
  $php   = $puphpet::params::hiera['php']

  if array_true($php, 'install') {
    $php_package = 'php'
  } else {
    $php_package = false
  }

  case $::operatingsystem {
    'debian': {
      $php_sqlite = 'sqlite'
    }
    'ubuntu': {
      $php_sqlite = 'sqlite3'
    }
    'redhat', 'centos': {
      $php_sqlite = 'sqlite3'
    }
  }

  if $php_package == 'php' and ! defined(Puphpet::Php::Module::Package[$php_sqlite]) {
    puphpet::php::module::package { $php_sqlite:
      service_autorestart => true,
    }
  }

}
