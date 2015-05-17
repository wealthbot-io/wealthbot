# puppet-resolvconf

## Overview

Manage /etc/resolv.conf with puppet.

* `resolvconf` : Main class.
* `resolvconf::file` : Definition to manage extra files.

The idea is to be able to globally include the `resolvconf` class on all nodes.
From there, hiera with puppet 3.x allows to start managing the
`/etc/resolv.conf` file's content in a very flexible way.

## Examples

In `site.pp` :
```puppet
hiera_include('classes')
```

In `hieradata` somewhere :
```yaml
---
classes:
  - '::resolvconf'
resolvconf::header: ''
resolvconf::nameserver:
  - '198.51.100.1'
  - '198.51.100.2'
resolvconf::search:
  - 'foo.example.com'
  - 'bar.example.com'
  - 'example.com'
```

This way, only nodes where the Hiera values apply get their `/etc/resolv.conf`
file modified by Puppet.

Ubuntu allows for file snippets to be used :
```puppet
resolvconf::file { '/etc/resolvconf/resolv.conf.d/tail':
  nameserver => [ '198.51.100.1', '198.51.100.2' ],
}
```

