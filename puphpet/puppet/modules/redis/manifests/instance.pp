# redis::instance
#
# This is an defined type to allow the configuration of
# multiple redis instances on one machine without conflicts
#
# @summary Allows the configuration of multiple redis configurations on one machine
#
# @example
#   redis::instance {'6380':
#     port => '6380',
#   }
#
# @param [String] activerehashing   Enable/disable active rehashing.
# @param [String] aof_load_truncated   Enable/disable loading truncated AOF file
# @param [String] aof_rewrite_incremental_fsync   Enable/disable fsync for AOF file
# @param [String] appendfilename   The name of the append only file
# @param [String] appendfsync   Adjust fsync mode. Valid options: always, everysec, no. Default:  everysec
# @param [String] appendonly   Enable/disable appendonly mode.
# @param [String] auto_aof_rewrite_min_size   Adjust minimum size for auto-aof-rewrite.
# @param [String] auto_aof_rewrite_percentage   Adjust percentatge for auto-aof-rewrite.
# @param [String] bind   Configure which IP address to listen on.
# @param [String] config_dir   Directory containing the configuration files.
# @param [String] config_dir_mode   Adjust mode for directory containing configuration files.
# @param [String] config_file_orig   The location and name of a config file that provides the source
# @param [String] config_file   Adjust main configuration file.
# @param [String] config_file_mode   Adjust permissions for configuration files.
# @param [String] config_group   Adjust filesystem group for config files.
# @param [String] config_owner   Adjust filesystem owner for config files.
# @param [String] conf_template   Define which template to use.
# @param [String] daemonize   Have Redis run as a daemon.
# @param [String] databases   Set the number of databases.
# @param [String] dbfilename   The filename where to dump the DB
# @param [String] extra_config_file   Description
# @param [String] hash_max_ziplist_entries   Set max ziplist entries for hashes.
# @param [String] hash_max_ziplist_value   Set max ziplist values for hashes.
# @param [String] hll_sparse_max_bytes   HyperLogLog sparse representation bytes limit
# @param [String] hz   Set redis background tasks frequency
# @param [String] latency_monitor_threshold   Latency monitoring threshold in milliseconds
# @param [String] list_max_ziplist_entries   Set max ziplist entries for lists.
# @param [String] list_max_ziplist_value   Set max ziplist values for lists.
# @param [String] log_dir   Specify directory where to write log entries.
# @param [String] log_dir_mode   Adjust mode for directory containing log files.
# @param [String] log_file   Specify file where to write log entries.
# @param [String] log_level   Specify the server verbosity level.
# @param [String] masterauth   If the master is password protected (using the "requirepass" configuration
# @param [String] maxclients   Set the max number of connected clients at the same time.
# @param [String] maxmemory   Don't use more memory than the specified amount of bytes.
# @param [String] maxmemory_policy   How Redis will select what to remove when maxmemory is reached.
# @param [String] maxmemory_samples   Select as well the sample size to check.
# @param [String] min_slaves_max_lag   The lag in seconds
# @param [String] min_slaves_to_write   Minimum number of slaves to be in "online" state
# @param [String] no_appendfsync_on_rewrite   If you have latency problems turn this to 'true'. Otherwise leave it as
# @param [String] notify_keyspace_events   Which events to notify Pub/Sub clients about events happening
# @param [String] pid_file   Where to store the pid.
# @param [String] port   Configure which port to listen on.
# @param [String] protected_mode  Whether protected mode is enabled or not.  Only applicable when no bind is set.
# @param [String] rdbcompression   Enable/disable compression of string objects using LZF when dumping.
# @param [String] repl_backlog_size   The replication backlog size
# @param [String] repl_backlog_ttl   The number of seconds to elapse before freeing backlog buffer
# @param [String] repl_disable_tcp_nodelay   Enable/disable TCP_NODELAY on the slave socket after SYNC
# @param [String] repl_ping_slave_period   Slaves send PINGs to server in a predefined interval. It's possible
# @param [String] repl_timeout   Set the replication timeout for:
# @param [String] requirepass   Require clients to issue AUTH <PASSWORD> before processing any
#   other commands.
# @param [String] save_db_to_disk   Set if save db to disk.
# @param [String] save_db_to_disk_interval    save the dataset every N seconds if there are at least M changes in the dataset
# @param [String] service_enable   Enable/disable daemon at boot.
# @param [String] service_ensure   Specify if the server should be running.
# @param [String] service_group   Specify which group to run as.
# @param [String] service_hasrestart   Does the init script support restart?
# @param [String] service_hasstatus   Does the init script support status?
# @param [String] service_user   Specify which user to run as.
# @param [String] set_max_intset_entries   The following configuration setting sets the limit in the size of the
#   set in order to use this special memory saving encoding.
#   Default: 512
# @param [String] slave_priority   The priority number for slave promotion by Sentinel
# @param [String] slave_read_only   You can configure a slave instance to accept writes or not.
# @param [String] slave_serve_stale_data   When a slave loses its connection with the master, or when the replication
#   is still in progress, the slave can act in two different ways:
#   1) if slave-serve-stale-data is set to 'yes' (the default) the slave will
#      still reply to client requests, possibly with out of date data, or the
#      data set may just be empty if this is the first synchronization.
#
#   2) if slave-serve-stale-data is set to 'no' the slave will reply with
#      an error "SYNC with master in progress" to all the kind of commands
#      but to INFO and SLAVEOF.
#
#   Default: true
#
# @param [String] slaveof   Use slaveof to make a Redis instance a copy of another Redis server.
# @param [String] slowlog_log_slower_than   Tells Redis what is the execution time, in microseconds, to exceed
#   in order for the command to get logged.
#   Default: 10000
#
# @param [String] slowlog_max_len   Tells Redis what is the length to exceed in order for the command
#   to get logged.
#   Default: 1024
#
# @param [String] stop_writes_on_bgsave_error   If false then Redis will continue to work as usual even if there
#   are problems with disk, permissions, and so forth.
#   Default: true
#
# @param [String] syslog_enabled   Enable/disable logging to the system logger.
# @param [String] syslog_facility   Specify the syslog facility.
#   Must be USER or between LOCAL0-LOCAL7.
#   Default: undef
#
# @param [String] tcp_backlog   Sets the TCP backlog
# @param [String] tcp_keepalive   TCP keepalive.
# @param [String] timeout   Close the connection after a client is idle for N seconds (0 to disable).
# @param [String] ulimit   Limit the use of system-wide resources.
# @param [String] unixsocket   Define unix socket path
# @param [String] unixsocketperm   Define unix socket file permissions
# @param [String] workdir   The DB will be written inside this directory, with the filename specified
#   above using the 'dbfilename' configuration directive.
#   Default: /var/lib/redis/
# @param [String] workdir_mode   Adjust mode for data directory.
# @param [String] zset_max_ziplist_entries   Set max entries for sorted sets.
# @param [String] zset_max_ziplist_value   Set max values for sorted sets.
# @param [String] cluster_enabled   Enables redis 3.0 cluster functionality
# @param [String] cluster_config_file   Config file for saving cluster nodes configuration. This file is never touched by humans.
#   Only set if cluster_enabled is true
#   Default: nodes.conf
# @param [String] cluster_node_timeout   Node timeout
#   Only set if cluster_enabled is true
#   Default: 5000
define redis::instance(
  $activerehashing               = $::redis::activerehashing,
  $aof_load_truncated            = $::redis::aof_load_truncated,
  $aof_rewrite_incremental_fsync = $::redis::aof_rewrite_incremental_fsync,
  $appendfilename                = $::redis::appendfilename,
  $appendfsync                   = $::redis::appendfsync,
  $appendonly                    = $::redis::appendonly,
  $auto_aof_rewrite_min_size     = $::redis::auto_aof_rewrite_min_size,
  $auto_aof_rewrite_percentage   = $::redis::auto_aof_rewrite_percentage,
  $bind                          = $::redis::bind,
  $output_buffer_limit_slave     = $::redis::output_buffer_limit_slave,
  $output_buffer_limit_pubsub    = $::redis::output_buffer_limit_pubsub,
  $conf_template                 = $::redis::conf_template,
  $config_dir                    = $::redis::config_dir,
  $config_dir_mode               = $::redis::config_dir_mode,
  $config_file                   = $::redis::config_file,
  $config_file_mode              = $::redis::config_file_mode,
  $config_file_orig              = $::redis::config_file_orig,
  $config_group                  = $::redis::config_group,
  $config_owner                  = $::redis::config_owner,
  $daemonize                     = $::redis::daemonize,
  $databases                     = $::redis::databases,
  $dbfilename                    = $::redis::dbfilename,
  $extra_config_file             = $::redis::extra_config_file,
  $hash_max_ziplist_entries      = $::redis::hash_max_ziplist_entries,
  $hash_max_ziplist_value        = $::redis::hash_max_ziplist_value,
  $hll_sparse_max_bytes          = $::redis::hll_sparse_max_bytes,
  $hz                            = $::redis::hz,
  $latency_monitor_threshold     = $::redis::latency_monitor_threshold,
  $list_max_ziplist_entries      = $::redis::list_max_ziplist_entries,
  $list_max_ziplist_value        = $::redis::list_max_ziplist_value,
  $log_dir                       = $::redis::log_dir,
  $log_dir_mode                  = $::redis::log_dir_mode,
  $log_level                     = $::redis::log_level,
  $minimum_version               = $::redis::minimum_version,
  $masterauth                    = $::redis::masterauth,
  $maxclients                    = $::redis::maxclients,
  $maxmemory                     = $::redis::maxmemory,
  $maxmemory_policy              = $::redis::maxmemory_policy,
  $maxmemory_samples             = $::redis::maxmemory_samples,
  $min_slaves_max_lag            = $::redis::min_slaves_max_lag,
  $min_slaves_to_write           = $::redis::min_slaves_to_write,
  $no_appendfsync_on_rewrite     = $::redis::no_appendfsync_on_rewrite,
  $notify_keyspace_events        = $::redis::notify_keyspace_events,
  $managed_by_cluster_manager    = $::redis::managed_by_cluster_manager,
  $package_ensure                = $::redis::package_ensure,
  $port                          = $::redis::port,
  $protected_mode                = $::redis::protected_mode,
  $rdbcompression                = $::redis::rdbcompression,
  $repl_backlog_size             = $::redis::repl_backlog_size,
  $repl_backlog_ttl              = $::redis::repl_backlog_ttl,
  $repl_disable_tcp_nodelay      = $::redis::repl_disable_tcp_nodelay,
  $repl_ping_slave_period        = $::redis::repl_ping_slave_period,
  $repl_timeout                  = $::redis::repl_timeout,
  $requirepass                   = $::redis::requirepass,
  $save_db_to_disk               = $::redis::save_db_to_disk,
  $save_db_to_disk_interval      = $::redis::save_db_to_disk_interval,
  $service_user                  = $::redis::service_user,
  $set_max_intset_entries        = $::redis::set_max_intset_entries,
  $slave_priority                = $::redis::slave_priority,
  $slave_read_only               = $::redis::slave_read_only,
  $slave_serve_stale_data        = $::redis::slave_serve_stale_data,
  $slaveof                       = $::redis::slaveof,
  $slowlog_log_slower_than       = $::redis::slowlog_log_slower_than,
  $slowlog_max_len               = $::redis::slowlog_max_len,
  $stop_writes_on_bgsave_error   = $::redis::stop_writes_on_bgsave_error,
  $syslog_enabled                = $::redis::syslog_enabled,
  $syslog_facility               = $::redis::syslog_facility,
  $tcp_backlog                   = $::redis::tcp_backlog,
  $tcp_keepalive                 = $::redis::tcp_keepalive,
  $timeout                       = $::redis::timeout,
  $unixsocketperm                = $::redis::unixsocketperm,
  $ulimit                        = $::redis::ulimit,
  $workdir_mode                  = $::redis::workdir_mode,
  $zset_max_ziplist_entries      = $::redis::zset_max_ziplist_entries,
  $zset_max_ziplist_value        = $::redis::zset_max_ziplist_value,
  $cluster_enabled               = $::redis::cluster_enabled,
  $cluster_config_file           = $::redis::cluster_config_file,
  $cluster_node_timeout          = $::redis::cluster_node_timeout,
  $service_ensure                = $::redis::service_ensure,
  $service_enable                = $::redis::service_enable,
  $service_group                 = $::redis::service_group,
  $service_hasrestart            = $::redis::service_hasrestart,
  $service_hasstatus             = $::redis::service_hasstatus,
  # Defaults for redis::instance
  $manage_service_file           = true,
  $log_file                      = undef,
  $pid_file                      = "/var/run/redis/redis-server-${name}.pid",
  $unixsocket                    = "/var/run/redis/redis-server-${name}.sock",
  $workdir                       = "${::redis::workdir}/redis-server-${name}",
) {

  if $title == 'default' {
    $redis_file_name_orig = $config_file_orig
    $redis_file_name      = $config_file
  } else {
    $redis_server_name    = "redis-server-${name}"
    $redis_file_name_orig = sprintf('%s/%s.%s', dirname($config_file_orig), $redis_server_name, 'conf.puppet')
    $redis_file_name      = sprintf('%s/%s.%s', dirname($config_file), $redis_server_name, 'conf')
  }

  if $log_dir != $::redis::log_dir {
    file { $log_dir:
      ensure => directory,
      group  => $service_group,
      mode   => $log_dir_mode,
      owner  => $service_user,
    }
  }

  $_real_log_file = $log_file ? {
    undef   => "${log_dir}/redis-server-${name}.log",
    default => $log_file,
  }

  if $workdir != $::redis::workdir {
    file { $workdir:
      ensure => directory,
      group  => $service_group,
      mode   => $workdir_mode,
      owner  => $service_user,
    }
  }

  if $manage_service_file {
    $service_provider_lookup = pick(getvar('service_provider'), false)

    if $service_provider_lookup == 'systemd' {

      file { "/etc/systemd/system/${redis_server_name}.service":
        ensure  => file,
        owner   => 'root',
        group   => 'root',
        mode    => '0644',
        content => template('redis/service_templates/redis.service.erb'),
      }
      ~> Exec['systemd-reload-redis']

      if $title != 'default' {
        service { $redis_server_name:
          ensure     => $service_ensure,
          enable     => $service_enable,
          hasrestart => $service_hasrestart,
          hasstatus  => $service_hasstatus,
          subscribe  => [
            File["/etc/systemd/system/${redis_server_name}.service"],
            Exec["cp -p ${redis_file_name_orig} ${redis_file_name}"],
          ],
        }
      }

    } else {

      file { "/etc/init.d/${redis_server_name}":
        ensure  => file,
        mode    => '0755',
        content => template("redis/service_templates/redis.${::osfamily}.erb"),
      }

      if $title != 'default' {
        service { $redis_server_name:
          ensure     => $service_ensure,
          enable     => $service_enable,
          hasrestart => $service_hasrestart,
          hasstatus  => $service_hasstatus,
          subscribe  => [
            File["/etc/init.d/${redis_server_name}"],
            Exec["cp -p ${redis_file_name_orig} ${redis_file_name}"],
          ],
        }
      }
    }
  }

  File {
    owner  => $config_owner,
    group  => $config_group,
    mode   => $config_file_mode,
  }

  file {$redis_file_name_orig:
    ensure  => file,
  }

  exec {"cp -p ${redis_file_name_orig} ${redis_file_name}":
    path        => '/usr/bin:/bin',
    subscribe   => File[$redis_file_name_orig],
    refreshonly => true,
  }

  if $package_ensure =~ /^([0-9]+:)?[0-9]+\.[0-9]/ {
    if ':' in $package_ensure {
      $_redis_version_real = split($package_ensure, ':')
      $redis_version_real = $_redis_version_real[1]
    } else {
      $redis_version_real = $package_ensure
    }
  } else {
    $redis_version_real = pick(getvar('redis_server_version'), $minimum_version)
  }

  if ($redis_version_real and $conf_template == 'redis/redis.conf.erb') {
    case $redis_version_real {
      /^2.4./: {
        File[$redis_file_name_orig] { content => template('redis/redis.conf.2.4.10.erb') }
      }
      /^2.8./: {
        File[$redis_file_name_orig] { content => template('redis/redis.conf.2.8.erb') }
      }
      /^3.2./: {
        File[$redis_file_name_orig] { content => template('redis/redis.conf.3.2.erb') }
      }
      default: {
        File[$redis_file_name_orig] { content => template($conf_template) }
      }
    }
  } else {
    File[$redis_file_name_orig] { content => template($conf_template) }
  }

}
