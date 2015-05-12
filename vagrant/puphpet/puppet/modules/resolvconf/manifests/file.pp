# Define: resolvconf::file
#
# Manage any resolv.conf type file with puppet. See resolv.conf(5).
#
# Parameters:
#  $nameserver:
#    Array of nameservers. Default: empty
#  $domain:
#    Domain name. Default: empty
#  $search:
#    Array of search domains. Default: empty
#  $sortlist:
#    Array of sortlist IP-address-netmask pairs. Default: empty
#  $options:
#    Array of options. Default: empty
#
# Sample Usage :
#  resolvconf::file { '/etc/resolvconf/resolv.conf.d/tail':
#    nameserver => [ '8.8.8.8', '8.8.4.4' ],
#    search     => [ 'example.lan', 'example.com' ],
#  }
#
define resolvconf::file (
  $ensure     = present,
  $header     = 'This file is managed by Puppet, do not edit',
  $nameserver = [],
  $domain     = '',
  $search     = [],
  $sortlist   = [],
  $options    = [],
) {

  if $domain != '' and $search != [] {
    fail('The "domain" and "search" parameters are mutually exclusive.')
  }

  if $nameserver != [] {
    file { $title:
      ensure  => $ensure,
      owner   => 'root',
      group   => 'root',
      mode    => '0644',
      content => template("${module_name}/resolv.conf.erb"),
    }
  }

}

