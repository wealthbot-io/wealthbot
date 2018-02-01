# Install client cli tool. See README.md for more details.
class postgresql::client (
  Enum['file', 'absent'] $file_ensure        = 'file',
  Stdlib::Absolutepath $validcon_script_path = $postgresql::params::validcon_script_path,
  String[1] $package_name                    = $postgresql::params::client_package_name,
  String[1] $package_ensure                  = 'present'
) inherits postgresql::params {

  if $package_name != 'UNSET' {
    package { 'postgresql-client':
      ensure => $package_ensure,
      name   => $package_name,
      tag    => 'postgresql',
    }
  }

  file { $validcon_script_path:
    ensure => $file_ensure,
    source => 'puppet:///modules/postgresql/validate_postgresql_connection.sh',
    owner  => 0,
    group  => 0,
    mode   => '0755',
  }

}
