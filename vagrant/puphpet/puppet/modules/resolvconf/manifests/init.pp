# Class: resolvconf
#
# Manage /etc/resolv.conf with puppet. See resolv.conf(5).
#
# This class is just a wrapper around resolvconf::file for the main file.
#
# Sample Usage :
#  class { 'resolvconf':
#    nameserver => [ '8.8.8.8', '8.8.4.4' ],
#    search     => [ 'example.lan', 'example.com' ],
#  }
#
class resolvconf (
  $header     = 'This file is managed by Puppet, do not edit',
  $nameserver = [],
  $domain     = '',
  $search     = [],
  $sortlist   = [],
  $options    = [],
) {

  resolvconf::file { '/etc/resolv.conf':
    header     => $header,
    nameserver => $nameserver,
    domain     => $domain,
    search     => $search,
    sortlist   => $sortlist,
    options    => $options,
  }

}

