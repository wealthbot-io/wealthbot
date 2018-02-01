## apache_setenv provider

This is a custom type and provider supplied by `augeasproviders`.

### manage simple entry

    apache_setenv { "SPECIAL_PATH":
      ensure => present,
      value  => "/foo/bin",
    }

### manage entry with no value

    apache_setenv { "ENABLE_FOO":
      ensure  => present,
    }

### delete entry

    apache_setenv { "SPECIAL_PATH":
      ensure => absent,
    }

### manage entry in another config location

    apache_setenv { "SPECIAL_PATH":
      ensure => present,
      value  => "/foo/bin",
      target => "/etc/httpd/conf.d/app.conf",
    }
