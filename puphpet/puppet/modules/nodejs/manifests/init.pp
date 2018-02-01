# = Class: nodejs
#
# == Parameters:
#
# [*version*]
#   The NodeJS version ('vX.Y.Z', 'latest', 'lts' or 'v6.x' (latest release from the NodeJS 6 branch)).
#
# [*target_dir*]
#   Where to install the executables.
#
# [*make_install*]
#   If false, will install from nodejs.org binary distributions.
#
# [*node_path*]
#   Value of the system environment variable (default: "/usr/local/node/node-default/lib/node_modules").
#
# [*cpu_cores*]
#   Number of CPU cores to use for compiling nodejs. Will be used for parallel 'make' jobs.
#
# [*instances*]
#   List of instances to install.
#
# [*instances_to_remove*]
#   Instances to be removed.
#
# [*download_timeout*]
#   Maximum download timeout.
#
# [*build_deps*]
#   Optional parameter whether or not to allow the module to installs its dependant packages.
#
# == Example:
#
#  include nodejs
#
#  class { 'nodejs':
#    version  => 'v0.10.17'
#  }
#
class nodejs(
  $version             = $::nodejs::params::version,
  $target_dir          = $::nodejs::params::target_dir,
  $make_install        = $::nodejs::params::make_install,
  $node_path           = $::nodejs::params::node_path,
  $cpu_cores           = $::nodejs::params::cpu_cores,
  $instances           = $::nodejs::params::instances,
  $instances_to_remove = $::nodejs::params::instances_to_remove,
  $download_timeout    = $::nodejs::params::download_timeout,
  $build_deps          = $::nodejs::params::build_deps,
) inherits ::nodejs::params  {
  validate_string($node_path)
  validate_integer($cpu_cores)
  validate_string($version)
  validate_string($target_dir)
  validate_bool($make_install)
  validate_hash($instances)
  validate_array($instances_to_remove)
  validate_integer($download_timeout)
  validate_bool($build_deps)

  $node_version        = evaluate_version($version)
  $nodejs_default_path = '/usr/local/node/node-default'

  if $build_deps {
    Anchor['nodejs::start'] ->
    class { '::nodejs::instance::pkgs':
      make_install => $make_install,
    } ->
    Class['::nodejs::instances']
  }
  anchor { 'nodejs::start': } ->
    class { '::nodejs::instances':
      instances           => $instances,
      node_version        => $node_version,
      target_dir          => $target_dir,
      make_install        => $make_install,
      cpu_cores           => $cpu_cores,
      instances_to_remove => $instances_to_remove,
      nodejs_default_path => $nodejs_default_path,
      download_timeout    => $download_timeout,
    } ->
    # TODO remove!
    file { '/etc/profile.d/nodejs.sh':
      ensure  => file,
      owner   => 'root',
      group   => 'root',
      mode    => '0644',
      content => template("${module_name}/nodejs.sh.erb"),
      require => File[$nodejs_default_path],
    } ->
  anchor { 'nodejs::end': }
}
