## apache_directive provider

This is a custom type and provider supplied by `augeasproviders`.

### manage simple entry

    apache_directive { "StartServers":
      args   => 4,
      ensure => present,
    }

### delete entry

    apache_directive { "ServerName":
      args   => "foo.example.com",
      ensure => absent,
    }

### manage entry in another config location

    apache_directive { "SetEnv":
      args        => ["SPECIAL_PATH", "/foo/bin"],
      args_params => 1,
      ensure      => present,
      target      => "/etc/httpd/conf.d/app.conf",
    }

The `SetEnv` directive is not unique per scope: the first arg identifies the entry we want to update, and needs to be taken into account. For this reason, we set `args_params` to `1`.

### set a value in a given context

    apache_directive { 'StartServers for mpm_prefork_module':
      ensure      => present,
      name        => 'StartServers',
      context     => 'IfModule[arg="mpm_prefork_module"]',
      args        => 4,
    }


The directive is nested in the context of the `mpm_prefork_module` module, so we specify this with the `context` parameter.

The value of `StartServers` for the `mpm_prefork_module` module will be set/updated to `4`. Note that the `IfModule` entry will not be created if it is missing.
