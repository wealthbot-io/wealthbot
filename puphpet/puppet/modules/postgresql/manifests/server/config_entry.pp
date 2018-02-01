# Manage a postgresql.conf entry. See README.md for more details.
define postgresql::server::config_entry (
  $ensure = 'present',
  $value  = undef,
  $path   = false
) {
  $postgresql_conf_path = $postgresql::server::postgresql_conf_path

  $target = $path ? {
    false   => $postgresql_conf_path,
    default => $path,
  }

  # Those are the variables that are marked as "(change requires restart)"
  # on postgresql.conf.  Items are ordered as on postgresql.conf.
  #
  # XXX: This resource supports setting other variables without knowing
  # their names.  Do not add them here.
  $requires_restart_until = {
    'data_directory'                      => undef,
    'hba_file'                            => undef,
    'ident_file'                          => undef,
    'external_pid_file'                   => undef,
    'listen_addresses'                    => undef,
    'port'                                => undef,
    'max_connections'                     => undef,
    'superuser_reserved_connections'      => undef,
    'unix_socket_directory'               => '9.3',   # Turned into "unix_socket_directories"
    'unix_socket_directories'             => undef,
    'unix_socket_group'                   => undef,
    'unix_socket_permissions'             => undef,
    'bonjour'                             => undef,
    'bonjour_name'                        => undef,
    'ssl'                                 => '10',
    'ssl_ciphers'                         => '10',
    'ssl_prefer_server_ciphers'           => '10',    # New on 9.4
    'ssl_ecdh_curve'                      => '10',    # New on 9.4
    'ssl_cert_file'                       => '10',    # New on 9.2
    'ssl_key_file'                        => '10',    # New on 9.2
    'ssl_ca_file'                         => '10',    # New on 9.2
    'ssl_crl_file'                        => '10',    # New on 9.2
    'shared_buffers'                      => undef,
    'huge_pages'                          => undef,   # New on 9.4
    'max_prepared_transactions'           => undef,
    'max_files_per_process'               => undef,
    'shared_preload_libraries'            => undef,
    'max_worker_processes'                => undef,   # New on 9.4
    'old_snapshot_threshold'              => undef,   # New on 9.6
    'wal_level'                           => undef,
    'wal_log_hints'                       => undef,   # New on 9.4
    'wal_buffers'                         => undef,
    'archive_mode'                        => undef,
    'max_wal_senders'                     => undef,
    'max_replication_slots'               => undef,   # New on 9.4
    'track_commit_timestamp'              => undef,   # New on 9.5
    'hot_standby'                         => undef,
    'logging_collector'                   => undef,
    'cluster_name'                        => undef,   # New on 9.5
    'silent_mode'                         => '9.2',   # Removed
    'track_activity_query_size'           => undef,
    'autovacuum_max_workers'              => undef,
    'autovacuum_freeze_max_age'           => undef,
    'autovacuum_multixact_freeze_max_age' => undef,   # New on 9.5
    'max_locks_per_transaction'           => undef,
    'max_pred_locks_per_transaction'      => undef,
  }

  Exec {
    logoutput => 'on_failure',
  }

  if ! ($name in $requires_restart_until and (
    ! $requires_restart_until[$name] or
    versioncmp($postgresql::server::_version, $requires_restart_until[$name]) < 0
  )) {
    Postgresql_conf {
      notify => Class['postgresql::server::reload'],
    }
  } elsif $postgresql::server::service_restart_on_change {
    Postgresql_conf {
      notify => Class['postgresql::server::service'],
    }
  } else {
    Postgresql_conf {
      before => Class['postgresql::server::service'],
    }
  }

  # We have to handle ports and the data directory in a weird and
  # special way.  On early Debian and Ubuntu and RHEL we have to ensure
  # we stop the service completely. On RHEL 7 we either have to create
  # a systemd override for the port or update the sysconfig file, but this
  # is managed for us in postgresql::server::config.
  if $::operatingsystem == 'Debian' or $::operatingsystem == 'Ubuntu' {
    if $name == 'port' and ( $::operatingsystemrelease =~ /^6/ or $::operatingsystemrelease =~ /^10\.04/ ) {
        exec { "postgresql_stop_${name}":
          command => "service ${::postgresql::server::service_name} stop",
          onlyif  => "service ${::postgresql::server::service_name} status",
          unless  => "grep 'port = ${value}' ${::postgresql::server::postgresql_conf_path}",
          path    => '/usr/sbin:/sbin:/bin:/usr/bin:/usr/local/bin',
          before  => Postgresql_conf[$name],
        }
    }
    elsif $name == 'data_directory' {
      exec { "postgresql_stop_${name}":
        command => "service ${::postgresql::server::service_name} stop",
        onlyif  => "service ${::postgresql::server::service_name} status",
        unless  => "grep \"data_directory = '${value}'\" ${::postgresql::server::postgresql_conf_path}",
        path    => '/usr/sbin:/sbin:/bin:/usr/bin:/usr/local/bin',
        before  => Postgresql_conf[$name],
      }
    }
  }
  if $::osfamily == 'RedHat' {
    if ! ($::operatingsystemrelease =~ /^7/ or $::operatingsystem == 'Fedora') {
      if $name == 'port' {
        # We need to force postgresql to stop before updating the port
        # because puppet becomes confused and is unable to manage the
        # service appropriately.
        exec { "postgresql_stop_${name}":
          command => "service ${::postgresql::server::service_name} stop",
          onlyif  => "service ${::postgresql::server::service_name} status",
          unless  => "grep 'PGPORT=${value}' /etc/sysconfig/pgsql/postgresql",
          path    => '/sbin:/bin:/usr/bin:/usr/local/bin',
          require => File['/etc/sysconfig/pgsql/postgresql'],
        }
        -> augeas { 'override PGPORT in /etc/sysconfig/pgsql/postgresql':
          lens    => 'Shellvars.lns',
          incl    => '/etc/sysconfig/pgsql/*',
          context => '/files/etc/sysconfig/pgsql/postgresql',
          changes => "set PGPORT ${value}",
          require => File['/etc/sysconfig/pgsql/postgresql'],
          notify  => Class['postgresql::server::service'],
          before  => Class['postgresql::server::reload'],
        }
      } elsif $name == 'data_directory' {
        # We need to force postgresql to stop before updating the data directory
        # otherwise init script breaks
        exec { "postgresql_${name}":
          command => "service ${::postgresql::server::service_name} stop",
          onlyif  => "service ${::postgresql::server::service_name} status",
          unless  => "grep 'PGDATA=${value}' /etc/sysconfig/pgsql/postgresql",
          path    => '/sbin:/bin:/usr/bin:/usr/local/bin',
          require => File['/etc/sysconfig/pgsql/postgresql'],
        }
        -> augeas { 'override PGDATA in /etc/sysconfig/pgsql/postgresql':
          lens    => 'Shellvars.lns',
          incl    => '/etc/sysconfig/pgsql/*',
          context => '/files/etc/sysconfig/pgsql/postgresql',
          changes => "set PGDATA ${value}",
          require => File['/etc/sysconfig/pgsql/postgresql'],
          notify  => Class['postgresql::server::service'],
          before  => Class['postgresql::server::reload'],
        }
      }
    }
  }

  case $ensure {
    /present|absent/: {
      postgresql_conf { $name:
        ensure  => $ensure,
        target  => $target,
        value   => $value,
        require => Class['postgresql::server::initdb'],
      }
    }

    default: {
      fail("Unknown value for ensure '${ensure}'.")
    }
  }
}
