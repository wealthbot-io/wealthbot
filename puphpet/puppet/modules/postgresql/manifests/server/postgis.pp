# Install the postgis postgresql packaging. See README.md for more details.
class postgresql::server::postgis (
  String $package_name      = $postgresql::params::postgis_package_name,
  String[1] $package_ensure = 'present'
) inherits postgresql::params {

  package { 'postgresql-postgis':
    ensure => $package_ensure,
    name   => $package_name,
    tag    => 'postgresql',
  }

  anchor { 'postgresql::server::postgis::start': }
  -> Class['postgresql::server::install']
  -> Package['postgresql-postgis']
  -> Class['postgresql::server::service']
  -> anchor { 'postgresql::server::postgis::end': }

  if $postgresql::globals::manage_package_repo {
    Class['postgresql::repo']
    -> Package['postgresql-postgis']
  }
}
