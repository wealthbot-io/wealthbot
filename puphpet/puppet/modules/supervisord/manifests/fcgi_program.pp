# Define: supervisord::fcgi_program
#
# This define creates an eventlistener configuration file
#
# Documentation on parameters available at:
# http://supervisord.org/configuration.html#fcgi-program-x-section-settings
#
define supervisord::fcgi_program(
  $command,
  $socket,
  $ensure                  = present,
  $ensure_process          = 'running',
  $socket_owner            = undef,
  $socket_mode             = undef,
  $env_var                 = undef,
  $process_name            = undef,
  $numprocs                = undef,
  $numprocs_start          = undef,
  $priority                = undef,
  $autostart               = undef,
  $autorestart             = undef,
  $startsecs               = undef,
  $startretries            = undef,
  $exitcodes               = undef,
  $stopsignal              = undef,
  $stopwaitsecs            = undef,
  $stopasgroup             = undef,
  $killasgroup             = undef,
  $user                    = undef,
  $redirect_stderr         = undef,
  $stdout_logfile          = "fcgi-program_${name}.log",
  $stdout_logfile_maxbytes = undef,
  $stdout_logfile_backups  = undef,
  $stdout_capture_maxbytes = undef,
  $stdout_events_enabled   = undef,
  $stderr_logfile          = "fcgi-program_${name}.error",
  $stderr_logfile_maxbytes = undef,
  $stderr_logfile_backups  = undef,
  $stderr_capture_maxbytes = undef,
  $stderr_events_enabled   = undef,
  $environment             = undef,
  $program_environment     = undef,
  $directory               = undef,
  $umask                   = undef,
  $serverurl               = undef,
  $config_file_mode        = '0644'
) {

  include supervisord

  # parameter validation
  validate_legacy(String, 'validate_string', $command)
  validate_legacy('Optional[String]', 'validate_re', $ensure_process, ['running', 'stopped', 'removed', 'unmanaged'])
  validate_legacy('Optional[String]', 'validate_re', $socket, ['^tcp:\/\/.*:\d+$', '^unix:\/\/\/'])
  if $process_name { validate_legacy(String, 'validate_string', $process_name) }
  if $numprocs { if $numprocs !~ Integer { validate_legacy('Optional[String]', 'validate_re', $numprocs, ['^\d+'])} }
  if $numprocs_start { if $numprocs_start !~ Integer { validate_legacy('Optional[String]', 'validate_re', $numprocs_start, ['^\d+'])} }
  if $priority { if $priority !~ Integer { validate_legacy('Optional[String]', 'validate_re', $priority, ['^\d+']) } }
  if $autostart { if $autostart !~ Boolean { validate_legacy('Optional[String]', 'validate_re', $autostart, ['true', 'false']) } }
  if $autorestart { if $autorestart !~ Boolean { validate_legacy('Optional[String]', 'validate_re', $autorestart, ['true', 'false', 'unexpected']) } }
  if $startsecs { if $startsecs !~ Integer { validate_legacy('Optional[String]', 'validate_re', $startsecs, ['^\d+'])} }
  if $startretries { if $startretries !~ Integer { validate_legacy('Optional[String]', 'validate_re', $startretries, ['^\d+'])} }
  if $exitcodes { validate_legacy(String, 'validate_string', $exitcodes)}
  if $stopsignal { validate_legacy('Optional[String]', 'validate_re', $stopsignal, ['TERM', 'HUP', 'INT', 'QUIT', 'KILL', 'USR1', 'USR2']) }
  if $stopwaitsecs { if $stopwaitsecs !~ Integer { validate_legacy('Optional[String]', 'validate_re', $stopwaitsecs, ['^\d+'])} }
  if $stopasgroup { validate_legacy(Boolean, 'validate_bool', $stopasgroup) }
  if $killasgroup { validate_legacy(Boolean, 'validate_bool', $killasgroup) }
  if $user { validate_legacy(String, 'validate_string', $user) }
  if $redirect_stderr { validate_legacy(Boolean, 'validate_bool', $redirect_stderr) }
  validate_legacy(String, 'validate_string', $stdout_logfile)
  if $stdout_logfile_maxbytes { validate_legacy(String, 'validate_string', $stdout_logfile_maxbytes) }
  if $stdout_logfile_backups { if $stdout_logfile_backups !~ Integer { validate_legacy('Optional[String]', 'validate_re', $stdout_logfile_backups, ['^\d+'])} }
  if $stdout_capture_maxbytes { validate_legacy(String, 'validate_string', $stdout_capture_maxbytes) }
  if $stdout_events_enabled { validate_legacy(Boolean, 'validate_bool', $stdout_events_enabled) }
  validate_legacy(String, 'validate_string', $stderr_logfile)
  if $stderr_logfile_maxbytes { validate_legacy(String, 'validate_string', $stderr_logfile_maxbytes) }
  if $stderr_logfile_backups { if $stderr_logfile_backups !~ Integer { validate_legacy('Optional[String]', 'validate_re', $stderr_logfile_backups, ['^\d+'])} }
  if $stderr_capture_maxbytes { validate_legacy(String, 'validate_string', $stderr_capture_maxbytes) }
  if $stderr_events_enabled { validate_legacy(Boolean, 'validate_bool', $stderr_events_enabled) }
  if $directory { validate_legacy(Stdlib::Compat::Absolute_Path, 'validate_absolute_path', $directory) }
  if $umask { validate_legacy('Optional[String]', 'validate_re', $umask, ['^[0-7][0-7][0-7]$']) }
  validate_legacy('Optional[String]', 'validate_re', $config_file_mode, ['^0[0-7][0-7][0-7]$'])

  # create the correct log variables
  $stdout_logfile_path = $stdout_logfile ? {
        /(NONE|AUTO|syslog)/ => $stdout_logfile,
        /^\//                => $stdout_logfile,
        default              => "${supervisord::log_path}/${stdout_logfile}",
  }

  $stderr_logfile_path = $stderr_logfile ? {
        /(NONE|AUTO|syslog)/ => $stderr_logfile,
        /^\//                => $stderr_logfile,
        default              => "${supervisord::log_path}/${stderr_logfile}",
  }

  # Handle deprecated $environment variable
  if $environment { notify {'[supervisord] *** DEPRECATED WARNING ***: $program_environment has replaced $environment':}}
  $_program_environment = $program_environment ? {
    undef   => $environment,
    default => $program_environment
  }

  # convert environment data into a csv
  if $env_var {
    $env_hash = hiera_hash($env_var)
    validate_legacy(Hash, 'validate_hash', $env_hash)
    $env_string = hash2csv($env_hash)
  }
  elsif $_program_environment {
    validate_legacy(Hash, 'validate_hash', $_program_environment)
    $env_string = hash2csv($_program_environment)
  }

  $conf = "${supervisord::config_include}/fcgi-program_${name}.conf"

  file { $conf:
    ensure  => $ensure,
    owner   => 'root',
    mode    => $config_file_mode,
    content => template('supervisord/conf/fcgi_program.erb'),
    notify  => Class['supervisord::reload']
  }

  case $ensure_process {
    'stopped': {
      supervisord::supervisorctl { "stop_${name}":
        command => 'stop',
        process => $name
      }
    }
    'removed': {
      supervisord::supervisorctl { "remove_${name}":
        command => 'remove',
        process => $name
      }
    }
    'running': {
      supervisord::supervisorctl { "start_${name}":
        command => 'start',
        process => $name,
        unless  => 'running'
      }
    }
    default: { }
  }
}
