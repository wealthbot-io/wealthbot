# Change Log

All notable changes to this project will be documented in this file. This project adheres to [Semantic Versioning](http://semver.org/).

## Supported Release [2.2.0]

### Summary
This is to enable Rubocop within the module.

### Added
- Rubocop has been enabled and will now run against all future pull requests.

### Fixed
- Entire module has been put through the rubocop process and now matches the expected standards.

## Supported Release [2.1.0]

### Summary
This is to provide a clean release from which to run Rebucop

### Added
- Debian 9 as supported platform

### Fixed
- CVS working copy detection ([MODULES-5704](https://tickets.puppet.com/browse/MODULES-5704))
- typo error for sshed-git-source
- Update to existence test, Use 'svn info' instead of 'svn status'. 'svn status' does not return proper exit codes, while 'svn info' does.
- working_copy_exists in svn provider. Change method 'working_copy_exists' to use 'svn info' instead of 'svn status'. 'svn status' does not return proper exit codes, while 'svn info' does. ([MODULES-5615](https://tickets.puppet.com/browse/MODULES-5615))
- tests associated with hg
- hg provider: remove escaped quotes - authentication fix

### Removed
- Support for Ubuntu 10.04 and 12.04, existing compatibility is unaffected ([MODULES-5501](https://tickets.puppet.com/browse/MODULES-5501))
- Support for Debian 6 and SLES 10, existing compatibility is unaffected
- Commented out test ([MODULES-5162](https://tickets.puppet.com/browse/MODULES-5162))

## Supported Release [2.0.0]

### Summary
This is a major release that **drops support for Puppet 3** and addresses an issue with the subversion provider.

### Added
- Documentation for using an non-standard ssh port ([MODULES-1910](https://tickets.puppet.com/browse/MODULES-1910))
- Autorequire for the subversion package in the vcsrepo type ([MODULES-4722](https://tickets.puppetlabs.com/browse/MODULES-4722))
- Puppet 5 support in metadata ([MODULES-5144](https://tickets.puppet.com/browse/MODULES-5144))

### Changed
- Lower bound of Puppet requirement to 4.7.0 ([MODULES-4823](https://tickets.puppetlabs.com/browse/MODULES-4823))

### Fixed
- Solaris `SSH_AUTH_SOCKET` issue 
- Issue with subversion provider ([MODULES-4280](https://tickets.puppetlabs.com/browse/MODULES-4280))
- `force` parameter to return a boolean instead of a string ([MODULES-4864](https://tickets.puppetlabs.com/browse/MODULES-4864))

## [1.5.0] - 2016-12-19 Supported Release

### Summary
Release featuring some refactoring and improvements around git's `ensurable`.

### Bugfixes
- `ensure => absent` fix

### Features
- `:source` property added
- Improved `ensure` handling for git provider
- General refactoring for all providers
- Various test improvements

## [1.4.0] - 2015-09-06 Supported Release

### Summary

Small release for a new feature and added compatibility.

### Features

- Git repositories can be cloned as mirror or bare repos.
- Added STDERR to Puppet's output.
- Added Debian 8 and Ubuntu 16.04 compatibility.

## [1.3.2] - 2015-12-08 Supported Release

### Summary

Small release for support of newer PE versions. This increments the version of PE in the metadata.json file.

## [1.3.1] - 2015-07-28 Supported Release

### Summary

This release includes a number of bugfixes and test updates.

### Fixed

- Fix for detached HEAD on git 2.4+.
- Git provider doesn't ignore revision property when depth is used (MODULES-2131).
- Tests fixed.
- Check if submodules == true before calling update_submodules.

## [1.3.0] - 2015-05-19 Supported Release

### Summary

This release adds git provider remote handling, svn conflict resolution, and fixes the git provider when /tmp is mounted noexec.

### Added

- `source` property now takes a hash of sources for the git provider's remotes.
- Added `submodules` parameter to skip submodule initialization for the git provider.
- Added `conflict` to the svn provider to resolve conflicts.
- Added `branch` parameter to specify clone branch.
- Readme rewritten.

### Fixed

- The git provider now works even if `/tmp` is noexec.

## [1.2.0] - 2014-11-04 Supported Release

### Summary

This release includes some improvements for git, mercurial, and cvs providers, and fixes the bug where there were warnings about multiple default providers.

### Added

- Update git and mercurial providers to set UID with `Puppet::Util::Execution.execute` instead of `su`
- Allow git excludes to be string or array
- Add `user` feature to cvs provider

### Fixed

- No more warnings about multiple default providers! (MODULES-428)

## [1.1.0] - 2014-07-14 Supported Release

### Summary

This release adds a Perforce provider\* and corrects the git provider behavior
when using `ensure => latest`.

\*(Only git provider is currently supported.)

### Added

- New Perforce provider.

### Fixed

- Fix behavior with `ensure => latest` and detached HEAD. (MODULES-660)
- Spec test fixes.

## [1.0.2] - 2014-06-30 Supported Release

### Summary

This supported release adds SLES 11 to the list of compatible OSs and
documentation updates for support.

## [1.0.1] - 2014-06-17 Supported Release

### Summary

This release is the first supported release of vcsrepo. The readme has been
greatly improved.

### Added

- Updated and expanded readme to follow readme template.

### Fixed

- Remove SLES from compatability metadata.
- Unpin rspec development dependencies.
- Update acceptance level testing.

## [1.0.0] - 2014-06-04

### Summary

This release focuses on a number of bugfixes, and also has some
new features for Bzr and Git.

### Added

- Bzr:
 - Call set_ownership.
- Git:
 - Add ability for shallow clones.
 - Use -a and desired for HARD resets.
 - Use rev-parse to get tag canonical revision.

### Fixed

- HG:
 - Only add ssh options when it's talking to the network.
- Git:
 - Fix for issue with detached HEAD.
 - `force => true` will now destroy and recreate repo.
 - Actually use the remote parameter.
 - Use origin/master instead of origin/HEAD when on master.
- SVN:
 - Fix svnlook behavior with plain directories.

## 0.2.0 - 2013-11-13

### Summary

This release mainly focuses on a number of bugfixes, which should
significantly improve the reliability of Git and SVN.  Thanks to
our many contributors for all of these fixes!

### Added

- Git:
 - Add autorequire for `Package['git']`.
- HG:
 - Allow user and identity properties.
- Bzr:
 - "ensure => latest" support.
- SVN:
 - Added configuration parameter.
 - Add support for master svn repositories.
- CVS:
 - Allow for setting the CVS_RSH environment variable.

### Fixed

- Handle Puppet::Util[::Execution].withenv for 2.x and 3.x properly.
- Change path_empty? to not do full directory listing.
- Overhaul spec tests to work with rspec2.
- Git:
 - Improve Git SSH usage documentation.
 - Add ssh session timeouts to prevent network issues from blocking runs.
 - Fix git provider checkout of a remote ref on an existing repo.
 - Allow unlimited submodules (thanks to --recursive).
 - Use git checkout --force instead of short -f everywhere.
 - Update git provider to handle checking out into an existing (empty) dir.
- SVN:
 - Handle force property.
 - Adds support for changing upstream repo url.
 - Check that the URL of the WC matches the URL from the manifest.
 - Changed from using "update" to "switch".
 - Handle revision update without source switch.
 - Fix svn provider to look for '^Revision:' instead of '^Last Changed Rev:'.
- CVS:
 - Documented the "module" attribute.

[2.1.0]: https://github.com/puppetlabs/puppetlabs-vcsrepo/compare/2.0.0...2.1.0
[2.0.0]: https://github.com/puppetlabs/puppetlabs-vcsrepo/compare/1.5.0...2.0.0
[1.5.0]: https://github.com/puppetlabs/puppetlabs-vcsrepo/compare/1.4.0...1.5.0
[1.4.0]: https://github.com/puppetlabs/puppetlabs-vcsrepo/compare/1.3.2...1.4.0
[1.3.2]: https://github.com/puppetlabs/puppetlabs-vcsrepo/compare/1.3.1...1.3.2
[1.3.1]: https://github.com/puppetlabs/puppetlabs-vcsrepo/compare/1.3.0...1.3.1
[1.3.0]: https://github.com/puppetlabs/puppetlabs-vcsrepo/compare/1.2.0...1.3.0
[1.2.0]: https://github.com/puppetlabs/puppetlabs-vcsrepo/compare/1.1.0...1.2.0
[1.1.0]: https://github.com/puppetlabs/puppetlabs-vcsrepo/compare/1.0.2...1.1.0
[1.0.2]: https://github.com/puppetlabs/puppetlabs-vcsrepo/compare/1.0.1...1.0.2
[1.0.1]: https://github.com/puppetlabs/puppetlabs-vcsrepo/compare/1.0.0...1.0.1
[1.0.0]: https://github.com/puppetlabs/puppetlabs-vcsrepo/compare/0.2.0...1.0.0
