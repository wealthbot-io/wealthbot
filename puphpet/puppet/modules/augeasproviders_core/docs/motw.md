# Module of the Week: domcleal/augeasproviders - Use Augeas to modify config files

_The following blog post was written in November 2012, so may be out of date in
places.  Originally published
[here](http://puppetlabs.com/blog/module-of-the-week-domclealaugeasproviders/)._

* Purpose: A set of providers and types that use Augeas to modify config files
* Module: [domcleal/augeasproviders](http://forge.puppetlabs.com/domcleal/augeasproviders)
* Puppet Version: 0.25+
* Platforms: Any with ruby-augeas available (Linux, BSD, Solaris, AIX)

[Augeas](http://augeas.net) is a library and API for accessing and modifying text configuration files, with a [number of language bindings](http://www.augeas.net/download.html) and [over a hundred](http://git.fedorahosted.org/cgit/augeas.git/tree/lenses) common config formats supported.  It emphasises safety (not breaking files) and preservation of a file's existing layout and formatting.

The `augeasproviders` module offers providers for Puppet using the Augeas library for a few existing resource types (e.g. `host`) and adds a few types of its own (e.g. `sysctl`).  Once installed, the new providers can be selected and the new types are immediately available for use - no Augeas knowledge required!

Puppet also contains an ["augeas" resource type](http://docs.puppetlabs.com/references/stable/type.html#augeas) for interacting directly with the Augeas API, but it's complex to use even with [examples](http://projects.puppetlabs.com/projects/puppet/wiki/Puppet_Augeas#Working+Examples).  The difficulty comes from an API designed for imperative use, since this only works well if the caller can test, branch and loop - but this doesn't fit the declarative Puppet DSL.  The type doesn't support the user with this problem and instead forces them to know tricks to try and make the API appear declarative.

`augeasproviders` solves this by calling the Augeas API from a provider, which can be developed and tested once for a particular config file instead of making the Puppet user learn the API.  A provider can be far more intelligent than a list of API calls in a resource, since the provider can handle many possible scenarios while a resource might only be developed for one particular scenario or set of data.  Many test cases can be written for providers and types to ensure their reliability, while custom resources are likely to only be lightly tested.

The module is written by a couple of Augeas developers, with the aim of making the power of Augeas accessible and easy for Puppet users.


## Installing the module
* Complexity: Medium
* Installation Time: 5 minutes

Installing the `augeasproviders` module can be done with the Puppet module tool, available in Puppet 2.7.14+ and Puppet Enterprise 2.5+, and also available as a RubyGem:

    $ puppet module install domcleal-augeasproviders
    Preparing to install into /etc/puppet/modules ...
    Downloading from http://forge.puppetlabs.com ...
    Installing -- do not interrupt ...
    /etc/puppet/modules
    └── domcleal-augeasproviders (v0.4.0)

Alternatively, you can install the module manually:

    $ cd /etc/puppet/modules/
    $ wget <download URL from forge>
    $ tar zxvf domcleal-augeasproviders-*.tar.gz && rm domcleal-augeasproviders-*.tar.gz
    $ mv domcleal-augeasproviders-* augeasproviders

The ruby-augeas language bindings need to be installed on the client for the same Ruby instance used for the Puppet agent.  On Fedora/EL systems install `ruby-augeas` (EPEL, [yum.puppetlabs.com](http://yum.puppetlabs.com)) and on Debian/Ubuntu install `libaugeas-ruby`.  The wiki's [pre-reqs section](http://projects.puppetlabs.com/projects/puppet/wiki/Puppet_Augeas#Pre-requisites) has more detailed information.


## Resource overview

The module only consists of custom types and providers, stored under `lib/puppet/type/` and `lib/puppet/provider/` respectively inside the module.  As of version 0.4.0, these new types are:

* `nrpe_command` for setting command entries in Nagios NRPE's `nrpe.cfg`
* `sshd_config` for setting configuration entries in OpenSSH's `sshd_config`
* `sshd_config_subsystem` for setting subsystem entries in OpenSSH's `sshd_config`
* `sysctl` for entries inside Linux's `sysctl.conf`
* `syslog` for entries inside `syslog.conf`

And the following existing types have Augeas-based providers added:

* `host`
* `mailalias`
* `mounttab` from [puppetlabs-mount_providers](http://forge.puppetlabs.com/puppetlabs/mount_providers)


## Configuring the module
* Complexity: Easy
* Configuration Time: 1 minute

The new types need no configuration and can be used immediately.

For builtin types, change the provider on individual resources to `augeas`:

    host { "example.com":
      ensure   => present,
      ip       => "10.1.2.3",
      provider => "augeas",
    }

Or change the resource defaults globally or in a single scope:

    Host {
      provider => "augeas",
    }

This applies to the `host`, `mailalias` and `mounttab` ([puppetlabs-mount_providers](http://forge.puppetlabs.com/puppetlabs/mount_providers)) types.


## Example usage

Two of the types that `augeasproviders` currently extends are already in Puppet: [host](http://docs.puppetlabs.com/references/stable/type.html#host) and [mailalias](http://docs.puppetlabs.com/references/stable/type.html#mailalias).  They can be used in the same way just by specifying the provider, but with the benefit of not reformatting the file as the default "parsedfile" providers do.

    mailalias { "root":
      ensure    => present,
      recipient => "sysadmins@example.net",
      provider  => "augeas",
    }

This updates the "root" entry in `/etc/aliases` to forward mail to "sysadmins@example.net":

    $ grep root: /etc/aliases
    root:	sysadmins@example.net

The mounttab type from the [puppetlabs-mount_providers](http://forge.puppetlabs.com/puppetlabs/mount_providers) module manages (v)fstab entries:

    mounttab { "/example":
      ensure   => present,
      device   => "LABEL=/example",
      fstype   => "auto",
      options  => ["noatime", "uid=12345"],
      dump     => 0,
      pass     => 2,
      provider => "augeas",
    }

    $ grep example /etc/fstab
    LABEL=/example	/example	auto	noatime,uid=12345	0 2

Lastly, the number of new types in `augeasproviders` is growing and will continue to do so because of the number of config file formats that Augeas itself understands.  So far it has `sshd_config` and `sshd_config_subsystem` types for managing OpenSSH's `sshd_config`, `sysctl` for Linux's `sysctl.conf`, `nrpe_command` for Nagios NRPE command config and `syslog` for managing syslog and rsyslog entries.

The type descriptions are on the [module page](http://forge.puppetlabs.com/domcleal/augeasproviders) or can be found from `puppet doc -r type`.  Here's an example to manage an individual sysctl with a comment preceding it:

    sysctl { "kernel.sysrq":
      ensure  => present,
      value   => "1",
      comment => "Enable for debugging from system console",
    }

    # kernel.sysrq: Enable for debugging from system console
    kernel.sysrq = 1


## Conclusion

`augeasproviders` significantly simplifies using Augeas from Puppet if a provider or type exists for the type of file you need.  It makes partial file editing reliable with [well-tested](http://m0dlx.com/blog/Testing_techniques_for_Puppet_providers_using_Augeas.html) building blocks and returns your Puppet resources to being high-level and declarative.

The number of types and providers is slowly growing, but please file issues [on GitHub](http://github.com/hercules-team/augeasproviders/issues) if there's a target file type that you think would be particularly useful, or if you have problems with the module.  If you'd like to contribute then pull requests are gratefully received, and the testing framework should make developing a provider quite straightforward.

Some of the other new types planned are `kernel_parameter` to manage Linux kernel parameters in GRUB configs, a variety of INI file types, a `shellvars` type (i.e. `/etc/{default,sysconfig}/*`) and `ssh_authorized_keys`.


## Learn More:

* [domcleal-augeasproviders](http://forge.puppetlabs.com/domcleal/augeasproviders)
* [GitHub: augeasproviders](https://github.com/hercules-team/augeasproviders)
