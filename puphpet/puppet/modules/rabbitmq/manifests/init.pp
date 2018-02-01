# rabbitmq
#
# @summary A module to manage RabbitMQ
#
# @example Basic usage
#  include rabbitmq
#
# @example rabbitmq class
#  class { 'rabbitmq':
#    service_manage    => false,
#    port              => '5672',
#    delete_guest_user => true,
#  }
#
# @example Offline installation from local mirror:
#
#  class { 'rabbitmq':
#    key_content     => template('openstack/rabbit.pub.key'),
#    package_gpg_key => '/tmp/rabbit.pub.key',
#  }
#
# @example Use external package key source for any (apt/rpm) package provider:
#  class { 'rabbitmq':
#    package_gpg_key => 'http://www.some_site.some_domain/some_key.pub.key',
#  }
#
# @example To use RabbitMQ Environment Variables, use the parameters `environment_variables` e.g.:
#
#  class { 'rabbitmq':
#    port                  => '5672',
#    environment_variables => {
#      'NODENAME'    => 'node01',
#      'SERVICENAME' => 'RabbitMQ'
#    }
#  }
#
# @example Change RabbitMQ Config Variables in rabbitmq.config:
#
#  class { 'rabbitmq':
#    port             => '5672',
#    config_variables => {
#      'hipe_compile' => true,
#      'frame_max'    => 131072,
#      'log_levels'   => "[{connection, info}]"
#    }
#  }
#
# @example Change Erlang Kernel Config Variables in rabbitmq.config
#  class { 'rabbitmq':
#    port                    => '5672',
#    config_kernel_variables => {
#      'inet_dist_listen_min' => 9100,
#      'inet_dist_listen_max' => 9105,
#    }
#  }
# @example Change Management Plugin Config Variables in rabbitmq.config
#  class { 'rabbitmq':
#    config_management_variables => {
#      'rates_mode' => 'basic',
#    }
#  }
#
# @example Change Additional Config Variables in rabbitmq.config
#  class { 'rabbitmq':
#    config_additional_variables => {
#      'autocluster' => '[{consul_service, "rabbit"},{cluster_name, "rabbit"}]',
#      'foo'         => '[{bar, "baz"}]'
#    }
#  }
#  This will result in the following config appended to the config file:
#  {autocluster, [{consul_service, "rabbit"},{cluster_name, "rabbit"}]},
#   {foo, [{bar, "baz"}]}
#  (This is required for the [autocluster plugin](https://github.com/aweber/rabbitmq-autocluster)
#
# @example Use RabbitMQ clustering facilities
#  class { 'rabbitmq':
#    config_cluster           => true,
#    cluster_nodes            => ['rabbit1', 'rabbit2'],
#    cluster_node_type        => 'ram',
#    erlang_cookie            => 'A_SECRET_COOKIE_STRING',
#    wipe_db_on_cookie_change => true,
#  }
#
# @param admin_enable If enabled sets up the management interface/plugin for RabbitMQ.
# @param auth_backends An array specifying authorization/authentication backend to use. Single quotes should be placed around array entries, ex. ['{foo, baz}', 'baz'] Defaults to [rabbit_auth_backend_internal], and if using LDAP defaults to [rabbit_auth_backend_internal, rabbit_auth_backend_ldap].
# @param cluster_node_type Choose between disc and ram nodes.
# @param cluster_nodes An array of nodes for clustering.
# @param cluster_partition_handling Value to set for `cluster_partition_handling` RabbitMQ configuration variable.
# @param collect_statistics_interval Set the collect_statistics_interval in rabbitmq.config
# @param config The file to use as the rabbitmq.config template.
# @param config_additional_variables Additional config variables in rabbitmq.config
# @param config_cluster Enable or disable clustering support.
# @param config_kernel_variables Hash of Erlang kernel configuration variables to set (see [Variables Configurable in rabbitmq.config](#variables-configurable-in-rabbitmq.config)).
# @param config_path The path to write the RabbitMQ configuration file to.
# @param config_management_variables Hash of configuration variables for the [Management Plugin](https://www.rabbitmq.com/management.html).
# @param config_stomp Enable or disable stomp.
# @param config_shovel Enable or disable shovel.
# @param config_shovel_statics Hash of static shovel configurations
# @param config_variables To set config variables in rabbitmq.config
# @param default_user Username to set for the `default_user` in rabbitmq.config.
# @param default_pass Password to set for the `default_user` in rabbitmq.config.
# @param delete_guest_user Controls whether default guest user is deleted.
# @param env_config The template file to use for rabbitmq_env.config.
# @param env_config_path The path to write the rabbitmq_env.config file to.
# @param environment_variables RabbitMQ Environment Variables in rabbitmq_env.config
# @param erlang_cookie The erlang cookie to use for clustering - must be the same between all nodes. This value has no default and must be
#  set explicitly if using clustering. If you run Pacemaker and you don't want to use RabbitMQ buildin cluster, you can set config_cluster
#  to 'False' and set 'erlang_cookie'.
# @param file_limit Set rabbitmq file ulimit. Defaults to 16384. Only available on systems with `$::osfamily == 'Debian'` or
#  `$::osfamily == 'RedHat'`.
# @param heartbeat Set the heartbeat timeout interval, default is unset which uses the builtin server defaults of 60 seconds. Setting this
# @param inetrc_config Template to use for the inetrc config
# @param inetrc_config_path Path of the file to push the inetrc config to.
# @param ipv6 Whether to listen on ipv6
# @param interface Interface to bind to (sets tcp_listeners parameter). By default, bind to all interfaces
#  to `0` will disable heartbeats.
# @param key_content Uses content method for Debian OS family. Should be a template for apt::source class. Overrides `package_gpg_key`
#  behavior, if enabled. Undefined by default.
# @param ldap_auth Set to true to enable LDAP auth.
# @param ldap_server LDAP server to use for auth.
# @param ldap_user_dn_pattern User DN pattern for LDAP auth.
# @param ldap_other_bind How to bind to the LDAP server. Defaults to 'anon'.
# @param ldap_config_variables Hash of other LDAP config variables.
# @param ldap_use_ssl Set to true to use SSL for the LDAP server.
# @param ldap_port Numeric port for LDAP server.
# @param ldap_log Set to true to log LDAP auth.
# @param manage_python If enabled, on platforms that don't provide a Python 2 package by default, ensure that the python package is
#  installed (for rabbitmqadmin). This will only apply if `admin_enable` and `service_manage` are set.
# @param management_hostname The hostname for the RabbitMQ management interface.
# @param management_port The port for the RabbitMQ management interface.
# @param management_ip_address Allows you to set the IP for management interface to bind to separately. Set to 127.0.0.1 to bind to
#  localhost only, or 0.0.0.0 to bind to all interfaces.
# @param management_ssl Enable/Disable SSL for the management port. Has an effect only if ssl => true.
# @param node_ip_address Allows you to set the IP for RabbitMQ service to bind to. Set to 127.0.0.1 to bind to localhost only, or 0.0.0.0
#  to bind to all interfaces.
# @param package_apt_pin Whether to pin the package to a particular source
# @param package_ensure Determines the ensure state of the package.  Set to installed by default, but could be changed to latest.
# @param package_gpg_key RPM package GPG key to import. Uses source method. Should be a URL for Debian/RedHat OS family, or a file name for
#  RedHat OS family. Set to https://packagecloud.io/gpg.key by default. Note, that `key_content`, if specified, would override this
#  parameter for Debian OS family.
# @param package_name Name(s) of the package(s) to install
# @param port The RabbitMQ port.
# @param repos_ensure Ensure that a repo with the official (and newer) RabbitMQ package is configured, along with its signing key.
#  Defaults to false (use system packages). This does not ensure that soft dependencies (like EPEL on RHEL systems) are present.
# @param service_ensure The state of the service.
# @param service_manage Determines if the service is managed.
# @param service_name The name of the service to manage.
# @param ssl Configures the service for using SSL.
#  port => UNSET
# @param ssl_cacert CA cert path to use for SSL.
# @param ssl_cert Cert to use for SSL.
# @param ssl_cert_password Password used when generating CSR.
# @param ssl_depth SSL verification depth.
# @param ssl_dhfile Use this dhparam file [example: generate with `openssl dhparam -out /etc/rabbitmq/ssl/dhparam.pem 2048`
# @param ssl_erl_dist Whether to use the erlang package's SSL (relies on the ssl_erl_path fact)
# @param ssl_honor_cipher_order Force use of server cipher order
# @param ssl_interface Interface for SSL listener to bind to
# @param ssl_key Key to use for SSL.
# @param ssl_only Configures the service to only use SSL.  No cleartext TCP listeners will be created. Requires that ssl => true and
# @param ssl_management_port SSL management port.
# @param ssl_port SSL port for RabbitMQ
# @param ssl_reuse_sessions Reuse ssl sessions
# @param ssl_secure_renegotiate Use ssl secure renegotiate
# @param ssl_stomp_port SSL stomp port.
# @param ssl_verify rabbitmq.config SSL verify setting.
# @param ssl_fail_if_no_peer_cert rabbitmq.config `fail_if_no_peer_cert` setting.
# @param ssl_management_verify rabbitmq.config SSL verify setting for rabbitmq_management.
# @param ssl_manaagement_fail_if_no_peer_cert rabbitmq.config `fail_if_no_peer_cert` setting for rabbitmq_management.
# @param ssl_versions Choose which SSL versions to enable. Example: `['tlsv1.2', 'tlsv1.1']` Note that it is recommended to disable `sslv3
#  and `tlsv1` to prevent against POODLE and BEAST attacks. Please see the [RabbitMQ SSL](https://www.rabbitmq.com/ssl.html) documentation
#  for more information.
# @param ssl_ciphers Support only a given list of SSL ciphers. Example: `['dhe_rsa,aes_256_cbc,sha','dhe_dss,aes_256_cbc,sha',
#  'ecdhe_rsa,aes_256_cbc,sha']`. Supported ciphers in your install can be listed with: rabbitmqctl eval 'ssl:cipher_suites().'
#  Functionality can be tested with cipherscan or similar tool: https://github.com/jvehent/cipherscan.git
# @param stomp_port The port to use for Stomp.
# @param stomp_ssl_only Configures STOMP to only use SSL.  No cleartext STOMP TCP listeners will be created. Requires setting
#  ssl_stomp_port also.
# @param stomp_ensure Enable to install the stomp plugin.
# @param tcp_backlog The size of the backlog on TCP connections.
# @param tcp_keepalive Enable TCP connection keepalive for RabbitMQ service.
# @param tcp_recbuf Corresponds to recbuf in RabbitMQ `tcp_listen_options`
# @param tcp_sndbuf Integer, corresponds to sndbuf in RabbitMQ `tcp_listen_options`
# @param wipe_db_on_cookie_change Boolean to determine if we should DESTROY AND DELETE the RabbitMQ database.
# @param rabbitmq_user OS dependent, default defined in param.pp. The system user the rabbitmq daemon runs as.
# @param rabbitmq_group OS dependent, default defined in param.pp. The system group the rabbitmq daemon runs as.
# @param rabbitmq_home OS dependent. default defined in param.pp. The home directory of the rabbitmq deamon.
# @param $rabbitmqadmin_package OS dependent. default defined in param.pp. If undef: install rabbitmqadmin via archive, otherwise via package
class rabbitmq(
  Boolean $admin_enable                                            = $rabbitmq::params::admin_enable,
  Enum['ram', 'disk', 'disc'] $cluster_node_type                   = $rabbitmq::params::cluster_node_type,
  Array $cluster_nodes                                             = $rabbitmq::params::cluster_nodes,
  String $config                                                   = $rabbitmq::params::config,
  Boolean $config_cluster                                          = $rabbitmq::params::config_cluster,
  Stdlib::Absolutepath $config_path                                = $rabbitmq::params::config_path,
  Boolean $config_ranch                                            = $rabbitmq::params::config_ranch,
  Boolean $config_stomp                                            = $rabbitmq::params::config_stomp,
  Boolean $config_shovel                                           = $rabbitmq::params::config_shovel,
  Hash $config_shovel_statics                                      = $rabbitmq::params::config_shovel_statics,
  String $default_user                                             = $rabbitmq::params::default_user,
  String $default_pass                                             = $rabbitmq::params::default_pass,
  Boolean $delete_guest_user                                       = $rabbitmq::params::delete_guest_user,
  String $env_config                                               = $rabbitmq::params::env_config,
  Stdlib::Absolutepath $env_config_path                            = $rabbitmq::params::env_config_path,
  Optional[String] $erlang_cookie                                  = undef,
  Optional[String] $interface                                      = undef,
  Optional[String] $management_ip_address                          = undef,
  Integer[1, 65535] $management_port                               = $rabbitmq::params::management_port,
  Boolean $management_ssl                                          = $rabbitmq::params::management_ssl,
  Optional[String] $management_hostname                            = undef,
  Optional[String] $node_ip_address                                = undef,
  Optional[Variant[Numeric, String]] $package_apt_pin              = undef,
  String $package_ensure                                           = $rabbitmq::params::package_ensure,
  Optional[String] $package_gpg_key                                = $rabbitmq::params::package_gpg_key,
  Variant[String, Array] $package_name                             = $rabbitmq::params::package_name,
  Optional[String] $package_source                                 = undef,
  Optional[String] $package_provider                               = undef,
  Boolean $repos_ensure                                            = $rabbitmq::params::repos_ensure,
  Boolean $manage_python                                           = $rabbitmq::params::manage_python,
  String $rabbitmq_user                                            = $rabbitmq::params::rabbitmq_user,
  String $rabbitmq_group                                           = $rabbitmq::params::rabbitmq_group,
  Stdlib::Absolutepath $rabbitmq_home                              = $rabbitmq::params::rabbitmq_home,
  Integer $port                                                    = $rabbitmq::params::port,
  Boolean $tcp_keepalive                                           = $rabbitmq::params::tcp_keepalive,
  Integer $tcp_backlog                                             = $rabbitmq::params::tcp_backlog,
  Optional[Integer] $tcp_sndbuf                                    = undef,
  Optional[Integer] $tcp_recbuf                                    = undef,
  Optional[Integer] $heartbeat                                     = undef,
  Enum['running', 'stopped'] $service_ensure                       = $rabbitmq::params::service_ensure,
  Boolean $service_manage                                          = $rabbitmq::params::service_manage,
  String $service_name                                             = $rabbitmq::params::service_name,
  Boolean $ssl                                                     = $rabbitmq::params::ssl,
  Boolean $ssl_only                                                = $rabbitmq::params::ssl_only,
  Optional[Stdlib::Absolutepath] $ssl_cacert                       = undef,
  Optional[Stdlib::Absolutepath] $ssl_cert                         = undef,
  Optional[Stdlib::Absolutepath] $ssl_key                          = undef,
  Optional[Integer] $ssl_depth                                     = undef,
  Optional[String] $ssl_cert_password                              = undef,
  Integer[1, 65535] $ssl_port                                      = $rabbitmq::params::ssl_port,
  Optional[String] $ssl_interface                                  = undef,
  Integer[1, 65535] $ssl_management_port                           = $rabbitmq::params::ssl_management_port,
  Integer[1, 65535] $ssl_stomp_port                                = $rabbitmq::params::ssl_stomp_port,
  Enum['verify_none','verify_peer'] $ssl_verify                    = $rabbitmq::params::ssl_verify,
  Boolean $ssl_fail_if_no_peer_cert                                = $rabbitmq::params::ssl_fail_if_no_peer_cert,
  Enum['verify_none','verify_peer'] $ssl_management_verify         = $rabbitmq::params::ssl_management_verify,
  Boolean $ssl_management_fail_if_no_peer_cert                     = $rabbitmq::params::ssl_management_fail_if_no_peer_cert,
  Optional[Array] $ssl_versions                                    = undef,
  Boolean $ssl_secure_renegotiate                                  = $rabbitmq::params::ssl_secure_renegotiate,
  Boolean $ssl_reuse_sessions                                      = $rabbitmq::params::ssl_reuse_sessions,
  Boolean $ssl_honor_cipher_order                                  = $rabbitmq::params::ssl_honor_cipher_order,
  Optional[Stdlib::Absolutepath] $ssl_dhfile                       = undef,
  Array $ssl_ciphers                                               = $rabbitmq::params::ssl_ciphers,
  Boolean $stomp_ensure                                            = $rabbitmq::params::stomp_ensure,
  Boolean $ldap_auth                                               = $rabbitmq::params::ldap_auth,
  String $ldap_server                                              = $rabbitmq::params::ldap_server,
  Optional[String] $ldap_user_dn_pattern                           = $rabbitmq::params::ldap_user_dn_pattern,
  String $ldap_other_bind                                          = $rabbitmq::params::ldap_other_bind,
  Boolean $ldap_use_ssl                                            = $rabbitmq::params::ldap_use_ssl,
  Integer[1, 65535] $ldap_port                                     = $rabbitmq::params::ldap_port,
  Boolean $ldap_log                                                = $rabbitmq::params::ldap_log,
  Hash $ldap_config_variables                                      = $rabbitmq::params::ldap_config_variables,
  Integer[1, 65535] $stomp_port                                    = $rabbitmq::params::stomp_port,
  Boolean $stomp_ssl_only                                          = $rabbitmq::params::stomp_ssl_only,
  Boolean $wipe_db_on_cookie_change                                = $rabbitmq::params::wipe_db_on_cookie_change,
  String $cluster_partition_handling                               = $rabbitmq::params::cluster_partition_handling,
  Variant[Integer[-1,], Enum['unlimited', 'infinity']] $file_limit = $rabbitmq::params::file_limit,
  Hash $environment_variables                                      = $rabbitmq::params::environment_variables,
  Hash $config_variables                                           = $rabbitmq::params::config_variables,
  Hash $config_kernel_variables                                    = $rabbitmq::params::config_kernel_variables,
  Hash $config_management_variables                                = $rabbitmq::params::config_management_variables,
  Hash $config_additional_variables                                = $rabbitmq::params::config_additional_variables,
  Optional[Array] $auth_backends                                   = undef,
  Optional[String] $key_content                                    = undef,
  Optional[Integer] $collect_statistics_interval                   = undef,
  Boolean $ipv6                                                    = $rabbitmq::params::ipv6,
  String $inetrc_config                                            = $rabbitmq::params::inetrc_config,
  Stdlib::Absolutepath $inetrc_config_path                         = $rabbitmq::params::inetrc_config_path,
  Boolean $ssl_erl_dist                                            = $rabbitmq::params::ssl_erl_dist,
  Optional[String] $rabbitmqadmin_package                          = $rabbitmq::params::rabbitmqadmin_package,
) inherits rabbitmq::params {

  if $ssl_only and ! $ssl {
    fail('$ssl_only => true requires that $ssl => true')
  }

  if $config_stomp and $stomp_ssl_only and ! $ssl_stomp_port  {
    fail('$stomp_ssl_only requires that $ssl_stomp_port be set')
  }

  if $ssl_versions {
    unless $ssl {
      fail('$ssl_versions requires that $ssl => true')
    }
  }

  if $repos_ensure {
    case $facts['os']['family'] {
      'RedHat': {
        contain rabbitmq::repo::rhel
      }
      'Debian': {
        contain rabbitmq::repo::apt
      }
      default: {
      }
    }
  }

  contain rabbitmq::install
  contain rabbitmq::config
  contain rabbitmq::service
  contain rabbitmq::management

  if $admin_enable and $service_manage {
    include 'rabbitmq::install::rabbitmqadmin'

    rabbitmq_plugin { 'rabbitmq_management':
      ensure   => present,
      notify   => Class['rabbitmq::service'],
      provider => 'rabbitmqplugins',
    }

    Class['rabbitmq::service'] -> Class['rabbitmq::install::rabbitmqadmin']
    Class['rabbitmq::install::rabbitmqadmin'] -> Rabbitmq_exchange<| |>
  }

  if $stomp_ensure {
    rabbitmq_plugin { 'rabbitmq_stomp':
      ensure => present,
      notify => Class['rabbitmq::service'],
    }
  }

  if ($ldap_auth) {
    rabbitmq_plugin { 'rabbitmq_auth_backend_ldap':
      ensure => present,
      notify => Class['rabbitmq::service'],
    }
  }

  if ($config_shovel) {
    rabbitmq_plugin { 'rabbitmq_shovel':
      ensure   => present,
      notify   => Class['rabbitmq::service'],
      provider => 'rabbitmqplugins',
    }

    if ($admin_enable) {
      rabbitmq_plugin { 'rabbitmq_shovel_management':
        ensure   => present,
        notify   => Class['rabbitmq::service'],
        provider => 'rabbitmqplugins',
      }
    }
  }

  Class['rabbitmq::install']
  -> Class['rabbitmq::config']
  ~> Class['rabbitmq::service']
  -> Class['rabbitmq::management']

  # Make sure the various providers have their requirements in place.
  Class['rabbitmq::install'] -> Rabbitmq_plugin<| |>

}
