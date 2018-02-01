# rabbitmq

[![License](https://img.shields.io/github/license/voxpupuli/puppet-rabbitmq.svg)](https://github.com/voxpupuli/puppet-rabbitmq/blob/master/LICENSE)
[![Build Status](https://travis-ci.org/voxpupuli/puppet-rabbitmq.svg?branch=master)](https://travis-ci.org/voxpupuli/puppet-rabbitmq)
[![Code Coverage](https://coveralls.io/repos/github/voxpupuli/puppet-rabbitmq/badge.svg?branch=master)](https://coveralls.io/github/voxpupuli/puppet-rabbitmq)
[![Puppet Forge](https://img.shields.io/puppetforge/v/puppet/rabbitmq.svg)](https://forge.puppetlabs.com/puppet/rabbitmq)
[![Puppet Forge - downloads](https://img.shields.io/puppetforge/dt/puppet/rabbitmq.svg)](https://forge.puppetlabs.com/puppet/rabbitmq)
[![Puppet Forge - endorsement](https://img.shields.io/puppetforge/e/puppet/rabbitmq.svg)](https://forge.puppetlabs.com/puppet/rabbitmq)
[![Puppet Forge - scores](https://img.shields.io/puppetforge/f/puppet/rabbitmq.svg)](https://forge.puppetlabs.com/puppet/rabbitmq)

#### Table of Contents

1. [Overview](#overview)
2. [Module Description - What the module does and why it is useful](#module-description)
3. [Setup - The basics of getting started with rabbitmq](#setup)
    * [What rabbitmq affects](#what-rabbitmq-affects)
    * [Setup requirements](#setup-requirements)
4. [Usage - Configuration options and additional functionality](#usage)
5. [Reference - An under-the-hood peek at what the module is doing and how](#reference)
5. [Limitations - OS compatibility, etc.](#limitations)
   * [RedHat module dependencies](#redhat-module-dependecies)
6. [Development - Guide for contributing to the module](#development)

## Overview

This module manages RabbitMQ (www.rabbitmq.com)

## Module Description
The rabbitmq module sets up rabbitmq and has a number of providers to manage
everything from vhosts to exchanges after setup.

This module has been tested against 3.5.x and 3.6.x (as well as earlier
versions) and is known to not support all features against versions
prior to 2.7.1.

## Setup

### What rabbitmq affects

* rabbitmq repository files.
* rabbitmq package.
* rabbitmq configuration file.
* rabbitmq service.

## Usage

All options and configuration can be done through interacting with the parameters
on the main rabbitmq class.
These are now documented via [Puppet Strings](https://github.com/puppetlabs/puppet-strings)

For convenience, some examples are duplicated here:

## rabbitmq class

To begin with the rabbitmq class controls the installation of rabbitmq.  In here
you can control many parameters relating to the package and service, such as
disabling puppet support of the service:

```puppet
class { 'rabbitmq':
  service_manage    => false,
  port              => '5672',
  delete_guest_user => true,
}
```

### Environment Variables
To use RabbitMQ Environment Variables, use the parameters `environment_variables` e.g.:

```puppet
class { 'rabbitmq':
  port                  => 5672,
  environment_variables => {
    'NODENAME'    => 'node01',
    'SERVICENAME' => 'RabbitMQ'
  }
}
```

### Variables Configurable in rabbitmq.config
To change RabbitMQ Config Variables in rabbitmq.config, use the parameters `config_variables` e.g.:

```puppet
class { 'rabbitmq':
  port             => 5672,
  config_variables => {
    'hipe_compile' => true,
    'frame_max'    => 131072,
    'log_levels'   => "[{connection, info}]"
  }
}
```

To change Erlang Kernel Config Variables in rabbitmq.config, use the parameters
`config_kernel_variables` e.g.:

```puppet
class { 'rabbitmq':
  port                    => 5672,
  config_kernel_variables => {
    'inet_dist_listen_min' => 9100,
    'inet_dist_listen_max' => 9105,
  }
}
```

To change Management Plugin Config Variables in rabbitmq.config, use the parameters
`config_management_variables` e.g.:

```puppet
class { 'rabbitmq':
  config_management_variables => {
    'rates_mode' => 'basic',
  }
}
```

### Additional Variables Configurable in rabbitmq.config
To change Additional Config Variables in rabbitmq.config, use the parameter
`config_additional_variables` e.g.:

```puppet
class { 'rabbitmq':
  config_additional_variables => {
    'autocluster' => '[{consul_service, "rabbit"},{cluster_name, "rabbit"}]',
    'foo'         => '[{bar, "baz"}]'
  }
}
```
This will result in the following config appended to the config file:
```
% Additional config
  {autocluster, [{consul_service, "rabbit"},{cluster_name, "rabbit"}]},
  {foo, [{bar, "baz"}]}
```
(This is required for the [autocluster plugin](https://github.com/aweber/rabbitmq-autocluster)

### Clustering
To use RabbitMQ clustering facilities, use the rabbitmq parameters
`config_cluster`, `cluster_nodes`, and `cluster_node_type`, e.g.:

```puppet
class { 'rabbitmq':
  config_cluster           => true,
  cluster_nodes            => ['rabbit1', 'rabbit2'],
  cluster_node_type        => 'ram',
  erlang_cookie            => 'A_SECRET_COOKIE_STRING',
  wipe_db_on_cookie_change => true,
}
```

### rabbitmq\_user

query all current users: `$ puppet resource rabbitmq_user`

```puppet
rabbitmq_user { 'dan':
  admin    => true,
  password => 'bar',
}
```
Optional parameter tags will set further rabbitmq tags like monitoring, policymaker, etc.
To set the administrator tag use admin-flag.
```puppet
rabbitmq_user { 'dan':
  admin    => true,
  password => 'bar',
  tags     => ['monitoring', 'tag1'],
}
```

### rabbitmq\_vhost

query all current vhosts: `$ puppet resource rabbitmq_vhost`

```puppet
rabbitmq_vhost { 'myvhost':
  ensure => present,
}
```

### rabbitmq\_exchange

```puppet
rabbitmq_exchange { 'myexchange@myvhost':
  ensure      => present,
  user        => 'dan',
  password    => 'bar',
  type        => 'topic',
  internal    => false,
  auto_delete => false,
  durable     => true,
  arguments   => {
    hash-header => 'message-distribution-hash'
  }
}
```

### rabbitmq\_queue

```puppet
rabbitmq_queue { 'myqueue@myvhost':
  ensure      => present,
  user        => 'dan',
  password    => 'bar',
  durable     => true,
  auto_delete => false,
  arguments   => {
    x-message-ttl          => 123,
    x-dead-letter-exchange => 'other'
  },
}
```

### rabbitmq\_binding

```puppet
rabbitmq_binding { 'myexchange@myqueue@myvhost':
  ensure           => present,
  user             => 'dan',
  password         => 'bar',
  destination_type => 'queue',
  routing_key      => '#',
  arguments        => {},
}
```

```puppet
rabbitmq_binding { 'binding 1':
  ensure           => present,
  source           => 'myexchange',
  destination      => 'myqueue',
  vhost            => 'myvhost',
  user             => 'dan',
  password         => 'bar',
  destination_type => 'queue',
  routing_key      => 'key1',
  arguments        => {},
}

rabbitmq_binding { 'binding 2':
  ensure           => present,
  source           => 'myexchange',
  destination      => 'myqueue',
  vhost            => 'myvhost',
  user             => 'dan',
  password         => 'bar',
  destination_type => 'queue',
  routing_key      => 'key2',
  arguments        => {},
}

```

### rabbitmq\_user\_permissions

```puppet
rabbitmq_user_permissions { 'dan@myvhost':
  configure_permission => '.*',
  read_permission      => '.*',
  write_permission     => '.*',
}
```

### rabbitmq\_policy

```puppet
rabbitmq_policy { 'ha-all@myvhost':
  pattern    => '.*',
  priority   => 0,
  applyto    => 'all',
  definition => {
    'ha-mode'      => 'all',
    'ha-sync-mode' => 'automatic',
  },
}
```

### rabbitmq\_plugin

query all currently enabled plugins `$ puppet resource rabbitmq_plugin`

```puppet
rabbitmq_plugin {'rabbitmq_stomp':
  ensure => present,
}
```

### rabbitmq\_parameter

```puppet
  rabbitmq_parameter { 'documentumShovel@/':
    component_name => '',
    value          => {
        'src-uri'    => 'amqp://',
        'src-queue'  => 'my-queue',
        'dest-uri'   => 'amqp://remote-server',
        'dest-queue' => 'another-queue',
    },
  }

  rabbitmq_parameter { 'documentumFed@/':
    component_name => 'federation-upstream',
    value          => {
        'uri'     => 'amqp://myserver',
        'expires' => '360000',
    },
  }
```

## Reference

## Classes

* rabbitmq: Main class for installation and service management.
* rabbitmq::config: Main class for rabbitmq configuration/management.
* rabbitmq::install: Handles package installation.
* rabbitmq::params: Different configuration data for different systems.
* rabbitmq::service: Handles the rabbitmq service.
* rabbitmq::repo::apt: Handles apt repo for Debian systems.
* rabbitmq::repo::rhel: Handles rpm repo for Redhat systems.

### Module dependencies

If running CentOS/RHEL, ensure the epel repo, or another repo containing a
suitable Erlang version, is present. On Debian systems, puppetlabs/apt
(>=2.0.0 < 5.0.0) is a soft dependency.

To have a suitable erlang version installed on RedHat and Debian systems,
you have to install another puppet module from http://forge.puppetlabs.com/garethr/erlang with:

    puppet module install garethr-erlang

This module handles the packages for erlang.
To use the module, add the following snippet to your site.pp or an appropriate profile class:

For RedHat systems:

    include 'erlang'
    class { 'erlang': epel_enable => true}

For Debian systems:

    include 'erlang'
    package { 'erlang-base':
      ensure => 'latest',
    }

This module also depends on voxpupuli/archive to install rabbitmqadmin.

## Development

This module is maintained by [Vox Pupuli](https://voxpupuli.org/). Voxpupuli
welcomes new contributions to this module, especially those that include
documentation and rspec tests. We are happy to provide guidance if necessary.

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for more details.

### Authors
* Jeff McCune <jeff@puppetlabs.com>
* Dan Bode <dan@puppetlabs.com>
* RPM/RHEL packages by Vincent Janelle <randomfrequency@gmail.com>
* Puppetlabs Module Team
* Voxpupuli Team
