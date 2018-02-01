# postgresql

#### Table of Contents

1. [Module Description - What does the module do?](#module-description)
2. [Setup - The basics of getting started with postgresql module](#setup)
    * [What postgresql affects](#what-postgresql-affects)
    * [Getting started with postgresql](#getting-started-with-postgresql)
3. [Usage - Configuration options and additional functionality](#usage)
    * [Configure a server](#configure-a-server)
    * [Create a database](#create-a-database)
    * [Manage users, roles, and permissions](#manage-users-roles-and-permissions)
    * [Manage ownership of DB objects](#manage-ownership-of-db-objects)
    * [Override defaults](#override-defaults)
    * [Create an access rule for pg_hba.conf](#create-an-access-rule-for-pg_hbaconf)
    * [Create user name maps for pg_ident.conf](#create-user-name-maps-for-pg_identconf)
    * [Validate connectivity](#validate-connectivity)
4. [Reference - An under-the-hood peek at what the module is doing and how](#reference)
    * [Classes](#classes)
    * [Defined Types](#defined-types)
    * [Types](#types)
    * [Functions](#functions)
5. [Limitations - OS compatibility, etc.](#limitations)
6. [Development - Guide for contributing to the module](#development)
    * [Contributors - List of module contributors](#contributors)
7. [Tests](#tests)
8. [Contributors - List of module contributors](#contributors)

## Module description

The postgresql module allows you to manage PostgreSQL databases with Puppet.

PostgreSQL is a high-performance, free, open-source relational database server. The postgresql module allows you to manage packages, services, databases, users, and common security settings in PostgreSQL.

## Setup

### What postgresql affects

* Package, service, and configuration files for PostgreSQL
* Listened-to ports
* IP and mask (optional)

### Getting started with postgresql

To configure a basic default PostgreSQL server, declare the `postgresql::server` class.

```puppet
class { 'postgresql::server':
}
```

## Usage

### Configure a server

For default settings, declare the `postgresql::server` class as above. To customize PostgreSQL server settings, specify the [parameters](#postgresqlserver) you want to change:

```puppet
class { 'postgresql::server':
  ip_mask_deny_postgres_user => '0.0.0.0/32',
  ip_mask_allow_all_users    => '0.0.0.0/0',
  ipv4acls                   => ['hostssl all johndoe 192.168.0.0/24 cert'],
  postgres_password          => 'TPSrep0rt!',
}
```

After configuration, test your settings from the command line:

```shell
psql -h localhost -U postgres
psql -h my.postgres.server -U
```

If you get an error message from these commands, your permission settings restrict access from the location you're trying to connect from. Depending on whether you want to allow connections from that location, you might need to adjust your permissions.

For more details about server configuration parameters, consult the [PostgreSQL Runtime Configuration documentation](http://www.postgresql.org/docs/current/static/runtime-config.html).

### Create a database

You can set up a variety of PostgreSQL databases with the `postgresql::server::db` defined type. For instance, to set up a database for PuppetDB:

```puppet
class { 'postgresql::server':
}

postgresql::server::db { 'mydatabasename':
  user     => 'mydatabaseuser',
  password => postgresql_password('mydatabaseuser', 'mypassword'),
}
```

### Manage users, roles, and permissions

To manage users, roles, and permissions:

```puppet
class { 'postgresql::server':
}

postgresql::server::role { 'marmot':
  password_hash => postgresql_password('marmot', 'mypasswd'),
}

postgresql::server::database_grant { 'test1':
  privilege => 'ALL',
  db        => 'test1',
  role      => 'marmot',
}

postgresql::server::table_grant { 'my_table of test2':
  privilege => 'ALL',
  table     => 'my_table',
  db        => 'test2',
  role      => 'marmot',
}
```

This example grants **all** privileges on the test1 database and on the `my_table` table of the test2 database to the specified user or group. After the values are added into the PuppetDB config file, this database would be ready for use.

### Manage ownership of DB objects

To change the ownership of all objects within a database using REASSIGN OWNED:

```puppet
postgresql::server::reassign_owned_by { 'new owner is meerkat':
  db        => 'test_db',
  old_owner => 'marmot',
  new_owner => 'meerkat',
}
```

This would run the PostgreSQL statement 'REASSIGN OWNED' to update to ownership of all tables, sequences, functions and views currently owned by the role 'marmot' to be owned by the role 'meerkat' instead.

This applies to objects within the nominated database, 'test_db' only.

For Postgresql >= 9.3, the ownership of the database is also updated.

### Override defaults

The `postgresql::globals` class allows you to configure the main settings for this module globally, so that other classes and defined resources can use them. By itself, it does nothing.

For example, to overwrite the default `locale` and `encoding` for all classes, use the following:

```puppet
class { 'postgresql::globals':
  encoding => 'UTF-8',
  locale   => 'en_US.UTF-8',
}

class { 'postgresql::server':
}
```

To use a specific version of the PostgreSQL package:

```puppet
class { 'postgresql::globals':
  manage_package_repo => true,
  version             => '9.2',
}

class { 'postgresql::server':
}
```

### Manage remote users, roles, and permissions

Remote SQL objects are managed using the same Puppet resources as local SQL objects, along with a [`connect_settings`](#connect_settings) hash. This provides control over how Puppet connects to the remote Postgres instances and which version is used for generating SQL commands.

The `connect_settings` hash can contain environment variables to control Postgres client connections, such as 'PGHOST', 'PGPORT', 'PGPASSWORD', and 'PGSSLKEY'. See the [PostgreSQL Environment Variables](http://www.postgresql.org/docs/9.4/static/libpq-envars.html)  documentation for a complete list of variables.

Additionally, you can specify the target database version with the special value of 'DBVERSION'. If the `connect_settings` hash is omitted or empty, then Puppet connects to the local PostgreSQL instance.

You can provide a `connect_settings` hash for each of the Puppet resources, or you can set a default `connect_settings` hash in `postgresql::globals`. Configuring `connect_settings` per resource allows SQL objects to be created on multiple databases by multiple users.

```puppet
$connection_settings_super2 = {
  'PGUSER'     => 'super2',
  'PGPASSWORD' => 'foobar2',
  'PGHOST'     => '127.0.0.1',
  'PGPORT'     => '5432',
  'PGDATABASE' => 'postgres',
}

include postgresql::server

# Connect with no special settings, i.e domain sockets, user postgres
postgresql::server::role { 'super2':
  password_hash    => 'foobar2',
  superuser        => true,

  connect_settings => {},
}

# Now using this new user connect via TCP
postgresql::server::database { 'db1':
  connect_settings => $connection_settings_super2,
  require          => Postgresql::Server::Role['super2'],
}
```

### Create an access rule for pg_hba.conf

To create an access rule for `pg_hba.conf`:

```puppet
postgresql::server::pg_hba_rule { 'allow application network to access app database':
  description => 'Open up PostgreSQL for access from 200.1.2.0/24',
  type        => 'host',
  database    => 'app',
  user        => 'app',
  address     => '200.1.2.0/24',
  auth_method => 'md5',
}
```

This would create a ruleset in `pg_hba.conf` similar to:

```
# Rule Name: allow application network to access app database
# Description: Open up PostgreSQL for access from 200.1.2.0/24
# Order: 150
host  app  app  200.1.2.0/24  md5
```

By default, `pg_hba_rule` requires that you include `postgresql::server`. However, you can override that behavior by setting target and postgresql_version when declaring your rule.  That might look like the following:

```puppet
postgresql::server::pg_hba_rule { 'allow application network to access app database':
  description        => 'Open up postgresql for access from 200.1.2.0/24',
  type               => 'host',
  database           => 'app',
  user               => 'app',
  address            => '200.1.2.0/24',
  auth_method        => 'md5',
  target             => '/path/to/pg_hba.conf',
  postgresql_version => '9.4',
}
```

### Create user name maps for pg_ident.conf

To create a user name map for the pg_ident.conf:

```puppet
postgresql::server::pg_ident_rule { 'Map the SSL certificate of the backup server as a replication user':
  map_name          => 'sslrepli',
  system_username   => 'repli1.example.com',
  database_username => 'replication',
}
```

This would create a user name map in `pg_ident.conf` similar to:

```
#Rule Name: Map the SSL certificate of the backup server as a replication user
#Description: none
#Order: 150
sslrepli  repli1.example.com  replication
```

### Create recovery configuration

To create the recovery configuration file (`recovery.conf`):

```puppet
postgresql::server::recovery { 'Create a recovery.conf file with the following defined parameters':
  restore_command           => 'cp /mnt/server/archivedir/%f %p',
  archive_cleanup_command   => undef,
  recovery_end_command      => undef,
  recovery_target_name      => 'daily backup 2015-01-26',
  recovery_target_time      => '2015-02-08 22:39:00 EST',
  recovery_target_xid       => undef,
  recovery_target_inclusive => true,
  recovery_target           => 'immediate',
  recovery_target_timeline  => 'latest',
  pause_at_recovery_target  => true,
  standby_mode              => 'on',
  primary_conninfo          => 'host=localhost port=5432',
  primary_slot_name         => undef,
  trigger_file              => undef,
  recovery_min_apply_delay  => 0,
}
```

The above creates this `recovery.conf` config file:

```
restore_command = 'cp /mnt/server/archivedir/%f %p'
recovery_target_name = 'daily backup 2015-01-26'
recovery_target_time = '2015-02-08 22:39:00 EST'
recovery_target_inclusive = true
recovery_target = 'immediate'
recovery_target_timeline = 'latest'
pause_at_recovery_target = true
standby_mode = 'on'
primary_conninfo = 'host=localhost port=5432'
recovery_min_apply_delay = 0
```

Only the specified parameters are recognized in the template. The `recovery.conf` is only be created if at least one parameter is set **and** [manage_recovery_conf](#manage_recovery_conf) is set to true.

### Validate connectivity

To validate client connections to a remote PostgreSQL database before starting dependent tasks, use the `postgresql_conn_validator` resource. You can use this on any node where the PostgreSQL client software is installed. It is often chained to other tasks such as starting an application server or performing a database migration.

Example usage:

```puppet
postgresql_conn_validator { 'validate my postgres connection':
  host              => 'my.postgres.host',
  db_username       => 'mydbuser',
  db_password       => 'mydbpassword',
  db_name           => 'mydbname',
}->
exec { 'rake db:migrate':
  cwd => '/opt/myrubyapp',
}
```

## Reference

The postgresql module comes with many options for configuring the server. While you are unlikely to use all of the settings below, they provide a decent amount of control over your security settings.

**Classes:**

* [postgresql::client](#postgresqlclient)
* [postgresql::globals](#postgresqlglobals)
* [postgresql::lib::devel](#postgresqllibdevel)
* [postgresql::lib::java](#postgresqllibjava)
* [postgresql::lib::perl](#postgresqllibperl)
* [postgresql::lib::python](#postgresqllibpython)
* [postgresql::server](#postgresqlserver)
* [postgresql::server::plperl](#postgresqlserverplperl)
* [postgresql::server::contrib](#postgresqlservercontrib)
* [postgresql::server::postgis](#postgresqlserverpostgis)

**Defined Types:**

* [postgresql::server::config_entry](#postgresqlserverconfig_entry)
* [postgresql::server::database](#postgresqlserverdatabase)
* [postgresql::server::database_grant](#postgresqlserverdatabase_grant)
* [postgresql::server::db](#postgresqlserverdb)
* [postgresql::server::extension](#postgresqlserverextension)
* [postgresql::server::grant](#postgresqlservergrant)
* [postgresql::server::grant_role](#postgresqlservergrant_role)
* [postgresql::server::pg_hba_rule](#postgresqlserverpg_hba_rule)
* [postgresql::server::pg_ident_rule](#postgresqlserverpg_ident_rule)
* [postgresql::server::reassign_owned_by](#postgresqlserverreassign_owned_by)
* [postgresql::server::recovery](#postgresqlserverrecovery)
* [postgresql::server::role](#postgresqlserverrole)
* [postgresql::server::schema](#postgresqlserverschema)
* [postgresql::server::table_grant](#postgresqlservertable_grant)
* [postgresql::server::tablespace](#postgresqlservertablespace)

**Types:**

* [postgresql_psql](#custom-resource-postgresql_psql)
* [postgresql_replication_slot](#custom-resource-postgresql_replication_slot)
* [postgresql_conf](#custom-resource-postgresql_conf)
* [postgresql_conn_validator](#custom-resource-postgresql_conn_validator)

**Functions:**

* [postgresql_password](#function-postgresql_password)
* [postgresql_acls_to_resources_hash](#function-postgresql_acls_to_resources_hashacl_array-id-order_offset)

### Classes

#### postgresql::client

Installs PostgreSQL client software. Set the following parameters if you have a custom version you would like to install.

>**Note:** Make sure to add any necessary yum or apt repositories if specifying a custom version.

##### `package_ensure`

Whether the PostgreSQL client package resource should be present.

Valid values: 'present', 'absent'.

Default value: 'present'.

##### `package_name`

Sets the name of the PostgreSQL client package.

Default value: 'file'.

#### postgresql::lib::docs

Installs PostgreSQL bindings for Postgres-Docs. Set the following parameters if you have a custom version you would like to install.

**Note:** Make sure to add any necessary yum or apt repositories if specifying a custom version.

##### `package_name`

Specifies the name of the PostgreSQL docs package.

##### `package_ensure`

Whether the PostgreSQL docs package resource should be present.

Valid values: 'present', 'absent'.

Default value: 'present'.

#### postgresql::globals

**Note:** Most server-specific defaults should be overridden in the `postgresql::server` class. This class should be used only if you are using a non-standard OS, or if you are changing elements that can only be changed here, such as `version` or `manage_package_repo`.

##### `bindir`

Overrides the default PostgreSQL binaries directory for the target platform.

Default value: OS dependent.

##### `client_package_name`

Overrides the default PostgreSQL client package name.

Default value: OS dependent.

##### `confdir`

Overrides the default PostgreSQL configuration directory for the target platform.

Default value: OS dependent.

##### `contrib_package_name`

Overrides the default PostgreSQL contrib package name.

Default value: OS dependent.

##### `createdb_path`

**Deprecated.** Path to the `createdb` command.

Default value: '${bindir}/createdb'.

##### `datadir`

Overrides the default PostgreSQL data directory for the target platform.

Default value: OS dependent.

**Note:** Changing the datadir after installation causes the server to come to a full stop before making the change. For Red Hat systems, the data directory must be labeled appropriately for SELinux. On Ubuntu, you must explicitly set `needs_initdb = true` to allow Puppet to initialize the database in the new datadir (`needs_initdb` defaults to true on other systems).

**Warning:** If datadir is changed from the default, Puppet does not manage purging of the original data directory, which causes it to fail if the data directory is changed back to the original.

##### `data_checksums`

Optional.

Data type: Boolean.

Use checksums on data pages to help detect corruption by the I/O system that would otherwise be silent.

Valid values: `true` or `false`.

Default: initdb's default (`false`).

**Warning:** This option is used during initialization by initdb, and cannot be changed later. If set, checksums are calculated for all objects, in all databases.

##### `default_database`

Specifies the name of the default database to connect with.

Default value: 'postgres' (for most systems).

##### `devel_package_name`

Overrides the default PostgreSQL devel package name.

Default value: OS dependent.

##### `docs_package_name`

Optional.

Overrides the default PostgreSQL docs package name.

Default value: OS dependent.

##### `encoding`

Sets the default encoding for all databases created with this module. On certain operating systems, this is also used during the `template1` initialization, so it becomes a default outside of the module as well.

Default value: Dependent on the operating system's default encoding.

##### `group`

Overrides the default postgres user group to be used for related files in the file system.

Default value: 'postgres'.

##### `initdb_path`

Path to the `initdb` command.

##### `java_package_name`

Overrides the default PostgreSQL java package name.

Default value: OS dependent.

##### `locale`

Sets the default database locale for all databases created with this module. On certain operating systems, this is also used during the `template1` initialization, so it becomes a default outside of the module as well.

Default value: `undef`, which is effectively 'C'.

**On Debian, you'll need to ensure that the 'locales-all' package is installed for full functionality of PostgreSQL.**

##### `timezone`

Sets the default timezone of the postgresql server. The postgresql built-in default is taking the systems timezone information.

##### `logdir`

Overrides the default PostgreSQL log directory.

Default value: initdb's default path.

##### `manage_package_repo`

Sets up official PostgreSQL repositories on your host if set to `true`.

Default value: `false`.

##### `module_workdir`

Specifies working directory under which the psql command should be executed. May need to specify if '/tmp' is on volume mounted with noexec option.

Default value: '/tmp'.

##### `needs_initdb`

Explicitly calls the initdb operation after the server package is installed and before the PostgreSQL service is started.

Default value: OS dependent.

##### `perl_package_name`

Overrides the default PostgreSQL Perl package name.

Default value: OS dependent.

##### `pg_hba_conf_defaults`

Disables the defaults supplied with the module for `pg_hba.conf` if set to `false`. This is useful if you want to override the defaults. Be sure that your changes align with the rest of the module, as some access is required to perform some operations, such as basic `psql` operations.

Default value: The globals value set in `postgresql::globals::manage_pg_hba_conf` which defaults to `true`.

##### `pg_hba_conf_path`

Specifies the path to your `pg_hba.conf` file.

Default value: '${confdir}/pg_hba.conf'.

##### `pg_ident_conf_path`

Specifies the path to your `pg_ident.conf` file.

Default value: '${confdir}/pg_ident.conf'.

##### `plperl_package_name`

Overrides the default PostgreSQL PL/Perl package name.

Default value: OS dependent.

##### `plpython_package_name`

Overrides the default PostgreSQL PL/Python package name.

Default value: OS dependent.

##### `postgis_version`

Defines the version of PostGIS to install, if you install PostGIS.

Default value: The lowest available with the version of PostgreSQL to be installed.

##### `postgresql_conf_path`

Sets the path to your `postgresql.conf` file.

Default value: '${confdir}/postgresql.conf'.

##### `psql_path`

Sets the path to the `psql` command.

##### `python_package_name`

Overrides the default PostgreSQL Python package name.

Default value: OS dependent.

##### `recovery_conf_path`

Path to your `recovery.conf` file.

##### `repo_proxy`

Sets the proxy option for the official PostgreSQL yum-repositories only. This is useful if your server is behind a corporate firewall and needs to use proxy servers for outside connectivity.

Debian is currently not supported.

##### `repo_baseurl`

Sets the baseurl for the PostgreSQL repository. Useful if you host your own mirror of the repository.

Default value: The official PostgreSQL repository.

##### `server_package_name`

Overrides the default PostgreSQL server package name.

Default value: OS dependent.

##### `service_name`

Overrides the default PostgreSQL service name.

Default value: OS dependent.

##### `service_provider`

Overrides the default PostgreSQL service provider.

Default value: OS dependent.

##### `service_status`

Overrides the default status check command for your PostgreSQL service.

Default value: OS dependent.

##### `user`

Overrides the default PostgreSQL super user and owner of PostgreSQL related files in the file system.

Default value: 'postgres'.

##### `version`

The version of PostgreSQL to install and manage.

Default value: OS system default.

##### `xlogdir`

Overrides the default PostgreSQL xlog directory.

Default value: initdb's default path.

#### postgresql::lib::devel

Installs the packages containing the development libraries for PostgreSQL and symlinks `pg_config` into `/usr/bin` (if not in `/usr/bin` or `/usr/local/bin`).

##### `link_pg_config`

If the bin directory used by the PostgreSQL page is not  `/usr/bin` or `/usr/local/bin`, symlinks `pg_config` from the package's bin dir into `usr/bin` (not applicable to Debian systems). Set to `false` to disable this behavior. 

Valid values: `true`, `false`.

Default value: `true`.

##### `package_ensure`

Overrides the 'ensure' parameter during package installation.

Default value: 'present'.

##### `package_name`

Overrides the default package name for the distribution you are installing to.

Default value: 'postgresql-devel' or 'postgresql<version>-devel' depending on your distro.

#### postgresql::lib::java

Installs PostgreSQL bindings for Java (JDBC). Set the following parameters if you have a custom version you would like to install.

**Note:** Make sure to add any necessary yum or apt repositories if specifying a custom version.

##### `package_ensure`

Specifies whether the package is present.

Valid values: 'present', 'absent'.

Default value: 'present'.

##### `package_name`

Specifies the name of the PostgreSQL java package.

#### postgresql::lib::perl

Installs the PostgreSQL Perl libraries.

##### `package_ensure`

Specifies whether the package is present.

Valid values: 'present', 'absent'.

Default value: 'present'.

##### `package_name`

Specifies the name of the PostgreSQL perl package to install.

#### postgresql::server::plpython

Installs the PL/Python procedural language for PostgreSQL.

##### `package_name`

Specifies the name of the postgresql PL/Python package.

##### `package_ensure`

Specifies whether the package is present.

Valid values: 'present', 'absent'.

Default value: 'present'.

#### postgresql::lib::python

Installs PostgreSQL Python libraries.

##### `package_ensure`

Specifies whether the package is present.

Valid values: 'present', 'absent'.

Default value: 'present'.

##### `package_name`

The name of the PostgreSQL Python package.

#### postgresql::server

##### `createdb_path`

**Deprecated.** Specifies the path to the `createdb` command.

Default value: '${bindir}/createdb'.

##### `data_checksums`

Optional.

Data type: Boolean.

Use checksums on data pages to help detect corruption by the I/O system that would otherwise be silent.

Valid values: `true` or `false`.

Default value: initdb's default (`false`).

**Warning:** This option is used during initialization by initdb, and cannot be changed later. If set, checksums are calculated for all objects, in all databases.

##### `default_database`

Specifies the name of the default database to connect with. On most systems this is 'postgres'.

##### `default_connect_settings`

Specifies a hash of environment variables used when connecting to a remote server. Becomes the default for other defined types, such as `postgresql::server::role`.

##### `encoding`

Sets the default encoding for all databases created with this module. On certain operating systems this is also used during the `template1` initialization, so it becomes a default outside of the module as well.

Default value: `undef`.

##### `group`

Overrides the default postgres user group to be used for related files in the file system.

Default value: OS dependent default.

##### `initdb_path`

Specifies the path to the `initdb` command.

Default value: '${bindir}/initdb'.

##### `ipv4acls`

Lists strings for access control for connection method, users, databases, IPv4 addresses;

see [PostgreSQL documentation](http://www.postgresql.org/docs/current/static/auth-pg-hba-conf.html) on `pg_hba.conf` for information.

##### `ipv6acls`

Lists strings for access control for connection method, users, databases, IPv6 addresses.

see [PostgreSQL documentation](http://www.postgresql.org/docs/current/static/auth-pg-hba-conf.html) on `pg_hba.conf` for information.

##### `ip_mask_allow_all_users`

Overrides PostgreSQL defaults for remote connections. By default, PostgreSQL does not allow database user accounts to connect via TCP from remote machines. If you'd like to allow this, you can override this setting.

Set to '0.0.0.0/0' to allow database users to connect from any remote machine, or '192.168.0.0/1' to allow connections from any machine on your local '192.168' subnet.

Default value: '127.0.0.1/32'.

##### `ip_mask_deny_postgres_user`

Specifies the IP mask from which remote connections should be denied for the postgres superuser.

Default value: '0.0.0.0/0', which denies any remote connection.

##### `locale`

Sets the default database locale for all databases created with this module. On certain operating systems this is used during the `template1` initialization as well, so it becomes a default outside of the module.

Default value: `undef`, which is effectively 'C'.

**On Debian, you must ensure that the 'locales-all' package is installed for full functionality of PostgreSQL.**

##### `manage_pg_hba_conf`

Whether to manage the `pg_hba.conf`.

If set to `true`, Puppet overwrites this file.

If set to `false`, Puppet does not modify the file.

Valid values: `true`, `false`.

Default value: `true`

##### `manage_pg_ident_conf`

Overwrites the pg_ident.conf file.

If set to `true`, Puppet overwrites the file.

If set to `false`, Puppet does not modify the file.

Valid values: `true`, `false`.

Default value: `true`.

##### `manage_recovery_conf`

Specifies whether or not manage the `recovery.conf`.

If set to `true`, Puppet overwrites this file.

Valid values: `true`, `false`.

Default value: `false`.

##### `needs_initdb`

Explicitly calls the `initdb` operation after server package is installed, and before the PostgreSQL service is started.

Default value: OS dependent.

##### `package_ensure`

Passes a value through to the `package` resource when creating the server instance.

Default value: `undef`.

##### `package_name`

Specifies the name of the package to use for installing the server software.

Default value: OS dependent.

##### `pg_hba_conf_defaults`

If `false`, disables the defaults supplied with the module for `pg_hba.conf`. This is useful if you disagree with the defaults and wish to override them yourself. Be sure that your changes of course align with the rest of the module, as some access is required to perform basic `psql` operations for example.

##### `pg_hba_conf_path`

Specifies the path to your `pg_hba.conf` file.

##### `pg_ident_conf_path`

Specifies the path to your `pg_ident.conf` file.

Default value: '${confdir}/pg_ident.conf'.

##### `plperl_package_name`

Sets the default package name for the PL/Perl extension.

Default value: OS dependent.

##### `plpython_package_name`

Sets the default package name for the PL/Python extension.

Default value: OS dependent.

##### `port`

Specifies the port for the PostgreSQL server to listen on. **Note:** The same port number is used for all IP addresses the server listens on. Also, for Red Hat systems and early Debian systems, changing the port causes the server to come to a full stop before being able to make the change. 

Default value: 5432. Meaning the Postgres server listens on TCP port 5432.

##### `postgres_password`

Sets the password for the postgres user to your specified value. By default, this setting uses the superuser account in the Postgres database, with a user called `postgres` and no password.

Default value: `undef`.

##### `postgresql_conf_path`

Specifies the path to your `postgresql.conf` file. 

Default value: '${confdir}/postgresql.conf'.

##### `psql_path`

Specifies the path to the `psql` command. 

Default value: OS dependent.

##### `service_manage`

Defines whether or not Puppet should manage the service. 

Default value: `true`.

##### `service_name`

Overrides the default PostgreSQL service name. 

Default value: OS dependent.

##### `service_provider`

Overrides the default PostgreSQL service provider. 

Default value: `undef`.

##### `service_reload`

Overrides the default reload command for your PostgreSQL service. 

Default value: OS dependent.

##### `service_restart_on_change`

Overrides the default behavior to restart your PostgreSQL service when a config entry has been changed that requires a service restart to become active. 

Default value: `true`.

##### `service_status`

Overrides the default status check command for your PostgreSQL service. 

Default value: OS dependent.

##### `user`

Overrides the default PostgreSQL super user and owner of PostgreSQL related files in the file system. 

Default value: 'postgres'.

#### postgresql::server::contrib

Installs the PostgreSQL contrib package.

##### `package_ensure`

Sets the ensure parameter passed on to PostgreSQL contrib package resource.

##### `package_name`

The name of the PostgreSQL contrib package.

#### postgresql::server::plperl

Installs the PL/Perl procedural language for postgresql.

##### `package_ensure`

The ensure parameter passed on to PostgreSQL PL/Perl package resource.

##### `package_name`

The name of the PostgreSQL PL/Perl package.

#### postgresql::server::postgis

Installs the PostgreSQL postgis packages.

### Defined Types

#### postgresql::server::config_entry

Modifies your `postgresql.conf` configuration file.

Each resource maps to a line inside the file, for example:

```puppet
postgresql::server::config_entry { 'check_function_bodies':
  value => 'off',
}
```

##### `ensure`

Removes an entry if set to 'absent'. 

Valid values: 'present', 'absent'.

Default value: 'present'.

##### `value`

Defines the value for the setting.

#### postgresql::server::db

Creates a local database, user, and assigns necessary permissions.

##### `comment`

Defines a comment to be stored about the database using the PostgreSQL COMMENT command.

##### `connect_settings`

Specifies a hash of environment variables used when connecting to a remote server. 

Default value: Connects to the local Postgres instance.

##### `dbname`

Sets the name of the database to be created. 

Default value: the namevar.

##### `encoding`

Overrides the character set during creation of the database. 

Default value: The default defined during installation.

##### `grant`

Specifies the permissions to grant during creation. 

Default value: 'ALL'.

##### `istemplate`

Specifies that the database is a template, if set to `true`. 

Default value: `false`.

##### `locale`

Overrides the locale during creation of the database. 

Default value: The default defined during installation.

##### `owner`

Sets a user as the owner of the database. 

Default value: '$user' variable set in `postgresql::server` or `postgresql::globals`.

##### `password`

**Required** Sets the password for the created user.

##### `tablespace`

Defines the name of the tablespace to allocate the created database to. 

Default value: PostgreSQL default.

##### `template`

Specifies the name of the template database from which to build this database. 

Defaults value: `template0`.

##### `user`

User to create and assign access to the database upon creation. Mandatory.

#### postgresql::server::database

Creates a database with no users and no permissions.

##### `dbname`

Sets the name of the database. 

Defaults value: The namevar.

##### `encoding`

Overrides the character set during creation of the database. 

Default value: The default defined during installation.

##### `istemplate`

Defines the database as a template if set to `true`.

Default value: `false`.

##### `locale`

Overrides the locale during creation of the database.

Default value: The default defined during installation.

##### `owner`

Sets name of the database owner.

Default value: The '$user' variable set in `postgresql::server` or `postgresql::globals`.

##### `tablespace`

Sets tablespace for where to create this database. 

Default value: The default defined during installation.

##### `template`

Specifies the name of the template database from which to build this database. 

Default value: 'template0'.

#### postgresql::server::database_grant

Manages grant-based access privileges for users, wrapping the `postgresql::server::database_grant` for database specific permissions. Consult the [PostgreSQL documentation for `grant`](http://www.postgresql.org/docs/current/static/sql-grant.html) for more information.

#### `connect_settings`

Specifies a hash of environment variables used when connecting to a remote server. 

Default value: Connects to the local Postgres instance.

##### `db`

Specifies the database to which you are granting access.

##### `privilege`

Specifies comma-separated list of privileges to grant. 

Valid options: 'ALL', 'CREATE', 'CONNECT', 'TEMPORARY', 'TEMP'.

##### `psql_db`

Defines the database to execute the grant against. 

**This should not ordinarily be changed from the default**

Default value: 'postgres'.

##### `psql_user`

Specifies the OS user for running `psql`. 

Default value: The default user for the module, usually 'postgres'.

##### `role`

Specifies the role or user whom you are granting access to.

#### postgresql::server::extension

Manages a PostgreSQL extension.

##### `database`

Specifies the database on which to activate the extension.

##### `ensure`

Specifies whether to activate or deactivate the extension.

Valid options: 'present' or 'absent'.

#### `extension`

Specifies the extension to activate. If left blank, uses the name of the resource.

#### `version`

Specifies the version of the extension which the database uses.
When an extension package is updated, this does not automatically change the effective version in each database.

This needs be updated using the PostgreSQL-specific SQL `ALTER EXTENSION...`

`version` may be set to `latest`, in which case the SQL `ALTER EXTENSION "extension" UPDATE` is applied to this database (only).

`version` may be set to a specific version, in which case the extension is updated using `ALTER EXTENSION "extension" UPDATE TO 'version'`

eg. If extension is set to `postgis` and version is set to `2.3.3`, this will apply the SQL `ALTER EXTENSION "postgis" UPDATE TO '2.3.3'` to this database only.

`version` may be omitted, in which case no `ALTER EXTENSION...` SQL is applied, and the version will be left unchanged.

##### `package_name`

Specifies a package to install prior to activating the extension.

##### `package_ensure`

Overrides default package deletion behavior.

By default, the package specified with `package_name` is installed when the extension is activated and removed when the extension is deactivated. To override this behavior, set the `ensure` value for the package.

#### postgresql::server::grant

Manages grant-based access privileges for roles. See [PostgreSQL documentation for `grant`](http://www.postgresql.org/docs/current/static/sql-grant.html) for more information.

##### `db`

Specifies the database to which you are granting access.

##### `object_type`

Specifies the type of object to which you are granting privileges.

Valid options: 'DATABASE', 'SCHEMA', 'SEQUENCE', 'ALL SEQUENCES IN SCHEMA', 'TABLE' or 'ALL TABLES IN SCHEMA'.

##### `object_name`

Specifies name of `object_type` to which to grant access, can be either a string or a two element array.

String: 'object_name'
Array:  ['schema_name', 'object_name']

##### `port`

Port to use when connecting.

Default value: `undef`, which generally defaults to port 5432 depending on your PostgreSQL packaging.

##### `privilege`

Specifies the privilege to grant.

Valid options: 'ALL', 'ALL PRIVILEGES' or 'object_type' dependent string.

##### `psql_db`

Specifies the database to execute the grant against.

**This should not ordinarily be changed from the default**

Default value: 'postgres'.

##### `psql_user`

Sets the OS user to run `psql`.

Default value: the default user for the module, usually 'postgres'.

##### `role`

Specifies the role or user whom you are granting access to.

#### postgresql::server::grant_role

Allows you to assign a role to a (group) role. See [PostgreSQL documentation for `Role Membership`](http://www.postgresql.org/docs/current/static/role-membership.html) for more information.

##### `group`

Specifies the group role to which you are assigning a role.

##### `role`

Specifies the role you want to assign to a group.  If left blank, uses the name of the resource.

##### `ensure`

Specifies whether to grant or revoke the membership.

Valid options: 'present' or 'absent'.

Default value: 'present'.

##### `port`

Port to use when connecting.

Default value: `undef`, which generally defaults to port 5432 depending on your PostgreSQL packaging.

##### `psql_db`

Specifies the database to execute the grant against.

**This should not ordinarily be changed from the default**

Default value: 'postgres'.

##### `psql_user`

Sets the OS user to run `psql`.

Default value: the default user for the module, usually `postgres`.

##### `connect_settings`

Specifies a hash of environment variables used when connecting to a remote server.

Default value: Connects to the local Postgres instance.

#### postgresql::server::pg_hba_rule

Allows you to create an access rule for `pg_hba.conf`. For more details see the [usage example](#create-an-access-rule-for-pghba.conf) and the [PostgreSQL documentation](http://www.postgresql.org/docs/current/static/auth-pg-hba-conf.html).

##### `address`

Sets a CIDR based address for this rule matching when the type is not 'local'.

##### `auth_method`

Provides the method that is used for authentication for the connection that this rule matches. Described further in the PostgreSQL `pg_hba.conf` documentation.

##### `auth_option`

For certain `auth_method` settings there are extra options that can be passed. Consult the PostgreSQL `pg_hba.conf` documentation for further details.

##### `database`

Sets a comma-separated list of databases that this rule matches.

##### `description`

Defines a longer description for this rule, if required. This description is placed in the comments above the rule in `pg_hba.conf`.

Default value: 'none'.

Specifies a way to uniquely identify this resource, but functionally does nothing.

##### `order`

Sets an order for placing the rule in `pg_hba.conf`.

Default value: 150.

#### `postgresql_version`

Manages `pg_hba.conf` without managing the entire PostgreSQL instance.

Default value: the version set in `postgresql::server`.

##### `target`

Provides the target for the rule, and is generally an internal only property.

**Use with caution.**

##### `type`

Sets the type of rule.

Valid options: 'local', 'host', 'hostssl' or 'hostnossl'.

##### `user`

Sets a comma-separated list of users that this rule matches.


#### postgresql::server::pg_ident_rule

Allows you to create user name maps for `pg_ident.conf`. For more details see the [usage example](#create-user-name-maps-for-pgidentconf) above and the [PostgreSQL documentation](http://www.postgresql.org/docs/current/static/auth-username-maps.html).

##### `database_username`

Specifies the user name of the database user. The `system_username` is mapped to this user name.

##### `description`

Sets a longer description for this rule if required. This description is placed in the comments above the rule in `pg_ident.conf`.

Default value: 'none'.

##### `map_name`

Sets the name of the user map that is used to refer to this mapping in `pg_hba.conf`.

##### `order`

Defines an order for placing the mapping in `pg_ident.conf`.

Default value: 150.

##### `system_username`

Specifies the operating system user name (the user name used to connect to the database).

##### `target`

Provides the target for the rule and is generally an internal only property.

**Use with caution.**

#### postgresql::server::reassign_owned_by

Runs the PostgreSQL command 'REASSIGN OWNED' on a database, to transfer the ownership of existing objects between database roles

##### `db`

Specifies the database to which the 'REASSIGN OWNED' will be applied

##### `old_role`

Specifies the role or user who is the current owner of the objects in the specified db

##### `new_role`

Specifies the role or user who will be the new owner of these objects

##### `psql_user`

Specifies the OS user for running `psql`.

Default value: The default user for the module, usually 'postgres'.

##### `port`

Port to use when connecting.

Default value: `undef`, which generally defaults to port 5432 depending on your PostgreSQL packaging.

##### `connect_settings`

Specifies a hash of environment variables used when connecting to a remote server.

Default value: Connects to the local Postgres instance.

#### postgresql::server::recovery

Allows you to create the content for `recovery.conf`. For more details see the [usage example](#create-recovery-configuration) and the [PostgreSQL documentation](http://www.postgresql.org/docs/current/static/recovery-config.html).

Every parameter value is a string set in the template except `recovery_target_inclusive`, `pause_at_recovery_target`, `standby_mode` and `recovery_min_apply_delay`.

A detailed description of all listed parameters can be found in the [PostgreSQL documentation](http://www.postgresql.org/docs/current/static/recovery-config.html).

The parameters are grouped into these three sections:

##### [Archive Recovery Parameters](http://www.postgresql.org/docs/current/static/archive-recovery-settings.html)

* `restore_command`
* `archive_cleanup_command`
* `recovery_end_command`

##### [Recovery Target Settings](http://www.postgresql.org/docs/current/static/recovery-target-settings.html)
* `recovery_target_name`
* `recovery_target_time`
* `recovery_target_xid`
* `recovery_target_inclusive`
* `recovery_target`
* `recovery_target_timeline`
* `pause_at_recovery_target`

##### [Standby Server Settings](http://www.postgresql.org/docs/current/static/standby-settings.html)
* `standby_mode`: Can be specified with the string ('on'/'off'), or by using a Boolean value (`true`/`false`).
* `primary_conninfo`
* `primary_slot_name`
* `trigger_file`
* `recovery_min_apply_delay`

##### `target`
Provides the target for the rule, and is generally an internal only property.
 
**Use with caution.**

#### postgresql::server::role
Creates a role or user in PostgreSQL.

##### `connection_limit`
Specifies how many concurrent connections the role can make.

Default value: '-1', meaning no limit.

##### `connect_settings`
Specifies a hash of environment variables used when connecting to a remote server.

Default value: Connects to the local Postgres instance.

##### `createdb`
Specifies whether to grant the ability to create new databases with this role.

Default value: `false`.

##### `createrole`
Specifies whether to grant the ability to create new roles with this role.

Default value: `false`.

##### `inherit`
Specifies whether to grant inherit capability for the new role.

Default value: `true`.

##### `login`
Specifies whether to grant login capability for the new role.

Default value: `true`.

##### `password_hash`
Sets the hash to use during password creation. If the password is not already pre-encrypted in a format that PostgreSQL supports, use the `postgresql_password` function to provide an MD5 hash here, for example:

##### `update_password`
If set to true, updates the password on changes. Set this to false to not modify the role's password after creation.

```puppet
postgresql::server::role { 'myusername':
  password_hash => postgresql_password('myusername', 'mypassword'),
}
```

##### `replication`

Provides provides replication capabilities for this role if set to `true`.

Default value: `false`.

##### `superuser`

Specifies whether to grant super user capability for the new role.

Default value: `false`.

##### `username`

Defines the username of the role to create.

Default value: the namevar.

#### postgresql::server::schema

Creates a schema.

##### `connect_settings`

Specifies a hash of environment variables used when connecting to a remote server.

Default value: Connects to the local Postgres instance.

##### `db`

Required.

Sets the name of the database in which to create this schema.

##### `owner`

Sets the default owner of the schema.

##### `schema`

Sets the name of the schema.

Default value: the namevar.

#### postgresql::server::table_grant

Manages grant-based access privileges for users. Consult the PostgreSQL documentation for `grant` for more information.

##### `connect_settings`

Specifies a hash of environment variables used when connecting to a remote server.

Default value: Connects to the local Postgres instance.

##### `db`

Specifies which database the table is in.

##### `privilege`

Specifies comma-separated list of privileges to grant. Valid options: 'ALL', 'SELECT', 'INSERT', 'UPDATE', 'DELETE', 'TRUNCATE', 'REFERENCES', 'TRIGGER'.

##### `psql_db`

Specifies the database to execute the grant against.

This should not ordinarily be changed from the default.

Default value: 'postgres'.

##### `psql_user`

Specifies the OS user for running `psql`.

Default value: The default user for the module, usually 'postgres'.

##### `role`

Specifies the role or user to whom you are granting access.

##### `table`

Specifies the table to which you are granting access.

#### postgresql::server::tablespace

Creates a tablespace. If necessary, also creates the location and assigns the same permissions as the PostgreSQL server.

##### `connect_settings`

Specifies a hash of environment variables used when connecting to a remote server.

Default value: Connects to the local Postgres instance.

##### `location`

Specifies the path to locate this tablespace.

##### `owner`

Specifies the default owner of the tablespace.

##### `spcname`

Specifies the name of the tablespace.

Default value: the namevar.

### Types

#### postgresql_psql

Enables Puppet to run psql statements.

##### `command`

Required.

Specifies the SQL command to execute via psql.

##### `cwd`

Specifies the working directory under which the psql command should be executed.

Default value: '/tmp'.

##### `db`

Specifies the name of the database to execute the SQL command against.

##### `environment`

Specifies any additional environment variables you want to set for a SQL command. Multiple environment variables should be specified as an array.

##### `name`

Sets an arbitrary tag for your own reference; the name of the message. This is the namevar.

##### `onlyif`

Sets an optional SQL command to execute prior to the main command. This is generally intended to be used for idempotency, to check for the existence of an object in the database to determine whether or not the main SQL command needs to be executed at all.

##### `port`

Specifies the port of the database server to execute the SQL command against.

##### `psql_group`

Specifies the system user group account under which the psql command should be executed.

Default value: 'postgres'.

##### `psql_path`

Specifies the path to psql executable.

Default value: 'psql'.

##### `psql_user`

Specifies the system user account under which the psql command should be executed.

Default value: 'postgres'.

##### `refreshonly`

Specifies whether to execute the SQL only if there is a notify or subscribe event.

Valid values: `true`, `false`.

Default value: `false`.

##### `search_path`

Defines the schema search path to use when executing the SQL command.

##### `unless`

The inverse of `onlyif`.

#### postgresql_conf

Allows Puppet to manage `postgresql.conf` parameters.

##### `name`

Specifies the PostgreSQL parameter name to manage.

This is the namevar.

##### `target`

Specifies the path to `postgresql.conf`.

Default value: '/etc/postgresql.conf'.

##### `value`

Specifies the value to set for this parameter.

#### postgresql_replication_slot

Allows you to create and destroy replication slots to register warm standby replication on a PostgreSQL master server.

##### `name`

Specifies the name of the slot to create. Must be a valid replication slot name.

This is the namevar.

##### `ensure`

Required.

Specifies the action to create or destroy named slot. 

Valid values: 'present', 'absent'.

Default value: 'present'.

#### postgresql_conn_validator

Validate the connection to a local or remote PostgreSQL database using this type.

##### `connect_settings`

Specifies a hash of environment variables used when connecting to a remote server. This is an alternative to providing individual parameters (`host`, etc). If provided, the individual parameters take precedence.

Default value: {}

##### `db_name`

Specifies the name of the database you wish to test.

Default value: ''

##### `db_password`

Specifies the password to connect with. Can be left blank if `.pgpass` is being used, otherwise not recommended.

Default value: ''

##### `db_username`

Specifies the username to connect with.

Default value: ''

When using a Unix socket and ident auth, this is the user you are running as.

##### `command`

This is the command run against the target database to verify connectivity.

Default value: 'SELECT 1'

##### `host`

Sets the hostname of the database you wish to test.

Default value: '', which generally uses the designated local Unix socket.

**If the host is remote you must provide a username.**

##### `port`

Defines the port to use when connecting.

Default value: '' 

##### `run_as`

Specifies the user to run the `psql` command as. This is important when trying to connect to a database locally using Unix sockets and `ident` authentication. Not needed for remote testing.

##### `sleep`

Sets the number of seconds to sleep for before trying again after a failure.

##### `tries`

Sets the number of attempts after failure before giving up and failing the resource.

### Functions

#### postgresql_password

Generates a PostgreSQL encrypted password, use `postgresql_password`. Call it from the command line and then copy and paste the encrypted password into your manifest:

```shell
puppet apply --execute 'notify { 'test': message => postgresql_password('username', 'password') }'
```

Alternatively, you can call this from your production manifests, but the manifests will then contain a clear text version of your passwords.

#### postgresql_acls_to_resources_hash(acl_array, id, order_offset)

This internal function converts a list of `pg_hba.conf` based ACLs (passed in as an array of strings) to a format compatible with the `postgresql::pg_hba_rule` resource.

**This function should only be used internally by the module**.

## Limitations

Works with versions of PostgreSQL from 8.1 through 9.5.

Currently, the postgresql module is tested on the following operating systems:

* Debian 6.x, 7.x, 8.x.
* CentOS 5.x, 6.x, and 7.x.
* Ubuntu 10.04 and 12.04, 14.04.

Other systems might be compatible, but are not being actively tested.

### Apt module support

While this module supports both 1.x and 2.x versions of the 'puppetlabs-apt' module, it does not support 'puppetlabs-apt' 2.0.0 or 2.0.1.

### PostGIS support

PostGIS is currently considered an unsupported feature, as it doesn't work on all platforms correctly.

### All versions of RHEL/CentOS

If you have SELinux enabled you must add any custom ports you use to the `postgresql_port_t` context.  You can do this as follows:

```shell
semanage port -a -t postgresql_port_t -p tcp $customport
```

## Development

Puppet Labs modules on the Puppet Forge are open projects, and community contributions are essential for keeping them great. We can’t access the huge number of platforms and myriad hardware, software, and deployment configurations that Puppet is intended to serve. We want to keep it as easy as possible to contribute changes so that our modules work in your environment. There are a few guidelines that we need contributors to follow so that we can have a chance of keeping on top of things. For more information, see our [module contribution guide](https://docs.puppetlabs.com/forge/contributing.html).

### Tests

There are two types of tests distributed with this module. Unit tests with `rspec-puppet` and system tests using `rspec-system`.

For unit testing, make sure you have:

* rake
* bundler

Install the necessary gems:

```shell
bundle install --path=vendor
```

And then run the unit tests:

```shell
bundle exec rake spec
```

The unit tests are run in Travis-CI as well. If you want to see the results of your own tests, register the service hook through Travis-CI via the accounts section for your GitHub clone of this project.

To run the system tests, make sure you also have:

* Vagrant > 1.2.x
* VirtualBox > 4.2.10

Then run the tests using:

```shell
bundle exec rspec spec/acceptance
```

To run the tests on different operating systems, see the sets available in `.nodeset.yml` and run the specific set with the following syntax:

```shell
RSPEC_SET=debian-607-x64 bundle exec rspec spec/acceptance
```

### Contributors

View the full list of contributors on [Github](https://github.com/puppetlabs/puppetlabs-postgresql/graphs/contributors).
