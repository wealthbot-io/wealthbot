## 2017-03-10 - Release 2.0.0

### Summary

This release no longer allows the `type` parameter to be empty when creating the resource. It also includes an important fix to correctly detect if a defined password needs to be updated.

#### Features

- Added Ubuntu 16.10 (Yakkety Yak) to the list of supported operating systems.

#### Bugfixes

- Added an additional validation for the `type` parameter. This effectively makes the parameter mandatory for `ensure => present`. The type of the entry is required when the entry is missing in the debconf database and has to be created.
- Fix a bug that prevented reading a preseeded password correctly. Previously a password item would trigger a resource update with every Puppet run.

## 2016-05-13 - Release 1.0.0

### Summary

Initial release.
