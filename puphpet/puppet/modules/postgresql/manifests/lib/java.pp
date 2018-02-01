# This class installs the postgresql jdbc connector. See README.md for more
# details.
class postgresql::lib::java (
  String $package_name      = $postgresql::params::java_package_name,
  String[1] $package_ensure = 'present'
) inherits postgresql::params {

  package { 'postgresql-jdbc':
    ensure => $package_ensure,
    name   => $package_name,
    tag    => 'postgresql',
  }

}
