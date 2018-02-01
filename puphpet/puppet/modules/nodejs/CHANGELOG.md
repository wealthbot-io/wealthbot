# 2.0.0

## Version/Requirement changes

- dropped support for Node.js versions until `v0.12.0`
- dropped support for all Puppet versions below `v3.4`
- dropped support for all ruby versions below `v2.1`

## Code changes

### Minor changes

- removed the `::nodejs_latest_version` and `::nodejs_stable_version` fact and replaced them with a puppet function to avoid evaluations on each node

- removed `with_npm` parameter (only used for Node.js 0.6 and below)

- fixed bug [#94](https://github.com/willdurand/puppet-nodejs/issues/94)

- added `cpu_cores` option to speedup the compilation process

- changed all downloads from `http` to `https`

- remove installation of `git` package.

- added support for ARM architecture (`armv6l` and `armv7l`).

- added `download_timeout` parameter to simplify configuration of package download timeouts.

- dropped `profile.d` script to patch NodeJS paths. Target directory will be set with `nodejs::$target_dir` which should be in `$PATH`.
  See [bcfdda3341aa8b0d885b40e9a6ab7f90859f9f3e](https://github.com/willdurand/puppet-nodejs/commit/bcfdda3341aa8b0d885b40e9a6ab7f90859f9f3e) and [#177](https://github.com/willdurand/puppet-nodejs/issues/177) for further reference.

### Installer Refactoring

- added `puppetlabs-gcc` for package handling of the compiler (and removed custom implementation)
- killed the `python_package` option (not needed anymore)
- `nodejs::install` has been replaced by an internal API. to use multiple instances, use the `instances` and `instances_to_remove` option of the `nodejs` class (see the docs for more details)
- Introduced a new `build_deps` parameter which makes the entire package setup optional (see `willdurand/composer#44`).

### Version refactoring

The whole version detection logic was quite outdated and needed a refactoring:

- removed the `stable` flag for versions. The behavior of `latest` was equal.
- introduced the `lts` flag to fetch the latest LTS release of Node.js.
- generic versions:
  - `7.x` to fetch the latest release of the Node.js v7 branch.
  - `7.0` to fetch the latest `7.0.x` release.

### `nodejs::npm` refactoring

The `nodejs::npm` resource has been refactored in order to keep the logic inside maintainable.

The following breaking changes were made:

- Removed `install_opt` and `remove_opt` and replaced it with a single `options` parameter.
- Renamed `exec_as_user` to `exec_user` as it's describes the intent of the parameter in a better way.
- Dropped automatic generation of a home directory for the `npm` calls and added a `home_dir` parameter which does the job.
- Removed the ability to write `dir:pkg` as resource title.
- The `pkg_name` has now `$title` as default parameter.
