# Define: supervisord::ctlplugin
#
# This define creates a ctlplugin section in supervisord.conf
#
# Sadly, I have been unable to find any documentation or mention of this stanza type
# in any of supervisord docs or code, but I am using it in production and it works.
#
#
# Documentation on parameters available at:
# http://supervisord.org/configuration.html#program-x-section-settings
#
define supervisord::ctlplugin(
  $ctl_factory,
  $ensure      = present,
) {
  include supervisord

  # parameter validation
  validate_legacy(String, 'validate_string', $ctl_factory)

  concat::fragment { "ctlplugin:${name}":
    target  => $supervisord::config_file,
    content => template('supervisord/conf/ctlplugin.erb'),
    order   => 70,
  }

}
