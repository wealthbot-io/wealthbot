# Redis class for configuring ulimit
# Used to DRY up the config class, and
# move the logic for ulimit changes all
# into one place.
#
# Parameters are not required as it's a
# private class only referencable from
# the redis module, where the variables
# would already be defined
#
# @example
#   contain redis::ulimit
#
# @author - Peter Souter
#
# @api private
class redis::ulimit {
  assert_private('The redis::ulimit class is only to be called from the redis::config class')

  $service_provider_lookup = pick(getvar('service_provider'), false)

  if $::redis::managed_by_cluster_manager {
    file { '/etc/security/limits.d/redis.conf':
      ensure  => 'file',
      owner   => 'root',
      group   => 'root',
      mode    => '0644',
      content => "redis soft nofile ${::redis::ulimit}\nredis hard nofile ${::redis::ulimit}\n",
    }
  }
  if $service_provider_lookup == 'systemd' {
    file { "/etc/systemd/system/${::redis::service_name}.service.d/":
      ensure                  => 'directory',
      owner                   => 'root',
      group                   => 'root',
      selinux_ignore_defaults => true,
    }

    file { "/etc/systemd/system/${::redis::service_name}.service.d/limit.conf":
      ensure => file,
      owner  => 'root',
      group  => 'root',
      mode   => '0444',
    }
    augeas { 'Systemd redis ulimit' :
      incl    => "/etc/systemd/system/${::redis::service_name}.service.d/limits.conf",
      lens    => 'Systemd.lns',
      context => "/etc/systemd/system/${::redis::service_name}.service.d/limits.conf",
      changes => [
        "defnode nofile Service/LimitNOFILE \"\"",
        "set \$nofile/value \"${::redis::ulimit}\""],
      notify  => [
        Exec['systemd-reload-redis'],
      ],
    }
  } else {
    augeas { 'redis ulimit':
      changes => "set ULIMIT ${::redis::ulimit}",
    }
    case $::osfamily {
      'Debian': {
        Augeas['redis ulimit'] {
          context => '/files/etc/default/redis-server',
        }
      }
      'RedHat': {
        Augeas['redis ulimit'] {
          context => '/files/etc/sysconfig/redis',
        }
      }
      default: {
        warning("Not sure how to set ULIMIT on non-systemd OSFamily ${::osfamily}, PR's welcome")
      }
    }
  }

}
