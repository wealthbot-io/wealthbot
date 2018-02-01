# = Define: nodejs::instance::pkgs
#
# Ensures that all packages will be installed properly.
#
# == Parameters:
#
# [*make_install*]
#   Whether or not to install all compiler-related dependencies.
#
# == Example:
#
# class { '::nodejs::instance::pkgs': }
#
class nodejs::instance::pkgs($make_install = false) {
  if $caller_module_name != $module_name {
    warning('nodejs::instance::pkgs is private!')
  }

  ensure_packages(['tar', 'wget', 'ruby'])
  ensure_packages(['semver'], {
    provider => gem,
    require  => Package['ruby'],
  })

  if $make_install {
    include ::gcc
    ensure_packages(['make'])
  }
}
