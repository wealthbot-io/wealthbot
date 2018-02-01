# Class apache::mod::jk
#
# Manages mod_jk connector
#
# All parameters are optional. When undefined, some receive default values,
# while others cause an optional directive to be absent
#
# For help on parameters, pls see official reference at:
# https://tomcat.apache.org/connectors-doc/reference/apache.html
#
class apache::mod::jk (
  # Binding to mod_jk
  Optional[String] $ip         = undef,
  Integer          $port       = 80,
  Boolean          $add_listen = true,
  # Conf file content
  $workers_file                = undef,
  $worker_property             = {},
  $logroot                     = undef,
  $shm_file                    = 'jk-runtime-status',
  $shm_size                    = undef,
  $mount_file                  = undef,
  $mount_file_reload           = undef,
  $mount                       = {},
  $un_mount                    = {},
  $auto_alias                  = undef,
  $mount_copy                  = undef,
  $worker_indicator            = undef,
  $watchdog_interval           = undef,
  $log_file                    = 'mod_jk.log',
  $log_level                   = undef,
  $log_stamp_format            = undef,
  $request_log_format          = undef,
  $extract_ssl                 = undef,
  $https_indicator             = undef,
  $sslprotocol_indicator       = undef,
  $certs_indicator             = undef,
  $cipher_indicator            = undef,
  $certchain_prefix            = undef,
  $session_indicator           = undef,
  $keysize_indicator           = undef,
  $local_name_indicator        = undef,
  $ignore_cl_indicator         = undef,
  $local_addr_indicator        = undef,
  $local_port_indicator        = undef,
  $remote_host_indicator       = undef,
  $remote_addr_indicator       = undef,
  $remote_port_indicator       = undef,
  $remote_user_indicator       = undef,
  $auth_type_indicator         = undef,
  $options                     = [],
  $env_var                     = {},
  $strip_session               = undef,
  # Location list
  # See comments in template mod/jk.conf.erb
  $location_list               = [],
  # Workers file content
  # See comments in template mod/jk/workers.properties.erb
  $workers_file_content        = {},
  # Mount file content
  # See comments in template mod/jk/uriworkermap.properties.erb
  $mount_file_content          = {},
){

  # Provides important variables
  include ::apache
  # Manages basic module config
  ::apache::mod { 'jk': }

  # Binding to mod_jk
  if $add_listen {
    $_ip = $ip ? {
      undef   => $facts['ipaddress'],
      default => $ip,
    }
    ensure_resource('apache::listen', "${_ip}:${port}", {})
  }

  # File resource common parameters
  File {
    ensure  => file,
    mode    => $::apache::file_mode,
    notify  => Class['apache::service'],
  }

  # Shared memory and log paths
  # If logroot unspecified, use default
  $log_dir = $logroot ? {
    undef   => $::apache::logroot,
    default => $logroot,
  }
  # If absolute path or pipe, use as-is
  # If relative path, prepend with log directory
  # If unspecified, use default
  $shm_path = $shm_file ? {
    undef       => "${log_dir}/jk-runtime-status",
    /^\"?[|\/]/ => $shm_file,
    default     => "${log_dir}/${shm_file}",
  }
  $log_path = $log_file ? {
    undef       => "${log_dir}/mod_jk.log",
    /^\"?[|\/]/ => $log_file,
    default     => "${log_dir}/${log_file}",
  }

  # Main config file
  $mod_dir = $::apache::mod_dir
  file {'jk.conf':
    path    => "${mod_dir}/jk.conf",
    content => template('apache/mod/jk.conf.erb'),
    require => [
      Exec["mkdir ${mod_dir}"],
      File[$mod_dir],
    ],
  }

  # Workers file
  if $workers_file != undef {
    $workers_path = $workers_file ? {
      /^\//   => $workers_file,
      default => "${apache::httpd_dir}/${workers_file}",
    }
    file { $workers_path:
      content => template('apache/mod/jk/workers.properties.erb'),
      require => Package['httpd'],
    }
  }

  # Mount file
  if $mount_file != undef {
    $mount_path = $mount_file ? {
      /^\//   => $mount_file,
      default => "${apache::httpd_dir}/${mount_file}",
    }
    file { $mount_path:
      content => template('apache/mod/jk/uriworkermap.properties.erb'),
      require => Package['httpd'],
    }
  }

}
