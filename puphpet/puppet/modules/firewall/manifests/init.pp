# = Class: firewall
#
# Manages packages and services required by the firewall type/provider.
#
# This class includes the appropriate sub-class for your operating system,
# where supported.
#
# == Parameters:
#
# [*ensure*]
#   Ensure parameter passed onto Service[] resources.
#   Default: running
#
class firewall (
  $ensure          = running,
  $ensure_v6       = undef,
  $pkg_ensure      = present,
  $service_name    = $::firewall::params::service_name,
  $service_name_v6 = $::firewall::params::service_name_v6,
  $package_name    = $::firewall::params::package_name,
  $ebtables_manage = false,
) inherits ::firewall::params {
  $_ensure_v6 = pick($ensure_v6, $ensure)

  case $ensure {
    /^(running|stopped)$/: {
      # Do nothing.
    }
    default: {
      fail("${title}: Ensure value '${ensure}' is not supported")
    }
  }

  if $ensure_v6 {
    case $ensure_v6 {
      /^(running|stopped)$/: {
        # Do nothing.
      }
      default: {
        fail("${title}: ensure_v6 value '${ensure_v6}' is not supported")
      }
    }
  }

  case $::kernel {
    'Linux': {
      class { "${title}::linux":
        ensure          => $ensure,
        ensure_v6       => $_ensure_v6,
        pkg_ensure      => $pkg_ensure,
        service_name    => $service_name,
        service_name_v6 => $service_name_v6,
        package_name    => $package_name,
        ebtables_manage => $ebtables_manage,
      }
      contain "${title}::linux"
    }
    'FreeBSD': {
    }
    default: {
      fail("${title}: Kernel '${::kernel}' is not currently supported")
    }
  }
}
