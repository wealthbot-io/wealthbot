# Defines where we can expect PHP-FPM ini files and paths to be located.
#
# ubuntu
#    7.1
#        /etc/php/7.1/fpm/php-fpm.conf
# centos
#    7.1
#        /etc/php-fpm.conf
#
define puphpet::php::fpm::ini (
  $fpm_version,
  $entry,
  $value  = '',
  $ensure = present,
  $php_fpm_service
  ) {

  $pool_name = 'global'

  $conf_filename = $::osfamily ? {
    'debian' => "/etc/php/${fpm_version}/fpm/php-fpm.conf",
    'redhat' => '/etc/php-fpm.conf',
  }

  if '=' in $value {
    $changes = $ensure ? {
      present => [ "set '${pool_name}/${entry}' \"'${value}'\"" ],
      absent  => [ "rm \"'${pool_name}/${entry}'\"" ],
    }
  }
  else {
    $changes = $ensure ? {
      present => [ "set '${pool_name}/${entry}' '${value}'" ],
      absent  => [ "rm \"'${pool_name}/${entry}'\"" ],
    }
  }

  augeas { "${pool_name}/${entry}: ${value}":
    lens    => 'PHP.lns',
    incl    => $conf_filename,
    changes => $changes,
    notify  => Service[$php_fpm_service],
  }

}
