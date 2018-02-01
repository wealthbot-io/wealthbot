# Define: supervisord::group
#
# This define creates an group configuration file
#
# Documentation on parameters available at:
# http://supervisord.org/configuration.html#group-x-section-settings
#
define supervisord::group (
  $programs,
  $ensure           = present,
  $priority         = undef,
  $config_file_mode = '0644'
) {

  include supervisord

  # parameter validation
  validate_legacy(Array, 'validate_array', $programs)
  if $priority { if $priority !~ Integer { validate_legacy('Optional[String]', 'validate_re', $priority, ['^\d+']) } }
  validate_legacy('Optional[String]', 'validate_re', $config_file_mode, ['^0[0-7][0-7][0-7]$'])

  $progstring = array2csv($programs)
  $conf = "${supervisord::config_include}/group_${name}.conf"

  file { $conf:
    ensure  => $ensure,
    owner   => 'root',
    mode    => $config_file_mode,
    content => template('supervisord/conf/group.erb'),
    notify  => Class['supervisord::reload']
  }
}
