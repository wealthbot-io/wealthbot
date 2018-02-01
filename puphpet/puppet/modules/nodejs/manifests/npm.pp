# = Define: nodejs::npm
#
# Define to install packages in a certain directory.
#
# == Parameters:
#
# [*ensure*]
#   Whether to install or uninstall the package.
#
# [*version*]
#   The specific version of the package to install (optional).
#
# [*options*]
#   Additional NPM options.
#
# [*exec_user*]
#   User which should execute the command (optional).
#
# [*list*]
#   Whehter to apply a package.json or installing a custom package (default: false).
#
# [*directory*]
#   Target directory.
#
# [*pkg_name*]
#   Package name.
#
# [*home_dir*]
#   The home directory of the executing user.
#
# == Example:
#
# Single package:
#
#   ::nodejs::npm { 'webpack-directory':
#     ensure    => present,
#     version   => 'x.x',
#     pkg_name  => 'webpack',
#     directory => '/directory',
#   }
#
# From package.json:
#
#   ::nodejs::npm { 'directory-npm-install':
#     list      => true,
#     directory => '/directory',
#   }
#
define nodejs::npm (
  $pkg_name  = $title,
  $ensure    = present,
  $version   = undef,
  $exec_user = undef,
  $list      = false,
  $directory = undef,
  $options   = undef,
  $home_dir  = '/root',
) {
  validate_string($version)
  validate_string($exec_user)
  validate_bool($list)
  validate_string($directory)
  validate_string($pkg_name)
  validate_string($options)
  validate_string($home_dir)

  include ::nodejs

  $exec_env = "HOME=${home_dir}"
  if $list {
    ::nodejs::npm::file { "npm-install-dir-${directory}":
      directory => $directory,
      exec_user => $exec_user,
      exec_env  => $exec_env,
      options   => $options,
    }
  } else {
    ::nodejs::npm::package { "npm-install-${pkg_name}-${directory}":
      ensure    => $ensure,
      exec_user => $exec_user,
      npm_dir   => $directory,
      npm_pkg   => $pkg_name,
      version   => $version,
      exec_env  => $exec_env,
      options   => $options,
    }
  }
}
