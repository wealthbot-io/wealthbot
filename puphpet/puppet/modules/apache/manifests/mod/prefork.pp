class apache::mod::prefork (
  $startservers           = '8',
  $minspareservers        = '5',
  $maxspareservers        = '20',
  $serverlimit            = '256',
  $maxclients             = '256',
  $maxrequestworkers      = undef,
  $maxrequestsperchild    = '4000',
  $maxconnectionsperchild = undef,
  $apache_version         = undef,
) {
  include ::apache
  $_apache_version = pick($apache_version, $apache::apache_version)
  if defined(Class['apache::mod::event']) {
    fail('May not include both apache::mod::prefork and apache::mod::event on the same node')
  }
  if versioncmp($_apache_version, '2.4') < 0 {
    if defined(Class['apache::mod::itk']) {
      fail('May not include both apache::mod::prefork and apache::mod::itk on the same node')
    }
  }
  if defined(Class['apache::mod::peruser']) {
    fail('May not include both apache::mod::prefork and apache::mod::peruser on the same node')
  }
  if defined(Class['apache::mod::worker']) {
    fail('May not include both apache::mod::prefork and apache::mod::worker on the same node')
  }

  if versioncmp($_apache_version, '2.3.13') < 0 {
    if $maxrequestworkers == undef {
      warning("For newer versions of Apache, \$maxclients is deprecated, please use \$maxrequestworkers.")
    } elsif $maxconnectionsperchild == undef {
      warning("For newer versions of Apache, \$maxrequestsperchild is deprecated, please use \$maxconnectionsperchild.")
    }
  }

  File {
    owner => 'root',
    group => $::apache::params::root_group,
    mode  => $::apache::file_mode,
  }

  # Template uses:
  # - $startservers
  # - $minspareservers
  # - $maxspareservers
  # - $serverlimit
  # - $maxclients
  # - $maxrequestworkers
  # - $maxrequestsperchild
  # - $maxconnectionsperchild
  file { "${::apache::mod_dir}/prefork.conf":
    ensure  => file,
    content => template('apache/mod/prefork.conf.erb'),
    require => Exec["mkdir ${::apache::mod_dir}"],
    before  => File[$::apache::mod_dir],
    notify  => Class['apache::service'],
  }

  case $::osfamily {
    'redhat': {
      if versioncmp($_apache_version, '2.4') >= 0 {
        ::apache::mpm{ 'prefork':
          apache_version => $_apache_version,
        }
      }
      else {
        file_line { '/etc/sysconfig/httpd prefork enable':
          ensure  => present,
          path    => '/etc/sysconfig/httpd',
          line    => '#HTTPD=/usr/sbin/httpd.worker',
          match   => '#?HTTPD=/usr/sbin/httpd.worker',
          require => Package['httpd'],
          notify  => Class['apache::service'],
        }
      }
    }
    'debian', 'freebsd': {
      ::apache::mpm{ 'prefork':
        apache_version => $_apache_version,
      }
    }
    'Suse': {
      ::apache::mpm{ 'prefork':
        apache_version => $apache_version,
        lib_path       => '/usr/lib64/apache2-prefork',
      }
    }
    'gentoo': {
      ::portage::makeconf { 'apache2_mpms':
        content => 'prefork',
      }
    }
    default: {
      fail("Unsupported osfamily ${::osfamily}")
    }
  }
}
