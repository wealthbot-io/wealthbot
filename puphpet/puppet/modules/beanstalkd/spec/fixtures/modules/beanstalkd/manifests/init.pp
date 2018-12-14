
# usage:
#
#  beanstalkd::config { name:
#    listenaddress  => '0.0.0.0',
#    listenport     => '13000',
#    maxjobsize     => '65535',
#    maxconnections => '1024',
#    binlogdir      => '/var/lib/beanstalkd/binlog',
#    binlogfsync    => undef,
#    binlogsize     => '10485760',
#    ensure         => 'running',   # running, stopped, absent
#    packageversion => 'latest',    # latest, present, or specific version
#    packagename    => undef,       # override package name
#    servicename    => undef        # override service name
#  }


define beanstalkd::config ( # name
  $listenaddress  = '0.0.0.0',
  $listenport     = '13000',
  $maxjobsize     = '65535',
  $maxconnections = '1024',                       # results in open file limit
  $binlogdir      = '/var/lib/beanstalkd/binlog', # set empty ( '' ) to disable binlog
  $binlogfsync    = undef,                        # unset = no explicit fsync
  $binlogsize     = '10485760',
  #
  $ensure         = 'running',  # running, stopped, absent
  $packageversion = 'latest',   # latest, present, or specific version
  $packagename    = undef,      # got your own custom package?  override the default name/service here.
  $servicename    = undef
) {

  include ::beanstalkd::params

  # simply the users experience for running/stopped/absent, and use ensure to cover those bases
  case $ensure {
    absent: {
      $ourpackageversion  = 'absent'
      $serviceenable      = 'false'
      $serviceensure      = 'stopped'
      $fileensure         = 'absent'
    }
    running: {
      $ourpackageversion = $packageversion
      $serviceenable     = 'true'
      $serviceensure     = 'running'
      $fileensure        = 'present'
    }
    stopped: {
      $ourpackageversion = $packageversion
      $serviceenable     = 'false'
      $serviceensure     = 'stopped'
      $fileensure        = 'present'
    }
    default: {
      fail("ERROR [${module_name}]: enable must be one of: running stopped absent")
    }
  }

  $user = $::beanstalkd::params::user

  # for service and package name - if we've specified one, use it. else use the default
  if $packagename == undef {
    $ourpackagename = $::beanstalkd::params::defaultpackagename
  } else {
    $ourpackagename = $packagename
  }

  if $servicename == undef {
    $ourservicename = $::beanstalkd::params::defaultservicename
  } else {
    $ourservicename = $servicename
  }

  package { $ourpackagename:
    ensure => $ourpackageversion
  }

  file { $::beanstalkd::params::configfile:
    content => template($::beanstalkd::params::configtemplate),
    owner   => 'root',
    group   => 'root',
    mode    => $::beanstalkd::params::mode,
    ensure  => $fileensure,
    require => Package[$ourpackagename],
  }

  if $::beanstalkd::params::servicefile != undef {
    file { $::beanstalkd::params::servicefile:
      content => template($::beanstalkd::params::servicefiletemplate),
      owner   => 'root',
      group   => 'root',
      mode    => $::beanstalkd::params::mode,
      ensure  => $fileensure,
      require => Package[$ourpackagename],
      notify  => Exec['beanstalkd-systemd-reloadconfig'],
    }

    exec { 'beanstalkd-systemd-reloadconfig':
      command     => $::beanstalkd::params::reloadconfig,
      path        => '/bin:/usr/bin:/usr/local/bin',
      notify      => Service[$ourservicename],
      refreshonly => true,
    }
  }

  if $binlogdir != '' {
    file { $binlogdir:
      owner   => $::beanstalkd::params::user,
      group   => 'root',
      ensure  => 'directory',
      require => Package[$ourpackagename],
    }
  }

  service { $ourservicename:
    enable    => $serviceenable,
    ensure    => $serviceensure,
    hasstatus => $::beanstalkd::params::hasstatus,
    restart   => $::beanstalkd::params::restart,
    require   => File[$::beanstalkd::params::configfile],
    subscribe => [
      Package[$ourpackagename],
      File[$::beanstalkd::params::configfile]
    ],
  }

}
