# Class: gcc
#
# This class installs gcc
#
# Parameters:
#
# Actions:
#   - Install the gcc package
#
# Requires:
#
# Sample Usage:
#
class gcc(
  $gcc_packages = $gcc::params::gcc_packages,
) inherits gcc::params {
  ensure_packages($gcc_packages)
}
