## 1.0.0 (2017-06-30)
### Summary
Major release removing Puppet 3 support.

#### Added
- `ssl_weak_cert` param in `mongodb::server` type
- `system_logrotate` param in `mongodb`, `mongodb::server`, and `mongodb::server::config`
- `handle_creds` to handle credentials outside of Puppet ([MODULES-1754](https://tickets.puppet.com/browse/MODULES-1754))
- `sslMode` when SSL is used
- ability to use unencrypted passwords

#### Changed
- **lower bound of Puppet requirement to 4.7.0**
- use of `sslMode` to `sslOnNormalPorts` to determine if SSL is enabled

#### Fixed
- `database` property in `mongodb_user` to allow hyphens ([MODULES-4444](https://tickets.puppet.com/browse/MODULES-4444))
- gsub pattern for `is_master` fact
- `mongodb_version` fact
- selinux dbpath contexts in `mongodb::server::config`
- Debian-based repo paths
- `$pidfilepath` for Debian
- syntax error in net.ipv6 configuration option
- spec failure caused by Puppet 5
- is_master in mongodb provider

## Unsupported Release [0.17.0]
### Summary
Adding features to improve spec testing, and added ability to manage pidfile creation

#### Fixed
- gettext and spec.opts
- msync Gemfile for 1.0 frozen strings ([MODULES-3631](https://tickets.puppet.com/browse/MODULES-3631))
- MongoDB 3.12 creates pid file and checks in init script ([MODULES-3956](https://tickets.puppet.com/browse/MODULES-3956))
- gemfile template to be identical ([MODULES-3704](https://tickets.puppet.com/browse/MODULES-3704))
- deprecation errors

## Unsupported Release [0.16.0]
### Summary
We fixed a critical bug where we lost idempotency in 0.15.0. The patch that fix
this problem will be part of this release.

#### Fixed
- Recursively manage only user/group for dbpath

## Unsupported Release [0.15.0]
### Summary
The addition of several new functional features which will help with management and multiple bug fixes.

#### Added
- Added ability to set PID file mode.
- Recursively manage the contents of dbpath directory.
- Now alllows custom templates.
- Addition of mongo listen port before creating facter.

#### Fixed
- Now allows hyphens in database names.
- Now converts MongoDB ObjectID objects to generic JSON.
- Use the same regex that the mongodb provider does when correcting for ObjectID values in the isMaster response.
- Fixes to ensure that the auth property for config is parsed correctly.
- Now checks if mongo is up before evaluating is_master fact.

## Unsupported Release [0.14.0]
### Summary
This breaking release increases the lower bound of the puppetlabs-apt dependency to the 2.x series of apt and puppetlabs-stdlib to >= 4.4.0. The operating system metadata is also updated to reflect modern systems.

#### Added
- Add `mongodb_is_master` fact
- Add `mongodb::db::db_name` parameter for exported resource deduplication
- Add Debian 8 compatibility
- Add Ubuntu 14.04 compatibility
- Add Ubuntu 16.04 compatibility
- Add puppet 3.x 4.x compatibility metadata

#### Changed
- Increase apt lower dependency to >= 2.1.0
- Increase stdlib lower dependency to >= 4.4.0
- Drop RHEL & Centos 5
- Drop Debian 6
- Drop Ubuntu 10.04

#### Fixed
- Catch unconfigured replset configuration queries
- Fix timestamp and other javascript object removal
- Correct permissions on .mongorc.js to 600

## Unsupported Release [0.13.0]
### Summary
Adds several new large features, including the support of mongodb 3.x. Also applies numerous bugfixes, mainly around fixing errors being thrown and syntax issues.

#### Added
- Adds mongodb_version fact.
- Add mongodb 3.x.
- Update to current msync configs.
- Now ensures that the pidfile exists and is writable.
- Simplified configuration parsing.
- Made argument handling more extensible.
- Added SSL support.
- Made ssl_ca optional when using SSL.
- Added $maxconns to mongodb::server::config.
- Added Suse to operating systems.

#### Fixed
- Removes empty lines between doc and definition.
- Fix when using admin params : catalog: Found 1 dependency cycle: issue.
- Some syntax error fixes.
- Cleaned up provider formatting.
- Parse NumberLong data type from mongodb outputs to generate valid json.
- Checks if $version is defined before versioncmp.
- Fixed deprecation warning for use of configtimeout.

## Unsupported Release [0.12.0]
### Summary
There are a number of bugfixes and features added in this release including, mongo db 3 engine support, ipv6 support and repo and yum improvements.

#### Added
- Distinguish between repo and package mgmt
- Immplement retries for MongoDB shell commands
- Initiate replica set creation from localhost if auth is enabled
- Added specific service provider for Debian
- mongo db 3 engine selection support
- added an option to set a custom repository location
- Improve support for MongoDB authentication and replicaset
- Add yum proxy options
- Enable IPv6 in mongodb provider

#### Fixed
- Fix mongodb_user username => name
- ensure that the client install does not start before the repo setup
- Fix replset not working on mongo 3.x
- Prealloc setting needs to be negated
- Add mongoDB >=3.x new yum repo location
- Add pidfilepath to globals when used in params
- Normalize spacing in template
- Switch to comparing current roles value with @property
- Fix versioncmp when version is undef
- Do not add blank parameter in ipv4
- Apply module sync

## Unsupported Release [0.11.0]
### Summary

#### Added
- arbiter support to to `mongodb_replset`
- `mongod_service_manage`, `mongos_service_manage`, and `ipv6` to `mongodb::globals`
- `service_manage`, `unitxsocketprefix`, `pidfilepath`, `logpath`, `fork`, `bind_ip`, `port`, and `restart` to `mongodb::mongos` class
- `key`, `ipv6`, `service_manage`, and `restart` to `mongodb::server` class
- Allow mongodb\_conn\_validator to take an array of nodes via composite namevar

#### Fixed
- Update to long apt repo key and bump compatibility to include apt 2
- Fix `nohttpinterface` on >= 2.6
- Fix connection validation when bind\_ip is 0.0.0.0
- Fix mongodb\_conn\_validator to use default port in shard mode

## Unsupported Release [0.10.0]
### Summary

This release adds a number of significant features and several bug fixes.

#### Added
- Adds support for sharding
- Adds support for RHEL 7
- Adds rudimentary support for SSL configuration
- Adds support for the enterprise repository

#### Fixed
- Fixes support for running on non-default ports
- Fixes the idempotency of password setting (for mongo 2.6)

## Unsupported Release [0.9.0]
### Summary

This release has a number of new parameters, support for 2.6, improved providers, and several bugfixes.

#### Added
- New parameters: `mongodb::globals`
  - `$service_ensure`
  - `$service_enable`
- New parameters: `mongodb`
  - `$quiet`
- New parameters: `mongodb::server`
  - `$service_ensure`
  - `$service_enable`
  - `$quiet`
  - `$config_content`
- Support for mongodb 2.6
- Reimplement `mongodb_user` and `mongodb_database` provider
- Added `mongodb_conn_validator` type

#### Fixed
- Use hkp for the apt keyserver
- Fix mongodb database existence check
- Fix `$server_package_name` problem (MODULES-690)
- Make sure `pidfilepath` doesn't have any spaces
- Providers need the client command before they can work (MODULES-1285)

## Unsupported Release [0.8.0]
### Summary

This feature features a rewritten mongodb_replset{} provider, includes several
important bugfixes, ruby 1.8 support, and two new features.

#### Added
- Rewritten mongodb_replset{}, featuring puppet resource support, prefetching,
and flushing.
- Add Ruby 1.8 compatibility.
- Adds `syslog`, allowing you to configure mongodb to send all logging to the hosts syslog.
- Add mongodb::replset, a wrapper class for hiera users.
- Improved testing!

#### Fixed
- Fixes the package names to work since 10gen renamed them again.
- Fix provider name in the README.
- Disallow `nojournal` and `journal` to be set at the same time.
- Changed - to = for versioned install on Ubuntu.

## Unsupported Release [0.7.0]
### Summary

Added Replica Set Type and Provider

## Unsupported Release [0.6.0]
### Summary

Added support for installing MongoDB client on 
RHEL family systems.

## Unsupported Release [0.5.0]
### Summary

Added types for providers for Mongo users and databases.

## Unsupported Release [0.4.0]

Major refactoring of the MongoDB module. Includes a new 'mongodb::globals' 
that consolidates many shared parameters into one location. This is an 
API-breaking release in anticipation of a 1.0 release.

## Unsupported Release [0.3.0]
### Summary

Adds a number of parameters and fixes some platform
specific bugs in module deployment.

## Unsupported Release [0.2.0]
### Summary

This release fixes a duplicate parameter.

#### Fixed
- Fix a duplicated parameter.

## Unsupported Release [0.1.0]
- Add support for RHEL/CentOS
- Change default mongodb install location to OS repo

## Unsupported Release [0.0.2]
- Fix Modulefile typo.
- Remove repo pin.
- Update spec tests and add travis support.

## Unsupported Release [0.0.1]
- Initial Release.

[1.0.0]: https://github.com/puppetlabs/puppetlabs-mongodb/compare/0.17.0...1.0.0
[0.17.0]: https://github.com/puppetlabs/puppetlabs-mongodb/compare/0.16.0...0.17.0
[0.16.0]: https://github.com/puppetlabs/puppetlabs-mongodb/compare/0.15.0...0.16.0
[0.15.0]: https://github.com/puppetlabs/puppetlabs-mongodb/compare/0.14.0...0.15.0
[0.14.0]: https://github.com/puppetlabs/puppetlabs-mongodb/compare/0.13.0...0.14.0
[0.13.0]: https://github.com/puppetlabs/puppetlabs-mongodb/compare/0.12.0...0.13.0
[0.12.0]: https://github.com/puppetlabs/puppetlabs-mongodb/compare/0.11.0...0.12.0
[0.11.0]: https://github.com/puppetlabs/puppetlabs-mongodb/compare/0.10.0...0.11.0
[0.10.0]: https://github.com/puppetlabs/puppetlabs-mongodb/compare/0.9.0...0.10.0
[0.9.0]: https://github.com/puppetlabs/puppetlabs-mongodb/compare/0.8.0...0.9.0
[0.8.0]: https://github.com/puppetlabs/puppetlabs-mongodb/compare/0.7.0...0.8.0
[0.7.0]: https://github.com/puppetlabs/puppetlabs-mongodb/compare/0.6.0...0.7.0
[0.6.0]: https://github.com/puppetlabs/puppetlabs-mongodb/compare/0.5.0...0.6.0
[0.5.0]: https://github.com/puppetlabs/puppetlabs-mongodb/compare/0.4.0...0.5.0
[0.4.0]: https://github.com/puppetlabs/puppetlabs-mongodb/compare/0.3.0...0.4.0
[0.3.0]: https://github.com/puppetlabs/puppetlabs-mongodb/compare/0.2.0...0.3.0
[0.2.0]: https://github.com/puppetlabs/puppetlabs-mongodb/compare/0.1.0...0.2.0
[0.1.0]: https://github.com/puppetlabs/puppetlabs-mongodb/compare/0.0.2...0.1.0
[0.0.2]: https://github.com/puppetlabs/puppetlabs-mongodb/compare/0.0.1...0.0.2
[0.0.1]: https://github.com/puppetlabs/puppetlabs-mongodb/tree/0.0.1
