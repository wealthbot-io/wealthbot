# Puppet Redis

## Build status

[![Build Status](https://travis-ci.org/arioch/puppet-redis.png?branch=master)](https://travis-ci.org/arioch/puppet-redis)

## Example usage

### Standalone

```puppet
include ::redis
```

### Master node

```puppet
class { '::redis':
  bind => '10.0.1.1',
}
```

With authentication

```puppet
class { '::redis':
  bind       => '10.0.1.1',
  masterauth => 'secret',
}
```

### Slave node

```puppet
class { '::redis':
  bind    => '10.0.1.2',
  slaveof => '10.0.1.1 6379',
}
```

With authentication

```puppet
class { '::redis':
  bind       => '10.0.1.2',
  slaveof    => '10.0.1.1 6379',
  masterauth => 'secret',
}
```

### Redis 3.0 Clustering

```puppet
class { '::redis':
  bind                 => '10.0.1.2',
  appendonly           => true,
  cluster_enabled      => true,
  cluster_config_file  => 'nodes.conf',
  cluster_node_timeout => 5000,
}
```

### Manage repositories

Disabled by default but if you really want the module to manage the required
repositories you can use this snippet:

```puppet
class { '::redis':
  manage_repo => true,
}
```

On Ubuntu, "chris-lea/redis-server" ppa repo will be added. You can change it by using ppa_repo parameter:

```puppet
class { '::redis':
  manage_repo => true,
  ppa_repo    => 'ppa:rwky/redis',
}
```

### Redis Sentinel

Optionally install and configuration a redis-sentinel server.

With default settings:

```puppet
include ::redis::sentinel
```

With adjustments:

```puppet
class { '::redis::sentinel':
  master_name      => 'cow',
  redis_host       => '192.168.1.5',
  failover_timeout => 30000,
}
```

## `redisget()` function

`redisget()` takes two or three arguments that are strings. The first is the key
to be looked up, the second is the URL to the Redis service and the
optional third argument is a default value to use if the key is not
found or connection to the Redis service cannot be made.

Example of basic usage.

```puppet
$version = redisget('version.myapp', 'redis://redis.example.com:6379')
```

Example with default value specified. This is useful to allow for cached
data in case Redis is not available.

```puppet
$version = redisget('version.myapp', 'redis://redis.example.com:6379', $::myapp_version)
```

You must have the 'redis' gem installed on your puppet master.

## Unit testing

Plain RSpec:

    $ rake spec

Using bundle:

    $ bundle exec rake spec

Test against a specific Puppet or Facter version:

    $ PUPPET_VERSION=3.2.1  bundle update && bundle exec rake spec
    $ PUPPET_VERSION=4.10.0 bundle update && bundle exec rake spec
    $ FACTER_VERSION=1.6.8  bundle update && bundle exec rake spec

## Puppet 3 Support

Puppet 3 is EOL as-of January 2017. The last release of this module that will
support Puppet 3.X and earlier will be the 3.X.X module releases.

Module versions from 4.X.X onwards will use Puppet 4 only features and will not work with
earlier versions.

We would recommend upgrading your Puppet agent to the latest release, as Puppet 4 comes with a load of awesome new features.

If you're stuck with older Puppet, you could also fork the module from 3.0.0 and use your fork as a Puppet 3 supported version.

## Contributing

* Fork it
* Create a feature branch (`git checkout -b my-new-feature`)
* Run rspec tests (`bundle exec rake spec`)
* Commit your changes (`git commit -am 'Added some feature'`)
* Push to the branch (`git push origin my-new-feature`)
* Create new Pull Request
