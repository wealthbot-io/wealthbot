# ntp
#
# Main class, includes all other classes.
#
# @param authprov [Optional[String]] Enables compatibility with W32Time in some versions of NTPd (such as Novell DSfW). Default value: undef.
# @param broadcastclient [Boolean] Enables reception of broadcast server messages to any local interface.
# @param config [Stdlib] Specifies a file for NTP's configuration info. Default value: '/etc/ntp.conf' (or '/etc/inet/ntp.conf' on Solaris).
# @param config_dir [Stdlib] Stdlib::Absolutepath. Specifies a directory for the NTP configuration files. Default value: undef.
# @param config_epp [String] Specifies an absolute or relative file path to an EPP template for the config file. Example value: 'ntp/ntp.conf.epp'. A validation error is thrown if both this **and** the `config_template` parameter are specified.
# @param config_file_mode [String] Specifies a file mode for the ntp configuration file. Default value: '0664'.
# @param config_template [String] Specifies an absolute or relative file path to an ERB template for the config file. Example value: 'ntp/ntp.conf.erb'. A validation error is thrown if both this **and** the `config_epp` parameter are specified.
# @param disable_auth [Boolean] Disables cryptographic authentication for broadcast client, multicast client, and symmetric passive associations.
# @param disable_dhclient [Boolean] Disables `ntp-servers` in `dhclient.conf` to prevent Dhclient from managing the NTP configuration.
# @param disable_kernel [Boolean] Disables kernel time discipline.
# @param disable_monitor [Boolean] Disables the monitoring facility in NTP. Default value: true.
# @param driftfile [Stdlib::Absolutepath] Specifies an NTP driftfile. Default value: '/var/lib/ntp/drift' (except on AIX and Solaris).
# @param enable_mode7 [Boolean] Enables processing of NTP mode 7 implementation-specific requests which are used by the deprecated ntpdc program. Default value: false.
# @param fudge [Optional. Array[String]]. Provides additional information for individual clock drivers. Default value: [ ]
# @param iburst_enable [Boolean] Specifies whether to enable the iburst option for every NTP peer. Default value: false (true on AIX and Debian).
# @param interfaces [Array[String]]. Specifies one or more network interfaces for NTP to listen on. Default value: [ ].
# @param interfaces_ignore [Array[String]] Specifies one or more ignore pattern for the NTP listener configuration (for example: all, wildcard, ipv6). Default value: [ ].
# @param keys [Array[String]] Distributes keys to keys file. Default value: [ ].
# @param keys_controlkey [Optional. Ntp::Key_id] Specifies the key identifier to use with the ntpq utility. Value in the range of 1 to 65,534 inclusive. Default value: ' '.
# @param keys_enable [Boolean] Whether to enable key-based authentication. Default value: false.
# @param keys_file [Stdlib::Absolutepath] Specifies the complete path and location of the MD5 key file containing the keys and key identifiers used by ntpd, ntpq and ntpdc when operating with symmetric key cryptography. Default value: `/etc/ntp.keys` (on RedHat and Amazon, `/etc/ntp/keys`).
# @param keys_requestkey [Optional Ntp::Key_id] Specifies the key identifier to use with the ntpdc utility program. Value in the range of 1 to 65,534. Default value: ' '.
# @param keys_trusted [Optional. Array[Ntp::Key_id]] Provides one or more keys to be trusted by NTP. Default value: [ ].
# @param leapfile [Optional Stdlib::Absolutepath] Specifies a leap second file for NTP to use. Default value: ' '.
# @param logfile [Optional Stdlib::Absolutepath] Specifies a log file for NTP to use instead of syslog. Default value: ' '.
# @param minpoll [Optional Ntp::Poll_interval] Sets Puppet to non-standard minimal poll interval of upstream servers. Values: 3 to 16. Default: undef.
# @param maxpoll [Optional Ntp::Poll_interval] Sets use non-standard maximal poll interval of upstream servers. Values: 3 to 16. Default option: undef, except on FreeBSD (on FreeBSD, defaults to 9).
# @param ntpsigndsocket [Optional Stdlib::Absolutepath] Sets NTP to sign packets using the socket in the ntpsigndsocket path. Requires NTP to be configured to sign sockets. Value: Path to the socket directory; for example, for Samba: `usr/local/samba/var/lib/ntp_signd/`. Default value: undef.
# @param package_ensure [String] Whether to install the NTP package, and what version to install. Values: 'present', 'latest', or a specific version number. Default value: 'present'.
# @param package_manage [Boolean] Whether to manage the NTP package. Default value: true.
# @param package_name [Array[String]] Specifies the NTP package to manage. Default value: ['ntp'] (except on AIX and Solaris).
# @param panic [Optional Integer[0]] Whether NTP should "panic" in the event of a very large clock skew. Applies only if `tinker` option set to true or if your environment is in a virtual machine. Default value: 0 if environment is virtual, undef in all other cases.
# @param peers [Array[String]] List of NTP servers with which to synchronise the local clock.
# @param pool [Array[String]] List of NTP server pools with which to synchronise the local clock.
# @param preferred_servers [Array[String] Specifies one or more preferred peers. Puppet appends 'prefer' to each matching item in the `servers` array. Default value: [ ].
# @param noselect_servers [Array[String] Specifies one or more peers to not sync with. Puppet appends 'noselect' to each matching item in the `servers` array. Default value: [ ].
# @param restrict [Array[String]] Specifies one or more `restrict` options for the NTP configuration. Puppet prefixes each item with 'restrict', so you need to list only the content of the restriction. Default value for most operating systems: '[default kod nomodify notrap nopeer noquery', '-6 default kod nomodify notrap nopeer noquery', '127.0.0.1', '-6 ::1']`. Default value for AIX systems: `['default nomodify notrap nopeer noquery', '127.0.0.1',]`.
# @param servers [Array[String]] Specifies one or more servers to be used as NTP peers. Default value: varies by operating system.
# @param service_enable [Boolean] Whether to enable the NTP service at boot. Default value: true.
# @param service_ensure [Enum['running', 'stopped']] Whether the NTP service should be running. Default value: 'running'.
# @param service_manage [Boolean] Whether to manage the NTP service.  Default value: true.
# @param service_name [String] The NTP service to manage. Default value: varies by operating system.
# @param service_provider [String] Which service provider to use for NTP. Default value: 'undef'.
# @param statistics [Optional Array] List of statistics to have NTP generate and keep. Default value: [ ].
# @param statsdir [Optional Stdlib::Absolutepath] Location of the NTP statistics directory on the managed system. Default value: '/var/log/ntpstats'.
# @param step_tickers_file [Optional Stdlib::Absolutepath] Location of the step tickers file on the managed system. Default value: varies by operating system.
# @param step_tickers_epp [Optional String] Location of the step tickers EPP template file. Default value: varies by operating system. Validation error is thrown if both this and the `step_tickers_template` parameters are specified.
# @param step_tickers_template [Optional String] Location of the step tickers ERB template file. Default value: varies by operating system. Validation error is thrown if both this and the `step_tickers_epp` parameter are specified.
# @param stepout [Optional Integer[0, 65535]]. Value for stepout if `tinker` value is true. Valid options: unsigned shortint digit. Default value: undef.
# @param tos [Boolean] Whether to enable tos options. Default value: false.
# @param tos_minclock [Optional Integer[1]] Specifies the minclock tos option. Default value: 3.
# @param tos_minsane [Optional Integer[1]] Specifies the minsane tos option. Default value: 1.
# @param tos_floor [Optional Integer[1]] Specifies the floor tos option. Default value: 1.
# @param tos_ceiling [Optional Integer[1]] Specifies the ceiling tos option. Default value: 15.
# @param tos_cohort [Variant Boolean, Integer[0,1]] Specifies the cohort tos option. Valid options: 0 or 1. Default value: 0.
# @param tinker [Boolean] Whether to enable tinker options. Default value: false.
# @param udlc [Boolean] Specifies whether to configure NTP to use the undisciplined local clock as a time source. Default value: false.
# @param udlc_stratum [Optional Integer[1,15]]. Specifies the stratum the server should operate at when using the undisciplined local clock as the time source. This value should be set to no less than 10 if ntpd might be accessible outside your immediate, controlled network. Default value: 10.am udlc [Boolean] Specifies whether to configure NTP to use the undisciplined local clock as a time source. Default value: false.
class ntp (
  Boolean $broadcastclient,
  Stdlib::Absolutepath $config,
  Optional[Stdlib::Absolutepath] $config_dir,
  String $config_file_mode,
  Optional[String] $config_epp,
  Optional[String] $config_template,
  Boolean $disable_auth,
  Boolean $disable_dhclient,
  Boolean $disable_kernel,
  Boolean $disable_monitor,
  Boolean $enable_mode7,
  Optional[Array[String]] $fudge,
  Stdlib::Absolutepath $driftfile,
  Optional[Stdlib::Absolutepath] $leapfile,
  Optional[Stdlib::Absolutepath] $logfile,
  Boolean $iburst_enable,
  Array[String] $keys,
  Boolean $keys_enable,
  Stdlib::Absolutepath $keys_file,
  Optional[Ntp::Key_id] $keys_controlkey,
  Optional[Ntp::Key_id] $keys_requestkey,
  Optional[Array[Ntp::Key_id]] $keys_trusted,
  Optional[Ntp::Poll_interval] $minpoll,
  Optional[Ntp::Poll_interval] $maxpoll,
  String $package_ensure,
  Boolean $package_manage,
  Array[String] $package_name,
  Optional[Integer[0]] $panic,
  Array[String] $peers,
  Optional[Array[String]] $pool,
  Array[String] $preferred_servers,
  Array[String] $noselect_servers,
  Array[String] $restrict,
  Array[String] $interfaces,
  Array[String] $interfaces_ignore,
  Array[String] $servers,
  Boolean $service_enable,
  Enum['running', 'stopped'] $service_ensure,
  Boolean $service_manage,
  String $service_name,
  Optional[String] $service_provider,
  Optional[Array] $statistics,
  Optional[Stdlib::Absolutepath] $statsdir,
  Optional[Integer[0, 65535]] $stepout,
  Optional[Stdlib::Absolutepath] $step_tickers_file,
  Optional[String] $step_tickers_epp,
  Optional[String] $step_tickers_template,
  Optional[Boolean] $tinker,
  Boolean $tos,
  Optional[Integer[1]] $tos_maxclock,
  Optional[Integer[1]] $tos_minclock,
  Optional[Integer[1]] $tos_minsane,
  Optional[Integer[1]] $tos_floor,
  Optional[Integer[1]] $tos_ceiling,
  Variant[Boolean, Integer[0,1]] $tos_cohort,
  Boolean $udlc,
  Optional[Integer[1,15]] $udlc_stratum,
  Optional[Stdlib::Absolutepath] $ntpsigndsocket,
  Optional[String] $authprov,
) {
  # defaults for tinker and panic are different, when running on virtual machines
  if str2bool($facts['is_virtual']) {
    $_tinker = pick($tinker, true)
    $_panic  = pick($panic, 0)
  } else {
    $_tinker = pick($tinker, false)
    $_panic  = $panic
  }

  contain ntp::install
  contain ntp::config
  contain ntp::service

  Class['::ntp::install']
  -> Class['::ntp::config']
  ~> Class['::ntp::service']
}
