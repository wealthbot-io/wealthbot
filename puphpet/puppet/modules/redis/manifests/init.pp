# This class installs redis
#
# @example Default install
#   include redis
#
# @example Slave Node
#   class { '::redis':
#     bind    => '10.0.1.2',
#     slaveof => '10.0.1.1 6379',
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
# @param [String] default_install  Configure a default install of redis
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
# @param [String] manage_repo   Enable/disable upstream repository configuration.
# @param [String] manage_package   Enable/disable management of package
# @param [String] managed_by_cluster_manager Choose if redis will be managed by a cluster manager such as pacemaker or rgmanager
# @param [String] masterauth   If the master is password protected (using the "requirepass" configuration
# @param [String] maxclients   Set the max number of connected clients at the same time.
# @param [String] maxmemory   Don't use more memory than the specified amount of bytes.
# @param [String] maxmemory_policy   How Redis will select what to remove when maxmemory is reached.
# @param [String] maxmemory_samples   Select as well the sample size to check.
# @param [String] min_slaves_max_lag   The lag in seconds
# @param [String] min_slaves_to_write   Minimum number of slaves to be in "online" state
# @param [String] no_appendfsync_on_rewrite   If you have latency problems turn this to 'true'. Otherwise leave it as
# @param [String] notify_keyspace_events   Which events to notify Pub/Sub clients about events happening
# @param [String] notify_service   You may disable service reloads when config files change if you
# @param [String] package_ensure   Default action for package.
# @param [String] package_name   Upstream package name.
# @param [String] pid_file   Where to store the pid.
# @param [String] port   Configure which port to listen on.
# @param [String] protected_mode  Whether protected mode is enabled or not.  Only applicable when no bind is set.
# @param [String] ppa_repo   Specify upstream (Ubuntu) PPA entry.
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
# @param [String] service_manage   Specify if the service should be part of the catalog.
# @param [String] service_enable   Enable/disable daemon at boot.
# @param [String] service_ensure   Specify if the server should be running.
# @param [String] service_group   Specify which group to run as.
# @param [String] service_hasrestart   Does the init script support restart?
# @param [String] service_hasstatus   Does the init script support status?
# @param [String] service_name   Specify the service name for Init or Systemd.
# @param [String] service_provider   Specify the service provider to use
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
class redis (
  $activerehashing               = $::redis::params::activerehashing,
  $aof_load_truncated            = $::redis::params::aof_load_truncated,
  $aof_rewrite_incremental_fsync = $::redis::params::aof_rewrite_incremental_fsync,
  $appendfilename                = $::redis::params::appendfilename,
  $appendfsync                   = $::redis::params::appendfsync,
  $appendonly                    = $::redis::params::appendonly,
  $auto_aof_rewrite_min_size     = $::redis::params::auto_aof_rewrite_min_size,
  $auto_aof_rewrite_percentage   = $::redis::params::auto_aof_rewrite_percentage,
  $bind                          = $::redis::params::bind,
  $output_buffer_limit_slave     = $::redis::params::output_buffer_limit_slave,
  $output_buffer_limit_pubsub    = $::redis::params::output_buffer_limit_pubsub,
  $conf_template                 = $::redis::params::conf_template,
  $config_dir                    = $::redis::params::config_dir,
  $config_dir_mode               = $::redis::params::config_dir_mode,
  $config_file                   = $::redis::params::config_file,
  $config_file_mode              = $::redis::params::config_file_mode,
  $config_file_orig              = $::redis::params::config_file_orig,
  $config_group                  = $::redis::params::config_group,
  $config_owner                  = $::redis::params::config_owner,
  $daemonize                     = $::redis::params::daemonize,
  $databases                     = $::redis::params::databases,
  $default_install               = $::redis::params::default_install,
  $dbfilename                    = $::redis::params::dbfilename,
  $extra_config_file             = $::redis::params::extra_config_file,
  $hash_max_ziplist_entries      = $::redis::params::hash_max_ziplist_entries,
  $hash_max_ziplist_value        = $::redis::params::hash_max_ziplist_value,
  $hll_sparse_max_bytes          = $::redis::params::hll_sparse_max_bytes,
  $hz                            = $::redis::params::hz,
  $latency_monitor_threshold     = $::redis::params::latency_monitor_threshold,
  $list_max_ziplist_entries      = $::redis::params::list_max_ziplist_entries,
  $list_max_ziplist_value        = $::redis::params::list_max_ziplist_value,
  $log_dir                       = $::redis::params::log_dir,
  $log_dir_mode                  = $::redis::params::log_dir_mode,
  $log_file                      = $::redis::params::log_file,
  $log_level                     = $::redis::params::log_level,
  $manage_package                = $::redis::params::manage_package,
  $manage_repo                   = $::redis::params::manage_repo,
  $masterauth                    = $::redis::params::masterauth,
  $maxclients                    = $::redis::params::maxclients,
  $maxmemory                     = $::redis::params::maxmemory,
  $maxmemory_policy              = $::redis::params::maxmemory_policy,
  $maxmemory_samples             = $::redis::params::maxmemory_samples,
  $min_slaves_max_lag            = $::redis::params::min_slaves_max_lag,
  $min_slaves_to_write           = $::redis::params::min_slaves_to_write,
  $no_appendfsync_on_rewrite     = $::redis::params::no_appendfsync_on_rewrite,
  $notify_keyspace_events        = $::redis::params::notify_keyspace_events,
  $notify_service                = $::redis::params::notify_service,
  $managed_by_cluster_manager    = $::redis::params::managed_by_cluster_manager,
  $package_ensure                = $::redis::params::package_ensure,
  $package_name                  = $::redis::params::package_name,
  $pid_file                      = $::redis::params::pid_file,
  $port                          = $::redis::params::port,
  $protected_mode                = $::redis::params::protected_mode,
  $ppa_repo                      = $::redis::params::ppa_repo,
  $rdbcompression                = $::redis::params::rdbcompression,
  $repl_backlog_size             = $::redis::params::repl_backlog_size,
  $repl_backlog_ttl              = $::redis::params::repl_backlog_ttl,
  $repl_disable_tcp_nodelay      = $::redis::params::repl_disable_tcp_nodelay,
  $repl_ping_slave_period        = $::redis::params::repl_ping_slave_period,
  $repl_timeout                  = $::redis::params::repl_timeout,
  $requirepass                   = $::redis::params::requirepass,
  $save_db_to_disk               = $::redis::params::save_db_to_disk,
  $save_db_to_disk_interval      = $::redis::params::save_db_to_disk_interval,
  $service_enable                = $::redis::params::service_enable,
  $service_ensure                = $::redis::params::service_ensure,
  $service_group                 = $::redis::params::service_group,
  $service_hasrestart            = $::redis::params::service_hasrestart,
  $service_hasstatus             = $::redis::params::service_hasstatus,
  $service_manage                = $::redis::params::service_manage,
  $service_name                  = $::redis::params::service_name,
  $service_provider              = $::redis::params::service_provider,
  $service_user                  = $::redis::params::service_user,
  $set_max_intset_entries        = $::redis::params::set_max_intset_entries,
  $slave_priority                = $::redis::params::slave_priority,
  $slave_read_only               = $::redis::params::slave_read_only,
  $slave_serve_stale_data        = $::redis::params::slave_serve_stale_data,
  $slaveof                       = $::redis::params::slaveof,
  $slowlog_log_slower_than       = $::redis::params::slowlog_log_slower_than,
  $slowlog_max_len               = $::redis::params::slowlog_max_len,
  $stop_writes_on_bgsave_error   = $::redis::params::stop_writes_on_bgsave_error,
  $syslog_enabled                = $::redis::params::syslog_enabled,
  $syslog_facility               = $::redis::params::syslog_facility,
  $tcp_backlog                   = $::redis::params::tcp_backlog,
  $tcp_keepalive                 = $::redis::params::tcp_keepalive,
  $timeout                       = $::redis::params::timeout,
  $unixsocket                    = $::redis::params::unixsocket,
  $unixsocketperm                = $::redis::params::unixsocketperm,
  $ulimit                        = $::redis::params::ulimit,
  $workdir                       = $::redis::params::workdir,
  $workdir_mode                  = $::redis::params::workdir_mode,
  $zset_max_ziplist_entries      = $::redis::params::zset_max_ziplist_entries,
  $zset_max_ziplist_value        = $::redis::params::zset_max_ziplist_value,
  $cluster_enabled               = $::redis::params::cluster_enabled,
  $cluster_config_file           = $::redis::params::cluster_config_file,
  $cluster_node_timeout          = $::redis::params::cluster_node_timeout,
) inherits redis::params {

  contain ::redis::preinstall
  contain ::redis::install
  contain ::redis::config
  contain ::redis::service

  Class['redis::preinstall']
  -> Class['redis::install']
  -> Class['redis::config']

  if $::redis::notify_service {
    Class['redis::config']
    ~> Class['redis::service']
  }

  if $::puppetversion and versioncmp($::puppetversion, '4.0.0') < 0 {
    warning("Puppet 3 is EOL as of 01/01/2017, The 3.X.X releases of the module are the last that will support Puppet 3\nFor more information, see https://github.com/arioch/puppet-redis#puppet-3-support")
  }

  exec { 'systemd-reload-redis':
    command     => 'systemctl daemon-reload',
    refreshonly => true,
    path        => '/bin:/usr/bin:/usr/local/bin',
  }

}
