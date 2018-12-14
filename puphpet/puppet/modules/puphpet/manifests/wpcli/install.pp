# Installs WPCLI system-wide
class puphpet::wpcli::install {

  $source   = 'https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar'
  $location = '/usr/local/bin/wp-cli'

  puphpet::server::wget { $location:
    source => $source,
    user   => 'root',
    group  => 'root',
    mode   => '+x'
  }
  -> file { 'symlink wp-cli':
    ensure => link,
    path   => '/usr/local/bin/wp',
    target => $location,
  }

}
