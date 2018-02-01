# Allows various adminstrative settings for Redis
# As documented in the FAQ and https://redis.io/topics/admin
#
# @example
#   include redis::administration
#
# @example
#   class {'redis::administration':
#     disable_thp => false,
#   }
#
# @param [Boolean] enable_overcommit_memory Enable the overcommit memory setting (Defaults to true)
# @param [Boolean] disable_thp Disable Transparent Huge Pages (Defaults to true)
# @param [String] somaxconn Set somaxconn value (Defaults to '65535')
#
# @author - Peter Souter
#
class redis::administration(
  $enable_overcommit_memory = true,
  $disable_thp              = true,
  $somaxconn                = '65535',
) {

  if $enable_overcommit_memory {
    sysctl { 'vm.overcommit_memory':
      ensure => 'present',
      value  => '1',
    }
  }

  if $disable_thp {
    exec { 'Disable Hugepages':
      command => 'echo never > /sys/kernel/mm/transparent_hugepage/enabled',
      path    => ['/sbin', '/usr/sbin', '/bin', '/usr/bin'],
      onlyif  => 'test -f /sys/kernel/mm/transparent_hugepage/enabled',
      unless  => 'cat /sys/kernel/mm/transparent_hugepage/enabled | grep "\[never\]"',
    }
  }

  if $somaxconn {
    sysctl { 'net.core.somaxconn':
      ensure => 'present',
      value  => $somaxconn,
    }
  }

}
