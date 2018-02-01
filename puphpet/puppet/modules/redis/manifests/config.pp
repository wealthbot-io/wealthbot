# = Class: redis::config
#
# This class provides configuration for Redis.
#
class redis::config {

  File {
    owner  => $::redis::config_owner,
    group  => $::redis::config_group,
    mode   => $::redis::config_file_mode,
  }

  file { $::redis::config_dir:
    ensure => directory,
    mode   => $::redis::config_dir_mode,
  }

  file {$::redis::log_dir:
    ensure => directory,
    group  => $::redis::service_group,
    mode   => $::redis::log_dir_mode,
    owner  => $::redis::service_user,
  }

  file {$::redis::workdir:
    ensure => directory,
    group  => $::redis::service_group,
    mode   => $::redis::workdir_mode,
    owner  => $::redis::service_user,
  }

  if $::redis::default_install {
    redis::instance {'default':
      pid_file            => $::redis::pid_file,
      log_file            => $::redis::log_file,
      manage_service_file => $::redis::manage_service_file,
      unixsocket          => $::redis::unixsocket,
      workdir             => $::redis::workdir,
    }
  }

  if $::redis::ulimit {
    contain ::redis::ulimit
  }

  $service_provider_lookup = pick(getvar('service_provider'), false)

  if $service_provider_lookup != 'systemd' {
    case $::operatingsystem {
      'Debian': {
        if $::lsbdistcodename == 'wheezy' {
          $var_run_redis_mode  = '2755'
          $var_run_redis_group = 'redis'
        } else {
          $var_run_redis_group = $::redis::config_group
          $var_run_redis_mode = '2775'
        }
      }
      default: {
        $var_run_redis_mode = '0755'
        $var_run_redis_group = $::redis::config_group
      }
    }

    file { '/var/run/redis':
      ensure => 'directory',
      owner  => $::redis::config_owner,
      group  => $var_run_redis_group,
      mode   => $var_run_redis_mode,
    }
  }

  # Adjust /etc/default/redis-server on Debian systems
  case $::osfamily {
    'Debian': {
      file { '/etc/default/redis-server':
        ensure => present,
        group  => $::redis::config_group,
        mode   => $::redis::config_file_mode,
        owner  => $::redis::config_owner,
      }
    }

    default: {
    }
  }
}
