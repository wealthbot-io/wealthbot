puppet-nodejs
=============

[![Build
Status](https://travis-ci.org/willdurand/puppet-nodejs.png?branch=master)](https://travis-ci.org/willdurand/puppet-nodejs)

This module allows you to install [Node.js](https://nodejs.org/) and
[NPM](https://npmjs.org/). This module is published on the Puppet Forge as
[willdurand/nodejs](https://forge.puppetlabs.com/willdurand/nodejs).

Version 1.9
-----------

The 1.x branch will be EOLed two months after ``2.0`` is released.
If you need the docs for 1.x, see [1.9](https://github.com/willdurand/puppet-nodejs/tree/1.9).

Installation
------------

### Manual installation

This modules depends on
[puppetlabs/stdlib](https://github.com/puppetlabs/puppetlabs-stdlib) and
[puppetlabs/gcc](https://github.com/puppetlabs/puppetlabs-gcc).
So all repositories have to be checked out:

```bash
git clone git://github.com/willdurand/puppet-nodejs.git modules/nodejs
git clone git://github.com/puppetlabs/puppetlabs-stdlib.git modules/stdlib
git clone git://github.com/puppetlabs/puppetlabs-gcc.git modules/gcc
```

For Redhat based OS, the following are (typical) additional requirements:

```bash
git clone git://github.com/treydock/puppet-gpg_key.git modules/gpg_key
```

### Puppet Module Tool:

    puppet module install willdurand/nodejs

### Librarian-puppet:

    mod 'willdurand/nodejs', '2.x.x'

Usage
-----

There are a few ways to use this puppet module. The easiest one is just using the class definition:

```puppet
class { 'nodejs':
  version => 'v6.0.0',
}
```
This install the precompiled Node.js version `v6.0.0` on your machine. `node` and `npm` will be available in your `$PATH` at `/usr/local/bin` so you can just start using `node`.

Shortcuts are provided to easily install the `latest` release or the latest LTS release (`lts`) by setting the `version` parameter to `latest` or `lts`. It will automatically look for the last release available on https://nodejs.org.

```puppet
# installs the latest nodejs version
class { 'nodejs':
  version => 'latest',
}
```

```puppet
# installs the latest nodejs LTS version
class { 'nodejs':
  version => 'lts',
}
```

### Compiling from source

In order to compile from source with `gcc`, the `make_install` must be `true`.

```puppet
class { 'nodejs':
  version      => 'lts',
  make_install => true,
}
```

### Setup using a generic version

Instead of fixing one specific nodejs version it's also possible to tell this module whether to use the latest of a certain minor release:

``` puppet
class { '::nodejs':
  version => '6.3',
}
```
This will install the latest patch release of `6.3.x`.

The same is possible with major releases:

``` puppet
class { '::nodejs':
  version => '6.x',
}
```

This will install the latest `6.x` release.

### Setup with a given download timeout

Due to infrastructures with slower connections the download of the nodejs binaries should be
configurable:

``` puppet
class { '::nodejs':
  download_timeout => 0,
}
```

### Setup multiple versions of Node.js

If you need more than one installed version of Node.js on your machine, you can just configure them using the `instances` list.

```puppet
class { '::nodejs':
  version => 'v6.0.0',
  instances => {
    "node-v6" => {
      version => 'v6.0.0'
    },
    "node-v5" => {
      version => 'v5.0.0'
    }
  },
}
```

This will install the node version `v5.0.0` and `v6.0.0` on your machine with `v6.0.0` as default and `v5.0.0` as versioned binary in `/usr/local/bin`:

```
/usr/local/bin/node # v6.0.0
/usr/local/bin/node-v6.0.0
/usr/local/bin/npm-v6.0.0

/usr/local/bin/npm # NPM shipped with v6.0.0
/usr/local/bin/npm-v5.0.0
/usr/local/bin/npm-v5.0.0
```

It is also possible to remove those versions again:

```puppet
class { '::nodejs':
  # ...
  instances_to_remove => ['5.4'],
}
```

After the run the directory __/usr/local/node/node-v5.4.1__ has been purged.
The link __/usr/local/bin/node-v5.4.1__ is also purged.

__Note:__ It is not possible to install and uninstall an instance in the same run. The version defined in the `version` parameter of the `nodejs` class can't be removed in the same run. If a version should be removed, it must not be present in the `instances` list.

### Setup using custom amount of cpu cores

By default, all available cpu (that are detected using the `::processorcount` fact)  cores are being used to compile nodejs. Set `cpu_cores` to any number of cores you want to use.

```puppet
class { 'nodejs':
  version   => 'lts',
  cpu_cores => 2,
}
```

### Configuring $NODE_PATH

The environment variable $NODE_PATH can be configured using the `init` manifest:

```puppet
class { '::nodejs':
  version   => 'lts',
  node_path => '/your/custom/node/path',
}
```

It is not possible to adjust a $NODE_PATH through ``::nodejs::install``.

### Binary path

`node` and `npm` are linked to `/usr/local/bin` to be available in your system `$PATH` by default. To link those binaries to e.g `/bin`, just set the parameter `target_dir`.

```puppet
class { 'nodejs':
  version    => 'lts',
  target_dir => '/bin',
}
```

### NPM

Also, this module installs [NPM](https://npmjs.org/) by default.

### NPM Provider

This module adds a new provider: `npm`. You can use it as usual:

```puppet
package { 'express':
  provider => npm
}
```

Note: When deploying a new machine without nodejs already installed, your npm package definition requires the nodejs class:

```puppet
class { 'nodejs':
  version => 'lts'
}

package { 'express':
  provider => 'npm',
  require  => Class['nodejs']
}
```

### NPM installer

The nodejs installer can be used if a npm package should not be installed globally, but in a certain directory.

There are two approaches how to use this feature:

#### Installing a single package into a directory

```puppet
::nodejs::npm { 'npm-webpack':
  ensure    => present, # absent would uninstall this package
  pkg_name  => 'webpack',
  version   => 'x.x',               # optional
  options   => '-x -y -z',          # CLI options passed to the "npm install" cmd, optional
  exec_user => 'vagrant',           # exec user, optional
  directory => '/target/directory', # target directory
  home_dir  => '/home/vagrant',     # home directory of the user which runs the installation (vagrant in this case)
}
```

This would install the package ``webpack`` into ``/target/directory`` with version ``x.x``.

#### Executing a ``package.json`` file

```puppet
::nodejs::npm { 'npm-install-dir':
  list      => true,       # flag to tell puppet to execute the package.json file
  directory => '/target',
  exec_user => 'vagrant',
  options   => '-x -y -z',
}
```

### Proxy

When your puppet agent is behind a web proxy, export the `http_proxy` environment variable:

```bash
export http_proxy=http://myHttpProxy:8888
```

### Skipping package setup

As discussed in [willdurand/composer#44](https://github.com/willdurand/puppet-composer/issues/44) each module should get a `build_deps` parameter which can be used in edge cases in order to turn the package setup of this module off:

``` puppet
class { '::nodejs':
  build_deps => false,
}
```

In this case you'll need to take care of the following packages:

- `tar`
- `ruby`
- `wget`
- `semver` (GEM used by ruby)
- `make` (if `make_install` = `true`)
- `gcc` compiler (if `make_install` = `true`)

Development with `nix`
----------------------

If you're using `nix` as dependency manager, you can create a custom shell which contains all dependencies declared in `Gemfile.lock` by running `nix-shell` in the root directory.

Running the tests
-----------------

Install the dependencies using [Bundler](https://bundler.io):

    bundle install

Run the following command:

    bundle exec rake test


Authors
-------

* William Durand <william.durand1@gmail.com>
* Johannes Graf ([@grafjo](https://github.com/grafjo))
* Maximilian Bosch ([@Ma27](https://github.com/Ma27))


License
-------

puppet-nodejs is released under the MIT License. See the bundled LICENSE file
for details.
