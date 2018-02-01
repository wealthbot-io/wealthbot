# Changelog

## 2.1.4

- Upped supported Puppet versions to include Puppet 5

## 2.1.3

- Test and list compatibility with Puppet 4 and latest OSes
- Fix catalog building in spec helper under Puppet 4.6
- Various fixes to test suite and gemset

## 2.1.2

- Fix fixtures lib for RSpec 3
- Avoid shadowing variable
- Improve tags in README

## 2.1.1

- Fix metadata.json

## 2.1.0

- Add whichquote method

## 2.0.1

- Fix README.md

## 2.0.0

- Split core library from types and providers

## 1.2.0

- API
    * add next_seq method

- Providers
    * fix creation of multiple resources using seq entries under Puppet 3.4, fixes #101
    * pam: obey file target or service, fixes #105
    * pam: arguments default to [], fixes #100
    * pam: add “control_is_param” parameter to decide whether “control” is a resource identifier or property, fixes #114
    * sysctl: fix tests under latest Augeas

## 1.1.0

- General
    * add augeasproviders::instances class, fixes #78
    * add development doc
    * test actual versions of Augeas in Travis CI
    * improve errors when loading and saving files
    * reorganize unit tests
    * workaround Puppet 3.5 type loading bug, fixes #96

- API
    * share an Augeas handler on Puppet 3.4+ for performance
    * add parsed_as? method
    * add aug_version method
    * add supported? method
    * add rm_node to accessors

- Providers
    * apache_directive: new type/provider
    * pam: new type/provider
    * shellvar: add unset and exported values for 'ensure'
    * shellvar: add an 'uncomment' param
    * shellvar: add an 'array_append' parameter
    * sshd_config: support case_insensitive entries
    * sshd_config: ensure that Port is inserted before ListenAddress, fixes #68
    * sshd_config_subsystem: support case_insensitive entries
    * do not confine providers to the existence of target files


## 1.0.2
* no change, re-release for bad tarball checksum

## 1.0.1
* sysctl: fix quoting issue when applying settings, fixes #53 (Jeremy Kitchen)
* sysctl: fix apply=>false, was always running, fixes #56 (Trey Dockendorf)
* all: use augeas/lenses/ from Puppet's pluginsync libdir (Craig Dunn)
* sshd: create array entries before Match groups

## 1.0.0
* devel: AugeasProviders::Provider has gained a large number of helper methods
  for writing providers
* all: providers completely refactored to use AugeasProviders::Provider helpers
* sysctl: ignore whitespace inside values during comparisons, fixes #50
* shellvar: fix require to work for puppet apply/specs

## 0.7.0
* pg_hba: new type for managing PostgreSQL pg_hba.conf entries
* shellvar: add support for array values
* sysctl: add 'apply' parameter to change live kernel value (default: true)
* sysctl: add 'val' parameter alias for duritong/puppet-sysctl compatibility
* mailalias: fix quoting of pipe recipients, fixes #41
* devel: test Ruby 2.0

## 0.6.1
* syslog: add rsyslog provider variant, requires Augeas 1.0.0
* all: fix ruby-augeas 0.3.0 compatibility on Ruby 1.9
* all: don't throw error when target file doesn't already exist
* kernel_parameter/grub: ensure partially present parameters will be removed

## 0.6.0
* apache_setenv: new type for managing Apache HTTP SetEnv config options (Endre
  Karlson)
* puppet_auth: new type for managing Puppet's auth.conf file
* shellvar: new type for managing /etc/{default,sysconfig}
* kernel_parameter: use EFI GRUB legacy config if present
* devel: replaced librarian-puppet with puppetlabs_spec_helper's .fixtures.yml
* devel: use augparse --notypecheck for improved speed

## 0.5.3
* sshd_config: reinstate separate name parameter
* docs: add sshd_config multiple keys example, fixes #27

## 0.5.2
* sshd_config, sysctl: create entries after commented out entry
* host, mailalias: implement prefetch for performance
* sshd_config: remove separate name parameter, only use key as namevar
* docs: remove symlinks from docs/, fixes #25, improve README, rename LICENSE
* devel: improve idempotence logging
* devel: update to Augeas 1.0.0, test Puppet 3.1

## 0.5.1
* all: fix library loading issue with `puppet apply`

## 0.5.0
* kernel_parameter: new type for managing kernel arguments in GRUB Legacy and
  GRUB 2 configs
* docs: documentation index, existing articles and numerous examples for all
  providers added
* docs: URLs changed to GitHub hercules-team organisation
* devel: files existence stubbed out in tests
* devel: Augeas submodule changed to point to GitHub
* devel: specs compatibility with 2.7.20 fixed

## 0.4.0
* nrpe_command: new type for managing NRPE settings (Christian Kaenzig)
* syslog: new type for managing (r)syslog destinations (Raphaël Pinson)

## 0.3.1
* all: fix missing require causing load errors
* sshd_config: store multiple values for a setting as multiple entries, e.g.
  multiple ListenAddress lines (issue #13)
* docs: minor fixes
* devel: test Puppet 3.0

## 0.3.0
* sysctl: new type for managing sysctl.conf entries
* mounttab: add Solaris /etc/vfstab support
* mounttab: fix options property idempotency
* mounttab: fix key=value options in fstab instances
* host: fix comment and host_aliases properties idempotency
* all: log /augeas//error output when unable to save
* packaging: hard mount_providers dependency removed
* devel: augparse used to test providers against expected tree
* devel: augeas submodule included for testing against latest lenses

## 0.2.0
* mounttab: new provider for mounttab type in puppetlabs-mount_providers
  (supports fstab only, no vfstab), mount_providers now a dependency
* devel: librarian-puppet used to install Puppet module dependencies

## 0.1.1
* host: fix host_aliases param support pre-2.7
* sshd_config: find Match groups in instances/ralsh
* sshd_config: support arrays for ((Allow|Deny)(Groups|Users))|AcceptEnv|MACs
* sshd_config_subsystem: new type and provider (Raphaël Pinson)
* devel: use Travis CI, specify deps via Gemfile + bundler
* specs: fixes for 0.25 and 2.6 series

## 0.1.0
* host: fix pre-2.7 compatibility when without comment property
* sshd_config: new type and provider (Raphaël Pinson)
* all: fix provider confine to enable use in same run as ruby-augeas install
  (Puppet #14822)
* devel: refactor common augopen code into utility class
* specs: fix both Ruby 1.8 and mocha 0.12 compatibility

## 0.0.4
* host: fix handling of multiple host_aliases
* host: fix handling of empty comment string, now removes comment
* host: fix missing ensure and comment parameters in puppet resource, only
  return aliases if present
* mailalias: fix missing ensure parameter in puppet resource
* specs: added comprehensive test harness for both providers

## 0.0.3
* all: add instances methods to enable `puppet resource`

## 0.0.2
* mailalias: new provider added for builtin mailalias type

## 0.0.1
* host: new provider added for builtin host type
