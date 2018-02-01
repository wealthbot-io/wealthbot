## shellvar provider

This is a custom type and provider supplied by `augeasproviders`.

### manage simple entry

    shellvar { "HOSTNAME":
      ensure => present,
      target => "/etc/sysconfig/network",
      value  => "host.example.com",
    }

    shellvar { "disable rsyncd":
      ensure   => present,
      target   => "/etc/default/rsync",
      variable => "RSYNC_ENABLE",
      value    => "false",
    }

    shellvar { "ntpd options":
      ensure   => present,
      target   => "/etc/sysconfig/ntpd",
      variable => "OPTIONS",
      value    => "-g -x -c /etc/myntp.conf",
    }

### manage entry with comment

    shellvar { "HOSTNAME":
      ensure  => present,
      target  => "/etc/sysconfig/network",
      comment => "My server's hostname",
      value   => "host.example.com",
    }

### export values

    shellvar { "HOSTNAME":
      ensure  => exported,
      target  => "/etc/sysconfig/network",
      value   => "host.example.com",
    }

### unset values

    shellvar { "HOSTNAME":
      ensure  => unset,
      target  => "/etc/sysconfig/network",
    }

### force quoting style

Values needing quotes will automatically get them, but they can also be
explicitly enabled.  Unfortunately the provider doesn't help with quoting the
values themselves.

    shellvar { "RSYNC_IONICE":
      ensure   => present,
      target   => "/etc/default/rsync",
      value    => "-c3",
      quoted   => "single",
    }

### delete entry

    shellvar { "RSYNC_IONICE":
      ensure => absent,
      target => "/etc/default/rsync",
    }

### remove comment from entry

    shellvar { "HOSTNAME":
      ensure  => present,
      target  => "/etc/sysconfig/network",
      comment => "",
    }

### replace commented value with entry

    shellvar { "HOSTNAME":
      ensure    => present,
      target    => "/etc/sysconfig/network",
      comment   => "",
      uncomment => true,
    }

### array values

You can pass array values to the type.

There are two ways of rendering array values, and the behavior is set using
the `array_type` parameter. `array_type` takes three possible values:

* `auto` (default): detects the type of the existing variable, defaults to `string`;
* `string`: renders the array as a string, with a space as element separator;
* `array`: renders the array as a shell array.

For example:

    shellvar { "PORTS":
      ensure     => present,
      target     => "/etc/default/puppetmaster",
      value      => ["18140", "18141", "18142"],
      array_type => "auto",
    }

will create `PORTS="18140 18141 18142"` by default, and will change `PORTS=(123)` to `PORTS=("18140" "18141" "18142")`.

    shellvar { "PORTS":
      ensure     => present,
      target     => "/etc/default/puppetmaster",
      value      => ["18140", "18141", "18142"],
      array_type => "string",
    }

will create `PORTS="18140 18141 18142"` by default, and will change `PORTS=(123)` to `PORTS="18140 18141 18142"`.

    shellvar { "PORTS":
      ensure     => present,
      target     => "/etc/default/puppetmaster",
      value      => ["18140", "18141", "18142"],
      array_type => "array",
    }

will create `PORTS=("18140" "18141" "18142")` by default, and will change `PORTS=123` to `PORTS=(18140 18141 18142)`.

Quoting is honored for arrays:

* When using the string behavior, quoting is global to the string;
* When using the array behavior, each value in the array is quoted as requested.

### appending to arrays

    shellvar { "GRUB_CMDLINE_LINUX":
      ensure       => present,
      target       => "/etc/default/grub",
      value        => "cgroup_enable=memory",
      array_append => true,
    }

will change `GRUB_CMDLINE_LINUX="quiet splash"` to `GRUB_CMDLINE_LINUX="quiet splash cgroup_enable=memory"`.

    shellvar { "GRUB_CMDLINE_LINUX":
      ensure       => present,
      target       => "/etc/default/grub",
      value        => ["quiet", "cgroup_enable=memory"],
      array_append => true,
    }

will also change `GRUB_CMDLINE_LINUX="quiet splash"` to `GRUB_CMDLINE_LINUX="quiet splash cgroup_enable=memory"`.
