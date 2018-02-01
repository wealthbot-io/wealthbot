## mailalias provider

This is a provider for a type distributed in Puppet core: [mailalias type
reference](http://docs.puppetlabs.com/references/stable/type.html#mailalias).

The provider needs to be explicitly given as `augeas` to use `augeasproviders`.

### manage simple entry

    mailalias { "example":
      ensure    => present,
      recipient => "bar",
      provider  => augeas,
    }

### manage entry with multiple recipients

    mailalias { "example":
      ensure    => present,
      recipient => [ "fred", "bob" ],
      provider  => augeas,
    }

### manage entry in another location

    mailalias { "example":
      ensure    => present,
      recipient => "bar",
      target    => "/etc/anotheraliases",
      provider  => augeas,
    }

### delete entry

    mailalias { "mailer-daemon":
      ensure   => absent,
      provider => augeas,
    }
