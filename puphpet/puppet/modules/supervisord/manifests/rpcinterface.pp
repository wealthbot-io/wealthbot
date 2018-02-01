# Define: supervisord::rpcinterface
#
# This define creates an rpcinterface configuration file
#
# Documentation on parameters available at:
# http://supervisord.org/configuration.html#rpcinterface-x-section-settings
#
define supervisord::rpcinterface (
  $rpcinterface_factory,
  $ensure                = present,
  $retries               = undef,
  $config_file_mode      = '0644'
) {

  include supervisord

  # parameter validation
  if $retries { if $retries !~ Integer { validate_legacy('Optional[String]', 'validate_re', $retries, ['^\d+'])}}
  validate_legacy('Optional[String]', 'validate_re', $config_file_mode, ['^0[0-7][0-7][0-7]$'])

  $conf = "${supervisord::config_include}/rpcinterface_${name}.conf"

  file { $conf:
    ensure  => $ensure,
    owner   => 'root',
    mode    => $config_file_mode,
    content => template('supervisord/conf/rpcinterface.erb'),
    notify  => Class['supervisord::reload']
  }

}
