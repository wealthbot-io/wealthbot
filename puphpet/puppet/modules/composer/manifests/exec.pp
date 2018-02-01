# == Type: composer::exec
#
# Either installs from composer.json or updates project or specific packages
#
# === Authors
#
# Thomas Ploch <profiploch@gmail.com>
#
# === Copyright
#
# Copyright 2013 Thomas Ploch
#
define composer::exec (
  $cmd,
  $cwd,
  $packages                 = [],
  $prefer_source            = false,
  $prefer_dist              = false,
  $dry_run                  = false,
  $custom_installers        = false,
  $scripts                  = false,
  $optimize                 = false,
  $ignore_platform_reqs     = false,
  $interaction              = false,
  $dev                      = true,
  $no_update                = false,
  $no_progress              = false,
  $update_with_dependencies = false,
  $logoutput                = false,
  $verbose                  = false,
  $refreshonly              = false,
  $lock                     = false,
  $timeout                  = undef,
  $user                     = $composer::user,
  $global                   = false,
  $working_dir              = undef,
  $onlyif                   = undef,
  $unless                   = undef,
) {
  require ::composer

  validate_legacy(String, 'validate_string', $cmd)
  validate_legacy(String, 'validate_string', $cwd)
  validate_legacy(Boolean, 'validate_bool', $lock)
  validate_legacy(Boolean, 'validate_bool', $prefer_source)
  validate_legacy(Boolean, 'validate_bool', $prefer_dist)
  validate_legacy(Boolean, 'validate_bool', $dry_run)
  validate_legacy(Boolean, 'validate_bool', $custom_installers)
  validate_legacy(Boolean, 'validate_bool', $scripts)
  validate_legacy(Boolean, 'validate_bool', $optimize)
  validate_legacy(Boolean, 'validate_bool', $ignore_platform_reqs)
  validate_legacy(Boolean, 'validate_bool', $interaction)
  validate_legacy(Boolean, 'validate_bool', $dev)
  validate_legacy(Boolean, 'validate_bool', $verbose)
  validate_legacy(Boolean, 'validate_bool', $refreshonly)
  validate_legacy(Array, 'validate_array', $packages)

  $m_timeout = $timeout?{
    undef => 300,
    default => $timeout
  }
  Exec {
    path        => "/bin:/usr/bin/:/sbin:/usr/sbin:${composer::target_dir}",
    environment => ["COMPOSER_HOME=${composer::composer_home}", "COMPOSER_PROCESS_TIMEOUT=${m_timeout}"],
    user        => $user,
    timeout     => $timeout
  }

  if $cmd != 'install' and $cmd != 'update' and $cmd != 'require' {
    fail(
      "Only types 'install', 'update' and 'require'' are allowed, ${cmd} given"
    )
  }

  if $prefer_source and $prefer_dist {
    fail('Only one of \$prefer_source or \$prefer_dist can be true.')
  }

  $composer_path = "${composer::target_dir}/${composer::composer_file}"

  $command = $global ? {
    true  => "${composer::php_bin} ${composer_path} global ${cmd}",
    false => "${composer::php_bin} ${composer_path} ${cmd}",
  }

  exec { "composer_${cmd}_${title}":
    command     => template("composer/${cmd}.erb"),
    cwd         => $cwd,
    logoutput   => $logoutput,
    refreshonly => $refreshonly,
    user        => $user,
    onlyif      => $onlyif,
    unless      => $unless,
  }
}
