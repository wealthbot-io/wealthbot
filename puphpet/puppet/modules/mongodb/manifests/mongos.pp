# This installs a Mongo Shard daemon. See README.md for more details.
class mongodb::mongos (
  Variant[Boolean, String] $ensure                          = $mongodb::params::mongos_ensure,
  Stdlib::Absolutepath $config                              = $mongodb::params::mongos_config,
  $config_content                                           = undef,
  $config_template                                          = undef,
  $configdb                                                 = $mongodb::params::mongos_configdb,
  Optional[Hash] $config_data                               = $mongodb::params::config_data,
  Boolean $service_manage                                   = $mongodb::params::mongos_service_manage,
  Optional[String] $service_provider                        = undef,
  Optional[String] $service_name                            = $mongodb::params::mongos_service_name,
  Boolean $service_enable                                   = $mongodb::params::mongos_service_enable,
  Enum['stopped','running'] $service_ensure                 = $mongodb::params::mongos_service_ensure,
  Optional[Enum['stopped','running']] $service_status       = $mongodb::params::mongos_service_status,
  Variant[Boolean, String] $package_ensure                  = $mongodb::params::package_ensure_mongos,
  String $package_name                                      = $mongodb::params::mongos_package_name,
  Optional[Stdlib::Absolutepath] $unixsocketprefix          = $mongodb::params::mongos_unixsocketprefix,
  Optional[Stdlib::Absolutepath] $pidfilepath               = $mongodb::params::mongos_pidfilepath,
  Optional[Variant[Boolean, Stdlib::Absolutepath]] $logpath = $mongodb::params::mongos_logpath,
  Optional[Boolean] $fork                                   = $mongodb::params::mongos_fork,
  Optional[Array[Stdlib::Compat::Ip_address]] $bind_ip      = undef,
  Optional[Integer[1, 65535]] $port                         = undef,
  Boolean $restart                                          = $mongodb::params::mongos_restart,
) inherits mongodb::params {
  contain mongodb::mongos::install
  contain mongodb::mongos::config
  contain mongodb::mongos::service

  if ($ensure == 'present' or $ensure == true) {
    Class['mongodb::mongos::install'] -> Class['mongodb::mongos::config']

    if $restart {
      # If $restart is true, notify the service on config changes (~>)
      Class['mongodb::mongos::config'] ~> Class['mongodb::mongos::service']
    } else {
      # If $restart is false, config changes won't restart the service (->)
      Class['mongodb::mongos::config'] -> Class['mongodb::mongos::service']
    }
  } else {
    Class['mongodb::mongos::service'] -> Class['mongodb::mongos::config'] -> Class['mongodb::mongos::install']
  }

}
