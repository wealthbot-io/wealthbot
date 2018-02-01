# Defines where we can expect PHP-FPM ini files and paths to be located.
#
# ubuntu
#    7.1
#        /etc/php/7.1/fpm/pool.d/www.conf
# centos
#    7.1
#        /etc/php-fpm.d/www.conf
#
define puphpet::php::fpm::pool_ini (
  $fpm_version,
  $pool_name = 'www',
  $entry,
  $value     = '',
  $ensure    = present,
  $php_fpm_service
  ) {

  $conf_filename = $::osfamily ? {
    'debian' => "/etc/php/${fpm_version}/fpm/pool.d/www.conf",
    'redhat' => '/etc/php-fpm.d/www.conf',
  }

  if '=' in $value {
    $changes = $ensure ? {
      present => [ "set '${pool_name}/${entry}' \"'${value}'\"" ],
      absent  => [ "rm \"'${pool_name}/${entry}'\"" ],
    }
  } else {
    $changes = $ensure ? {
      present => [ "set '${pool_name}/${entry}' '${value}'" ],
      absent  => [ "rm \"'${pool_name}/${entry}'\"" ],
    }
  }

  if ! defined(File[$conf_filename]) {
    file { $conf_filename:
      replace => no,
      ensure  => present,
      notify  => Service[$php_fpm_service],
    }
  }

  if ! defined(File_line["[${pool_name}]"]) {
    file_line { "[${pool_name}]":
      path    => $conf_filename,
      line    => "[${pool_name}]",
      require => File[$conf_filename],
    }
  }

  augeas { "${pool_name}/${entry}: ${value}":
    lens    => 'PHP.lns',
    incl    => $conf_filename,
    changes => $changes,
    require => File_line["[${pool_name}]"],
    notify  => Service[$php_fpm_service],
  }

}
