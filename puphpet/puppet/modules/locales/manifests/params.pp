# Default params for locales
class locales::params {
  $lc_ctype            = undef
  $lc_collate          = undef
  $lc_time             = undef
  $lc_numeric          = undef
  $lc_monetary         = undef
  $lc_messages         = undef
  $lc_paper            = undef
  $lc_name             = undef
  $lc_address          = undef
  $lc_telephone        = undef
  $lc_measurement      = undef
  $lc_identification   = undef
  $lc_all              = undef
  # Required for Suse - ignored for others
  $root_uses_lang      = 'ctype'  # if set to 'ctype', root will be stay POSIX, set to 'yes' to change root as well
  $installed_languages = ''       # blank for english, otherwise space seperated list.  Used by Yast2 only.
  $auto_detect_utf8    = 'no'     # Workaround for missing forward of LANG and LC variables of e.g. ssh login connections.
  $input_method        = ''       # A default input method to be used in X11. For more details see the comments at the top of /etc/X11/xim

  case $::operatingsystem {
    /(Ubuntu|Debian|LinuxMint)/: {

      $default_file      = '/etc/default/locale'
      $locale_gen_cmd    = '/usr/sbin/locale-gen'
      $update_locale_cmd = '/usr/sbin/update-locale'
      $supported_locales = '/usr/share/i18n/SUPPORTED' # ALL locales support

      case $::operatingsystem {
        /(Ubuntu|LinuxMint)/: {
          $package     = 'locales'
          case $::lsbdistcodename {
            'hardy': {
              $update_locale_pkg = 'belocs-locales-bin'
            }
            default: {
              $update_locale_pkg = 'libc-bin'
            }
          }
          if versioncmp($::operatingsystemrelease, '16.04') >= 0 {
            $config_file = '/etc/locale.gen'
          } else {
            $config_file = '/var/lib/locales/supported.d/local'
          }
        }
        'Debian' : {
          $package = 'locales-all'
          # If config_file is not set, we will end up with the error message:
          # Missing title. The title expression resulted in undef at [init.pp
          # at definition of file { $config_file: ]
          # even if this resource is inside the branch of an if which will never
          # be run.
          $config_file = '/etc/locale.gen'
          $update_locale_pkg = 'locales'
        }
        default: {
          $config_file = '/etc/locale.gen'
          $update_locale_pkg = false
        }
      }
    }
    /(RedHat|CentOS|OracleLinux|Fedora|Amazon|CloudLinux)/: {
      $package = 'glibc-common'
      $locale_gen_cmd = undef
      $update_local_pkg = undef
      #$config_file = '/etc/locale.gen'
      $update_locale_cmd = undef
      $config_file = '/var/lib/locales/supported.d/local'
      $update_locale_pkg = false
      $supported_locales = undef
      if versioncmp($::operatingsystemmajrelease, '7') >= 0 {
        $default_file      = '/etc/locale.conf'
      } else {
        $default_file      = '/etc/sysconfig/i18n'
      }
    }
    /(SuSE|SLES)/: {
      $package = 'glibc-locale'
      $default_file      = '/etc/sysconfig/language'
      $locale_gen_cmd = undef
      $update_local_pkg = undef
      $update_locale_cmd = undef
      $config_file = undef
      $update_locale_pkg = false
    }
    /(Archlinux)/: {
      $package           = 'glibc'
      $update_locale_pkg = false
      $locale_gen_cmd    = '/usr/bin/locale-gen' # /usr/sbin will also work but considered legacy
      $config_file       = '/etc/locales.gen'
      $default_file      = '/etc/locale.conf'
    }
    default: {
      fail("Unsupported platform: ${::operatingsystem}")
    }
  }
}
