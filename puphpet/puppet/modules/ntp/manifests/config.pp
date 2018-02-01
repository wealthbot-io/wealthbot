# @api private
# This class handles the configuration file. Avoid modifying private classes.
class ntp::config inherits ntp {

  #The servers-netconfig file overrides NTP config on SLES 12, interfering with our configuration.
  if ($facts['operatingsystem'] == 'SLES' and $facts['operatingsystemmajrelease'] == '12') or
    ($facts['operatingsystem'] == 'OpenSuSE' and $facts['operatingsystemmajrelease'] == '42') {
    file { '/var/run/ntp/servers-netconfig':
      ensure => 'absent'
    }
  }

  if $ntp::keys_enable {
    case $ntp::config_dir {
      '/', '/etc', undef: {}
      default: {
        file { $ntp::config_dir:
          ensure  => directory,
          owner   => 0,
          group   => 0,
          mode    => '0775',
          recurse => false,
        }
      }
    }

    file { $ntp::keys_file:
      ensure  => file,
      owner   => 0,
      group   => 0,
      mode    => '0600',
      content => epp('ntp/keys.epp'),
    }
  }

  #If both epp and erb are defined, throw validation error.
  #Otherwise use the defined erb/epp template, or use default
  if $ntp::config_epp and $ntp::config_template {
    fail('Cannot supply both config_epp and config_template templates for ntp config file.')
  } elsif $ntp::config_template {
    $config_content = template($ntp::config_template)
  } elsif $ntp::config_epp {
    $config_content = epp($ntp::config_epp)
  } else {
    $config_content = epp('ntp/ntp.conf.epp')
  }

  file { $ntp::config:
    ensure  => file,
    owner   => 0,
    group   => 0,
    mode    => $::ntp::config_file_mode,
    content => $config_content,
  }

  #If both epp and erb are defined, throw validation error.
  #Otherwise use the defined erb/epp template, or use default

  if $::ntp::step_tickers_file {
    if $::ntp::step_tickers_template and $::ntp::step_tickers_epp {
      fail('Cannot supply both step_tickers_file and step_tickers_epp templates for step ticker file')
    } elsif $::ntp::step_tickers_template {
      $step_ticker_content = template($ntp::step_tickers_template)
    } elsif $::ntp::step_tickers_epp {
      $step_ticker_content = epp($::ntp::step_tickers_epp)
    } else{
      $step_ticker_content = epp('ntp/step-tickers.epp')
    }

    file { $::ntp::step_tickers_file:
      ensure  => file,
      owner   => 0,
      group   => 0,
      mode    => $::ntp::config_file_mode,
      content => $step_ticker_content,
    }
  }

  if $ntp::logfile {
    file { $ntp::logfile:
      ensure => file,
      owner  => 'ntp',
      group  => 'ntp',
      mode   => '0664',
    }
  }

  if $ntp::disable_dhclient {
    augeas { 'disable ntp-servers in dhclient.conf':
      context => '/files/etc/dhcp/dhclient.conf',
      changes => 'rm request/*[.="ntp-servers"]',
    }

    file { '/var/lib/ntp/ntp.conf.dhcp':
      ensure => absent,
    }
  }
}
