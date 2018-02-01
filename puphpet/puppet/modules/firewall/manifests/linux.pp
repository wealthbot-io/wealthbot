# = Class: firewall::linux
#
# Installs the `iptables` package for Linux operating systems and includes
# the appropriate sub-class for any distribution specific services and
# additional packages.
#
# == Parameters:
#
# [*ensure*]
#   Ensure parameter passed onto Service[] resources. When `running` the
#   service will be started on boot, and when `stopped` it will not.
#   Default: running
#
# [*ensure_v6*]
#   Ensure parameter passed onto Service[] resources. When `running` the
#   service will be started on boot, and when `stopped` it will not.
#   Default: running
#
class firewall::linux (
  $ensure          = running,
  $ensure_v6       = undef,
  $pkg_ensure      = present,
  $service_name    = $::firewall::params::service_name,
  $service_name_v6 = $::firewall::params::service_name_v6,
  $package_name    = $::firewall::params::package_name,
  $ebtables_manage = false,
) inherits ::firewall::params {
  $enable = $ensure ? {
    'running' => true,
    'stopped' => false,
  }

  $_ensure_v6 = pick($ensure_v6, $ensure)

  $_enable_v6 = $_ensure_v6 ? {
    running => true,
    stopped => false,
  }

  package { 'iptables':
    ensure => $pkg_ensure,
  }

  if $ebtables_manage {
    package { 'ebtables':
      ensure => $pkg_ensure,
    }
  }

  case $::operatingsystem {
    'RedHat', 'CentOS', 'Fedora', 'Scientific', 'SL', 'SLC', 'Ascendos',
    'CloudLinux', 'PSBM', 'OracleLinux', 'OVS', 'OEL', 'Amazon', 'XenServer',
    'VirtuozzoLinux': {
      class { "${title}::redhat":
        ensure          => $ensure,
        ensure_v6       => $_ensure_v6,
        enable          => $enable,
        enable_v6       => $_enable_v6,
        package_name    => $package_name,
        service_name    => $service_name,
        service_name_v6 => $service_name_v6,
        require         => Package['iptables'],
      }
    }
    'Debian', 'Ubuntu': {
      class { "${title}::debian":
        ensure       => $ensure,
        enable       => $enable,
        package_name => $package_name,
        service_name => $service_name,
        require      => Package['iptables'],
      }
    }
    'Archlinux': {
      class { "${title}::archlinux":
        ensure       => $ensure,
        enable       => $enable,
        package_name => $package_name,
        service_name => $service_name,
        require      => Package['iptables'],
      }
    }
    'Gentoo': {
      class { "${title}::gentoo":
        ensure       => $ensure,
        enable       => $enable,
        package_name => $package_name,
        service_name => $service_name,
        require      => Package['iptables'],
      }
    }
    default: {}
  }
}
