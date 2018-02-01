## host provider

This is a provider for a type distributed in Puppet core: [host type
reference](http://docs.puppetlabs.com/references/stable/type.html#host).

The provider needs to be explicitly given as `augeas` to use `augeasproviders`.

The `comment` parameter is only supported on Puppet 2.7 and higher.

### manage simple entry

    host { "example":
      ensure   => present,
      ip       => "192.168.1.1",
      provider => augeas,
    }

### manage entry with aliases and comment

    host { "example":
      ensure       => present,
      ip           => "192.168.1.1",
      host_aliases => [ "foo-a", "foo-b" ],
      comment      => "test",
      provider     => augeas,
    }

### manage entry in another location

    host { "example":
      ensure   => present,
      ip       => "192.168.1.1",
      target   => "/etc/anotherhosts",
      provider => augeas,
    }

### delete entry

    host { "iridium":
      ensure   => absent,
      provider => augeas,
    }

### remove aliases

    host { "iridium":
      ensure       => present,
      host_aliases => [],
      provider     => augeas,
    }

### remove comment

    host { "argon":
      ensure   => present,
      comment  => "",
      provider => augeas,
    }
