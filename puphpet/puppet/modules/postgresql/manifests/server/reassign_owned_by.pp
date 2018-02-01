# Define for reassigning the ownership of objects within a database. See README.md for more details.
# This enables us to force the a particular ownership for objects within a database
define postgresql::server::reassign_owned_by (
  String $old_role,
  String $new_role,
  String $db,
  String $psql_user                 = $postgresql::server::user,
  Integer $port                     = $postgresql::server::port,
  Hash $connect_settings            = $postgresql::server::default_connect_settings,
) {

  $sql_command = "REASSIGN OWNED BY \"${old_role}\" TO \"${new_role}\""

  $group     = $postgresql::server::group
  $psql_path = $postgresql::server::psql_path

  #
  # Port, order of precedence: $port parameter, $connect_settings[PGPORT], $postgresql::server::port
  #
  if $port != undef {
    $port_override = $port
  } elsif $connect_settings != undef and has_key( $connect_settings, 'PGPORT') {
    $port_override = undef
  } else {
    $port_override = $postgresql::server::port
  }

  $onlyif = "SELECT tablename FROM pg_catalog.pg_tables WHERE
               schemaname NOT IN ('pg_catalog', 'information_schema') AND
               tableowner = '${old_role}'
             UNION ALL SELECT proname FROM pg_catalog.pg_proc WHERE
               pg_get_userbyid(proowner) = '${old_role}'
             UNION ALL SELECT viewname FROM pg_catalog.pg_views WHERE
               pg_views.schemaname NOT IN ('pg_catalog', 'information_schema') AND
               viewowner = '${old_role}'
             UNION ALL SELECT relname FROM pg_catalog.pg_class WHERE
               relkind='S' AND pg_get_userbyid(relowner) = '${old_role}'"

  postgresql_psql { "reassign_owned_by:${db}:${sql_command}":
    command          => $sql_command,
    db               => $db,
    port             => $port_override,
    connect_settings => $connect_settings,
    psql_user        => $psql_user,
    psql_group       => $group,
    psql_path        => $psql_path,
    onlyif           => $onlyif,
    require          => Class['postgresql::server']
  }

  if($old_role != undef and defined(Postgresql::Server::Role[$old_role])) {
    Postgresql::Server::Role[$old_role]->Postgresql_psql["reassign_owned_by:${db}:${sql_command}"]
  }
  if($new_role != undef and defined(Postgresql::Server::Role[$new_role])) {
    Postgresql::Server::Role[$new_role]->Postgresql_psql["reassign_owned_by:${db}:${sql_command}"]
  }

  if($db != undef and defined(Postgresql::Server::Database[$db])) {
    Postgresql::Server::Database[$db]->Postgresql_psql["reassign_owned_by:${db}:${sql_command}"]
  }
}
