# Class: archive::staging
# =======================
#
# backwards compatibility class for staging module.
#
class archive::staging (
  String $path  = $archive::params::path,
  String $owner = $archive::params::owner,
  String $group = $archive::params::group,
  String $mode  = $archive::params::mode,
) inherits archive::params {
  include '::archive'

  if !defined(File[$path]) {
    file { $path:
      ensure => directory,
      owner  => $owner,
      group  => $group,
      mode   => $mode,
    }
  }
}
