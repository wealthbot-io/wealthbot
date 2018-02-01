## puppet_auth provider

This is a custom type and provider supplied by `augeasproviders`.

It requires the `Puppet_Auth.lns` lens, which is provided with versions of Augeas strictly greater than 0.10.0.

### manage simple entry

    puppet_auth { 'Deny /facts':
      ensure        => present,
      path          => '/facts',
      authenticated => 'any',
    }

### manage regex entry

    puppet_auth { 'Deny ~ ^/facts/([^/]+)$':
      ensure        => present,
      path          => '^/facts/([^/]+)$',
      path_regex    => true,
      authenticated => 'any',
    }

### add multiple environments

    puppet_auth { 'Allow /facts for prod and dev environments from same client':
      ensure        => present,
      path          => '/facts',
      authenticated => 'any',
      allow         => '$1',
      environments  => ['prod', 'dev'],
    }

### ensure an entry is before a given path

`ins_after` provides the opposite functionality, so an entry is created after a
given path.

    puppet_auth { 'Allow /facts before first denied rule':
      ensure        => present,
      path          => '/facts',
      authenticated => 'any',
      allow         => '*',
      ins_before    => 'first deny',
    }

### delete entry

    puppet_auth { 'Remove /facts':
      ensure => absent,
      path   => '/facts',
    }
