# Define puppi::project
#
# This define creates and configures a Puppi project
# You must use different puppi::deploy and puppi::rollback defines
# to to build up the commands list
#
define puppi::project (
  $deploy_root              = undef,
  $source                   = undef,
  $user                     = 'root',
  $predeploy_customcommand  = '',
  $postdeploy_customcommand = '',
  $init_script              = '',
  $disable_services         = '',
  $firewall_src_ip          = '',
  $firewall_dst_port        = 0,
  $report_email             = '',
  $files_prefix             = undef,
  $source_baseurl           = undef,
  $document_root            = '',
  $config_root              = undef,
  $enable                   = true ) {

  require puppi::params

  $ensure = any2bool($enable) ? {
    false   => 'absent',
    default => 'directory',
  }

  $ensurefile = bool2ensure($enable)

  # Create Project subdirs
  file {
    "${puppi::params::projectsdir}/${name}":
      ensure => $ensure,
      mode   => '0755',
      owner  => $puppi::params::configfile_owner,
      group  => $puppi::params::configfile_group,
      force  => true;

    "${puppi::params::projectsdir}/${name}/check":
      ensure  => $ensure,
      mode    => '0755',
      owner   => $puppi::params::configfile_owner,
      group   => $puppi::params::configfile_group,
      force   => true,
      recurse => true,
      purge   => true,
      require => File["${puppi::params::projectsdir}/${name}"];

    "${puppi::params::projectsdir}/${name}/rollback":
      ensure  => $ensure,
      mode    => '0755',
      owner   => $puppi::params::configfile_owner,
      group   => $puppi::params::configfile_group,
      force   => true,
      recurse => true,
      purge   => true,
      require => File["${puppi::params::projectsdir}/${name}"];

    "${puppi::params::projectsdir}/${name}/deploy":
      ensure  => $ensure,
      mode    => '0755',
      owner   => $puppi::params::configfile_owner,
      group   => $puppi::params::configfile_group,
      force   => true,
      recurse => true,
      purge   => true,
      require => File["${puppi::params::projectsdir}/${name}"];

    "${puppi::params::projectsdir}/${name}/initialize":
      ensure  => $ensure,
      mode    => '0755',
      owner   => $puppi::params::configfile_owner,
      group   => $puppi::params::configfile_group,
      force   => true,
      recurse => true,
      purge   => true,
      require => File["${puppi::params::projectsdir}/${name}"];

    "${puppi::params::projectsdir}/${name}/configure":
      ensure  => $ensure,
      mode    => '0755',
      owner   => $puppi::params::configfile_owner,
      group   => $puppi::params::configfile_group,
      force   => true,
      recurse => true,
      purge   => true,
      require => File["${puppi::params::projectsdir}/${name}"];

    "${puppi::params::projectsdir}/${name}/report":
      ensure  => $ensure,
      mode    => '0755',
      owner   => $puppi::params::configfile_owner,
      group   => $puppi::params::configfile_group,
      force   => true,
      recurse => true,
      purge   => true,
      require => File["${puppi::params::projectsdir}/${name}"];
  }

  # Create Project configuration file
  file {
    "${puppi::params::projectsdir}/${name}/config":
      ensure  => $ensurefile,
      content => template('puppi/project/config.erb'),
      mode    => '0644',
      owner   => $puppi::params::configfile_owner,
      group   => $puppi::params::configfile_group,
      require => File["${puppi::params::projectsdir}/${name}"];
  }

}
