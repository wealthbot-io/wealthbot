class firewall::params {
  $package_ensure = 'present'
  case $::osfamily {
    'RedHat': {
      $service_name = 'iptables'
      $service_name_v6 = 'ip6tables'
      case $::operatingsystem {
        'Amazon': {
          $package_name = undef
        }
        'Fedora': {
          if versioncmp($::operatingsystemrelease, '15') >= 0 {
            $package_name = 'iptables-services'
          } else {
            $package_name = undef
          }
        }
        default: {
          if versioncmp($::operatingsystemrelease, '7.0') >= 0 {
            $package_name = 'iptables-services'
          } else {
            $package_name = 'iptables-ipv6'
          }
        }
      }
    }
    'Debian': {
      $service_name_v6 = undef
      case $::operatingsystem {
        'Debian': {
          if versioncmp($::operatingsystemrelease, '8.0') >= 0 {
            $service_name = 'netfilter-persistent'
            $package_name = 'iptables-persistent'
          } else {
            $service_name = 'iptables-persistent'
            $package_name = 'iptables-persistent'
          }

        }
        'Ubuntu': {
          if versioncmp($::operatingsystemrelease, '14.10') >= 0 {
            $service_name = 'netfilter-persistent'
            $package_name = 'iptables-persistent'
          } else {
            $service_name = 'iptables-persistent'
            $package_name = 'iptables-persistent'
          }

        }
        default: {
          $service_name = 'iptables-persistent'
          $package_name = 'iptables-persistent'
        }
      }
    }
    'Gentoo': {
      $service_name = ['iptables','ip6tables']
      $service_name_v6 = undef
      $package_name = 'net-firewall/iptables'
    }
    default: {
      $service_name_v6 = undef
      case $::operatingsystem {
        'Archlinux': {
          $service_name = ['iptables','ip6tables']
          $package_name = undef
        }
        default: {
          $service_name = 'iptables'
          $package_name = undef
        }
      }
    }
  }
}
