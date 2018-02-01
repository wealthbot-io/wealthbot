## [0.3.0] - 2015-04-07
###Backwards-incompatible changes
- `gcc_package` parameter in `class gcc` was renamed to `gcc_packages`

###Features
- Move to metadata.json instead of Modulefile
- Move to using `ensure_packages` for the array of `gcc_packages`

## [0.2.0] - 2014-06-03
###Features
- Add g++ for RHEL.
- Fail on unsupported distributions.

## [0.1.0] - 2013-08-14 
###Features
- Support `$osfamily` instead of using `$operatingsystem`. Note that
Amazon Linux is RedHat osfamily on facter version 1.7

## 0.0.3 - 2011-06-03
###Features
- committed source to git
- added tests

[0.1.0]: https://github.com/puppetlabs/puppetlabs-gcc/compare/0.0.3...0.1.0
[0.2.0]: https://github.com/puppetlabs/puppetlabs-gcc/compare/0.1.0...0.2.0
[0.3.0]: https://github.com/puppetlabs/puppetlabs-gcc/compare/0.2.0...0.3.0
