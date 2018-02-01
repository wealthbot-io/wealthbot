# == Class: mongodb
#
# Direct use of this class is deprecated. Please use mongodb::server
#
# Manage mongodb installations on RHEL, CentOS, Debian and Ubuntu - either
# installing from the 10Gen repo or from EPEL in the case of EL systems.
#
# === Parameters
#
# init (auto discovered) - override init (sysv or upstart) for Debian derivatives
# location - override apt location configuration for Debian derivatives
# packagename (auto discovered) - override the package name
# servicename (auto discovered) - override the service name
# service-enable (default: true) - Enable the service and ensure it is running
#
# === Examples
#
# To install with defaults from the distribution packages on any system:
#   include mongodb
#
# To install from 10gen on a EL server
#   class { 'mongodb':
#     enable_10gen => true,
#   }
#
# === Authors
#
# Craig Dunn <craig@craigdunn.org>
#
# === Copyright
#
# Copyright 2013 PuppetLabs
#
class mongodb (
  Optional[String] $init                                                                    = $mongodb::params::service_provider,
  Optional[String] $packagename                                                             = undef,
  String $servicename                                                                       = $mongodb::params::service_name,
  Variant[Boolean, Stdlib::Absolutepath] $logpath                                           = $mongodb::params::logpath,
  Boolean $logappend                                                                        = true,
  Optional[String] $system_logrotate                                                        = undef,
  Optional[Boolean] $fork                                                                   = $mongodb::params::fork,
  Optional[Integer[1, 65535]] $port                                                         = undef,
  Stdlib::Absolutepath $dbpath                                                              = $mongodb::params::dbpath,
  Optional[Boolean] $journal                                                                = undef,
  Optional[String] $nojournal                                                               = undef,
  Optional[Boolean] $smallfiles                                                             = undef,
  Optional[Boolean] $cpu                                                                    = undef,
  Optional[Boolean] $noauth                                                                 = undef,
  Optional[Boolean] $auth                                                                   = undef,
  Optional[Boolean] $verbose                                                                = undef,
  Optional[Boolean] $objcheck                                                               = undef,
  Optional[Boolean] $quota                                                                  = undef,
  Optional[Integer] $oplog_size                                                             = undef,
  $nohints                                                                                  = undef,
  Optional[Boolean] $nohttpinterface                                                        = undef,
  Optional[Boolean] $noscripting                                                            = undef,
  Optional[Boolean] $notablescan                                                            = undef,
  Optional[Boolean] $noprealloc                                                             = undef,
  Optional[Integer] $nssize                                                                 = undef,
  Optional[String] $mms_token                                                               = undef,
  Optional[String] $mms_name                                                                = undef,
  $mms_interval                                                                             = undef,
  Optional[Boolean] $configsvr                                                              = undef,
  Optional[Boolean] $shardsvr                                                               = undef,
  Optional[String] $replset                                                                 = undef,
  Optional[Boolean] $rest                                                                   = undef,
  Optional[Boolean] $quiet                                                                  = undef,
  Optional[Integer] $slowms                                                                 = undef,
  Optional[Stdlib::Absolutepath] $keyfile                                                   = undef,
  Optional[String[6]] $key                                                                  = undef,
  Optional[Boolean] $ipv6                                                                   = undef,
  Optional[Variant[Stdlib::Compat::Ip_address, Array[Stdlib::Compat::Ip_address]]] $bind_ip = undef,
  Optional[Stdlib::Absolutepath] $pidfilepath                                               = undef,
  Optional[String] $pidfilemode                                                             = undef,
) inherits mongodb::params {

  notify { 'An attempt has been made below to automatically apply your custom
    settings to mongodb::server. Please verify this works in a safe test
    environment.': }

  class { 'mongodb::server':
    package_name    => $packagename,
    logpath         => $logpath,
    logappend       => $logappend,
    fork            => $fork,
    port            => $port,
    dbpath          => $dbpath,
    journal         => $journal,
    nojournal       => $nojournal,
    smallfiles      => $smallfiles,
    cpu             => $cpu,
    noauth          => $noauth,
    verbose         => $verbose,
    objcheck        => $objcheck,
    quota           => $quota,
    oplog_size      => $oplog_size,
    nohints         => $nohints,
    nohttpinterface => $nohttpinterface,
    noscripting     => $noscripting,
    notablescan     => $notablescan,
    noprealloc      => $noprealloc,
    nssize          => $nssize,
    mms_token       => $mms_token,
    mms_name        => $mms_name,
    mms_interval    => $mms_interval,
    configsvr       => $configsvr,
    shardsvr        => $shardsvr,
    replset         => $replset,
    rest            => $rest,
    quiet           => $quiet,
    slowms          => $slowms,
    keyfile         => $keyfile,
    key             => $key,
    ipv6            => $ipv6,
    bind_ip         => $bind_ip,
    pidfilepath     => $pidfilepath,
  }

}
