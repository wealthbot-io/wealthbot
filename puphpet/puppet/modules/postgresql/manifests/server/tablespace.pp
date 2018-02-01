# This module creates tablespace. See README.md for more details.
define postgresql::server::tablespace(
  $location,
  $owner   = undef,
  $spcname = $title,
  $connect_settings = $postgresql::server::default_connect_settings,
) {
  $user           = $postgresql::server::user
  $group          = $postgresql::server::group
  $psql_path      = $postgresql::server::psql_path
  $module_workdir = $postgresql::server::module_workdir

  # If the connection settings do not contain a port, then use the local server port
  if $connect_settings != undef and has_key( $connect_settings, 'PGPORT') {
    $port = undef
  } else {
    $port = $postgresql::server::port
  }

  Postgresql_psql {
    psql_user        => $user,
    psql_group       => $group,
    psql_path        => $psql_path,
    port             => $port,
    connect_settings => $connect_settings,
    cwd              => $module_workdir,
  }

  file { $location:
    ensure  => directory,
    owner   => $user,
    group   => $group,
    mode    => '0700',
    seluser => 'system_u',
    selrole => 'object_r',
    seltype => 'postgresql_db_t',
    require => Class['postgresql::server'],
  }

  postgresql_psql { "CREATE TABLESPACE \"${spcname}\"":
    command => "CREATE TABLESPACE \"${spcname}\" LOCATION '${location}'",
    unless  => "SELECT 1 FROM pg_tablespace WHERE spcname = '${spcname}'",
    require => [Class['postgresql::server'], File[$location]],
  }

  if $owner {
    postgresql_psql { "ALTER TABLESPACE \"${spcname}\" OWNER TO \"${owner}\"":
      unless  => "SELECT 1 FROM pg_tablespace JOIN pg_roles rol ON spcowner = rol.oid WHERE spcname = '${spcname}' AND rolname = '${owner}'",
      require => Postgresql_psql["CREATE TABLESPACE \"${spcname}\""],
    }

    if defined(Postgresql::Server::Role[$owner]) {
      Postgresql::Server::Role[$owner]->Postgresql_psql["ALTER TABLESPACE \"${spcname}\" OWNER TO \"${owner}\""]
    }
  }
}
