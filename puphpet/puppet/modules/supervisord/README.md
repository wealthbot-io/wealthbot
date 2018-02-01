# Puppet Supervisord

[![Puppet Forge](http://img.shields.io/puppetforge/v/ajcrowe/supervisord.svg)](https://forge.puppetlabs.com/ajcrowe/supervisord)
[![Build Status](https://travis-ci.org/ajcrowe/puppet-supervisord.png?branch=master)](https://travis-ci.org/ajcrowe/puppet-supervisord)

Puppet module to manage the [supervisord](http://supervisord.org/) process control system.

Functions available to configure

* [programs](http://supervisord.org/configuration.html#program-x-section-settings)
* [groups](http://supervisord.org/configuration.html#group-x-section-settings)
* [fcgi-programs](http://supervisord.org/configuration.html#fcgi-program-x-section-settings)
* [eventlisteners](http://supervisord.org/configuration.html#eventlistener-x-section-settings)
* [rpcinterface](http://supervisord.org/configuration.html#rpcinterface-x-section-settings)

## Deprecation warning

To avoid conflict with puppet master's $environment variable, the *environment* parameter of supervisord::program resource is being renamed *program_environment* and old name will be removed in future version.

#### Examples

## Examples

### Configuring supervisord with defaults

Install supervisord with pip and install an init script if available

```puppet
include ::supervisord
```

### Install supervisord and pip

Install supervisord and install pip if not available.

```puppet
class { 'supervisord':
  install_pip => true,
}
```

This will download [setuptool](https://bitbucket.org/pypa/setuptools) and install pip with easy_install.

You can pass a specific url with `$setuptools_url = 'url'`

### Install without pip

If you want to use your system package manager you can specify that with `supervisord::package_provider`.

You'll also likely need to adjust the `supervisord::service_name` to match that installed by the system package. If you're using Debian, Redhat or Suse OS families you'll also want to disable the init scripts with `supervisord::install_init = false`.

#### Custom Init Script

Only Debian, RedHat and Suse families have an init script included currently. But you can provide custom scripts like this:

```puppet
class { 'supervisord':
  install_init         => true,
  init_script          => '/path/to/init_file',
  init_script_template => 'mymodule/template/init.erb',
  init_defaults        => false
}
```

### HTTP servers

As of version 3.0a3, Supervisor provides an HTTP server that can listen on a Unix socket, an inet socket, or both.  By default, this module enables the Unix socket HTTP server.  `supervisorctl` issues commands to the HTTP server, and it must be configured to talk to either the Unix socket or the inet socket.  If only one HTTP server is enabled, this module will configure `supervisorctl` to use that HTTP server.  If both HTTP servers are enabled, the Unix socket HTTP server will be used by default.  To use the inet socket instead, set `ctl_socket` to `inet` (its default is `unix`).
modified
#### Configure the Unix HTTP server

The Unix HTTP server is enabled by default.  Its parameters are:

```puppet
class { 'supervisord':
  unix_socket       => true,
  run_path          => '/var/run',
  unix_socket_mode  => '0700',
  unix_socket_owner => 'nobody',
  unix_socket_group => 'nobody',
  unix_auth         => false,
  unix_username     => undef,
  unix_password     => undef,
}
```

This results in the following config sections:

```
[unix_http_server]
file=/var/run/supervisor.sock
chmod=0700
chown=nobody:nobody

[supervisorctl]
serverurl=unix:///var/run/supervisor.sock
```

#### Configure the Inet HTTP server

The Inet HTTP server is disabled by default.  Its parameters are:

```puppet
class { 'supervisord':
  unix_socket          => false,
  inet_server          => true,
  inet_server_hostname => '127.0.0.1',
  inet_server_port     => '9001',
  inet_auth            => false,
  inet_username        => undef,
  inet_password        => undef,
}
```

This results in the following config sections:

```
[inet_http_server]
port=127.0.0.1:9001

[supervisorctl]
serverurl=http://127.0.0.1:9001
```

### Override sysconfig template

If `supervisord::install_init` is true (the default), then an init script will be installed, and that script will source the contents of the `templates/init/${::osfamily}/defaults.erb` file.  If you want to override that template, you can set `supervisord::init_template` to the path of an alternative template:

```puppet
class { 'supervisord':
  install_pip   => true,
  init_template => 'my/supervisord/${::osfamily}/defaults.erb'
}
```

You almost certainly want to copy and add to the original templates, as they contain important settings.

### Configure a program

```puppet
supervisord::program { 'myprogram':
  command             => 'command --args',
  priority            => '100',
  program_environment => {
    'HOME'   => '/home/myuser',
    'PATH'   => '/bin:/sbin:/usr/bin:/usr/sbin',
    'SECRET' => 'mysecret'
  }
}
```

You may also specify a variable for a hiera lookup to retreive your environment hash. This allows you to reuse existing environment variable hashes.

```puppet
supervisord::program { 'myprogram':
  command  => 'command --args',
  priority => '100',
  env_var  => 'my_common_envs'
}
```

Or you can fully define your programs in hiera:

```yaml
supervisord::programs:
  'myprogram':
    command: 'command --args'
    autostart: yes
    autorestart: 'true'
    program_environment:
      HOME: '/home/myuser'
      PATH: '/bin:/sbin:/usr/bin:/usr/sbin'
      SECRET: 'mysecret'
```

### Configure a group

```puppet
supervisord::group { 'mygroup':
  priority => 100,
  programs => ['program1', 'program2', 'program3']
}
```

### Configure a ctlplugin

```puppet
supervisord::ctlplugin { 'laforge':
  ctl_factory => 'mr.laforge.controllerplugin:make_laforge_controllerplugin'
}
```

### Configure an eventlistener

```puppet
supervisord::eventlistener { 'mylistener':
  command  => 'command --args',
  events   => ['PROCESS_STATE', 'PROCESS_STATE_START']
  priority => '100',
  env_var  => 'my_common_envs'
}
```

### Configure an rpcinterface

```puppet
supervisord::rpcinterface { 'laforge':
  rpcinterface_factory => 'mr.laforge.rpcinterface:make_laforge_rpcinterface'
}
```

### Run supervisorctl Commands

Should you need to run a sequence of command with `supervisorctl` you can use the define type `supervisord::supervisorctl`

```puppet
supervisord::supervisorctl { 'restart_myapp':
  command => 'restart',
  process => 'myapp'
}
```

You can also issue a command without specifying a process.

### Development

If you have suggestions or improvements please file an issue or pull request, i'll try and sort them as quickly as possble.

If you submit a pull please try and include tests for the new functionality/fix. The module is tested with [Travis-CI](https://travis-ci.org/ajcrowe/puppet-supervisord).


### Credits

* Debian init script sourced from the system package.
* RedHat/Centos init script sourced from https://github.com/Supervisor/initscripts
* Suse init script modified from RedHat/Centos script
