# = Type: postgresql::server::schema
#
# Create a new schema. See README.md for more details.
#
# == Requires:
#
# The database must exist and the PostgreSQL user should have enough privileges
#
# == Sample Usage:
#
# postgresql::server::schema {'private':
#     db => 'template1',
# }
#
define postgresql::server::schema(
  $db               = $postgresql::server::default_database,
  $owner            = undef,
  $schema           = $title,
  $connect_settings = $postgresql::server::default_connect_settings,
) {
  $user           = $postgresql::server::user
  $group          = $postgresql::server::group
  $psql_path      = $postgresql::server::psql_path
  $version        = $postgresql::server::_version
  $module_workdir = $postgresql::server::module_workdir

  Postgresql::Server::Db <| dbname == $db |> -> Postgresql::Server::Schema[$name]

  # If the connection settings do not contain a port, then use the local server port
  if $connect_settings != undef and has_key( $connect_settings, 'PGPORT') {
    $port = undef
  } else {
    $port = $postgresql::server::port
  }

  Postgresql_psql {
    db         => $db,
    psql_user  => $user,
    psql_group => $group,
    psql_path  => $psql_path,
    port       => $port,
    cwd        => $module_workdir,
    connect_settings => $connect_settings,
  }

  postgresql_psql { "${db}: CREATE SCHEMA \"${schema}\"":
    command => "CREATE SCHEMA \"${schema}\"",
    unless  => "SELECT 1 FROM pg_namespace WHERE nspname = '${schema}'",
    require => Class['postgresql::server'],
  }

  if $owner {
    postgresql_psql { "${db}: ALTER SCHEMA \"${schema}\" OWNER TO \"${owner}\"":
      command => "ALTER SCHEMA \"${schema}\" OWNER TO ${owner}",
      unless  => "SELECT 1 FROM pg_namespace JOIN pg_roles rol ON nspowner = rol.oid WHERE nspname = '${schema}' AND rolname = '${owner}'",
      require => Postgresql_psql["${db}: CREATE SCHEMA \"${schema}\""],
    }

    if defined(Postgresql::Server::Role[$owner]) {
      Postgresql::Server::Role[$owner]->Postgresql_psql["${db}: ALTER SCHEMA \"${schema}\" OWNER TO \"${owner}\""]
    }
  }
}
