# Class: supervisord::params
#
# Default parameters for supervisord
#
class supervisord::params {
  # sort out init params for different OS families
  case $::osfamily {
    'RedHat': {
      $unix_socket_group = 'nobody'
      $install_init      = true
      case $::operatingsystem {
        'Amazon': {
          $init_type              = 'init'
          $init_script            = '/etc/init.d/supervisord'
          $init_defaults          = '/etc/sysconfig/supervisord'
          $executable_path        = '/usr/local/bin'
        }
        default: {
          case $::operatingsystemmajrelease {
            '7': {
              $init_type     = 'systemd'
              $init_script   = '/etc/systemd/system/supervisord.service'
              $init_defaults = false
            }
            default: {
              $init_type     = 'init'
              $init_script   = '/etc/init.d/supervisord'
              $init_defaults = '/etc/sysconfig/supervisord'
            }
          }
          $executable_path = '/usr/bin'
        }
      }
    }
    'Suse': {
      $init_type         = 'init'
      $init_defaults     = '/etc/sysconfig/supervisor'
      $init_script       = '/etc/init.d/supervisord'
      $unix_socket_group = 'nobody'
      $install_init      = true
      $executable_path   = '/usr/local/bin'
    }
    'Debian': {
      case $::operatingsystem {
        'Ubuntu': {
          if versioncmp($::operatingsystemmajrelease, '15.10') > 0 {
            $init_type     = 'systemd'
            $init_script   = '/etc/systemd/system/supervisord.service'
            $init_defaults = false
          } else {
            $init_type     = 'init'
            $init_script   = '/etc/init.d/supervisord'
            $init_defaults = '/etc/default/supervisor'
          }
        }
        default: {
          case $::operatingsystemmajrelease {
            '8': {
              $init_type     = 'systemd'
              $init_script   = '/etc/systemd/system/supervisord.service'
              $init_defaults = false
            }
            default: {
              $init_type     = 'init'
              $init_script   = '/etc/init.d/supervisord'
              $init_defaults = '/etc/default/supervisor'
            }
          }
        }
      }
      $unix_socket_group = 'nogroup'
      $install_init      = true
      $executable_path   = '/usr/local/bin'
    }
    default:  {
      $init_defaults     = false
      $unix_socket_group = 'nogroup'
      $install_init      = false
      $executable_path   = '/usr/local/bin'
    }
  }

  $init_mode = $init_type ? {
    'systemd' => '0644',
    'init'    => '0755',
    default   => '0755'
  }

  $init_script_template   = "supervisord/init/${::osfamily}/${init_type}.erb"
  $init_defaults_template = "supervisord/init/${::osfamily}/defaults.erb"

  # default supervisord params
  $package_ensure          = 'installed'
  $package_provider        = 'pip'
  $package_install_options = undef
  $service_manage          = true
  $service_ensure          = 'running'
  $service_enable          = true
  $service_name            = 'supervisord'
  $service_restart         = undef
  $package_name            = 'supervisor'
  $executable              = "${executable_path}/supervisord"
  $executable_ctl          = "${executable_path}/supervisorctl"

  $scl_enabled             = false
  $scl_script              = '/opt/rh/python27/enable'

  $run_path                = '/var/run'
  $pid_file                = 'supervisord.pid'
  $log_path                = '/var/log/supervisor'
  $log_file                = 'supervisord.log'
  $logfile_maxbytes        = '50MB'
  $logfile_backups         = '10'
  $log_level               = 'info'
  $nodaemon                = false
  $minfds                  = '1024'
  $minprocs                = '200'
  $umask                   = '022'
  $manage_config           = true
  $config_include          = '/etc/supervisor.d'
  $config_file             = '/etc/supervisord.conf'
  $config_file_mode        = '0644'
  $setuptools_url          = 'https://bootstrap.pypa.io/ez_setup.py'

  $ctl_socket              = 'unix'

  $unix_socket             = true
  $unix_socket_file        = 'supervisor.sock'
  $unix_socket_mode        = '0700'
  $unix_socket_owner       = 'nobody'
  $unix_auth               = false
  $unix_username           = undef
  $unix_password           = undef

  $inet_server             = false
  $inet_server_hostname    = '127.0.0.1'
  $inet_server_port        = '9001'
  $inet_auth               = false
  $inet_username           = undef
  $inet_password           = undef
  $user                    = 'root'
  $group                   = 'root'
}
