# Class for setting cross-class global overrides. See README.md for more
# details.
class postgresql::globals (
  $client_package_name      = undef,
  $server_package_name      = undef,
  $contrib_package_name     = undef,
  $devel_package_name       = undef,
  $java_package_name        = undef,
  $docs_package_name        = undef,
  $perl_package_name        = undef,
  $plperl_package_name      = undef,
  $plpython_package_name    = undef,
  $python_package_name      = undef,
  $postgis_package_name     = undef,

  $service_name             = undef,
  $service_provider         = undef,
  $service_status           = undef,
  $default_database         = undef,

  $validcon_script_path     = undef,

  $initdb_path              = undef,
  $createdb_path            = undef,
  $psql_path                = undef,
  $pg_hba_conf_path         = undef,
  $pg_ident_conf_path       = undef,
  $postgresql_conf_path     = undef,
  $recovery_conf_path       = undef,
  $default_connect_settings = {},

  $pg_hba_conf_defaults     = undef,

  $datadir                  = undef,
  $confdir                  = undef,
  $bindir                   = undef,
  $xlogdir                  = undef,
  $logdir                   = undef,
  $log_line_prefix          = undef,

  $user                     = undef,
  $group                    = undef,

  $version                  = undef,
  $postgis_version          = undef,
  $repo_proxy               = undef,
  $repo_baseurl             = undef,

  $needs_initdb             = undef,

  $encoding                 = undef,
  $locale                   = undef,
  $data_checksums           = undef,
  $timezone                 = undef,

  $manage_pg_hba_conf       = undef,
  $manage_pg_ident_conf     = undef,
  $manage_recovery_conf     = undef,

  $manage_package_repo      = undef,
  $module_workdir           = undef,
) {
  # We are determining this here, because it is needed by the package repo
  # class.
  $default_version = $::osfamily ? {
    /^(RedHat|Linux)/ => $::operatingsystem ? {
      'Fedora' => $::operatingsystemrelease ? {
        /^(26)$/       => '9.6',
        /^(24|25)$/    => '9.5',
        /^(22|23)$/    => '9.4',
        /^(21)$/       => '9.3',
        /^(18|19|20)$/ => '9.2',
        /^(17)$/       => '9.1',
        default        => undef,
      },
      'Amazon' => '9.2',
      default => $::operatingsystemrelease ? {
        /^7\./ => '9.2',
        /^6\./ => '8.4',
        /^5\./ => '8.1',
        default => undef,
      },
    },
    'Debian' => $::operatingsystem ? {
      'Debian' => $::operatingsystemrelease ? {
        /^(squeeze|6\.)/ => '8.4',
        /^(wheezy|7\.)/  => '9.1',
        /^(jessie|8\.)/  => '9.4',
        /^(stretch|9\.)/ => '9.6',
        default => undef,
      },
      'Ubuntu' => $::operatingsystemrelease ? {
        /^(10.04|10.10|11.04)$/ => '8.4',
        /^(11.10|12.04|12.10|13.04|13.10)$/ => '9.1',
        /^(14.04)$/ => '9.3',
        /^(14.10|15.04|15.10)$/ => '9.4',
        /^(16.04|16.10)$/ => '9.5',
        /^(17.04)$/ => '9.6',
        default => undef,
      },
      default => undef,
    },
    'Archlinux' => $::operatingsystem ? {
      /Archlinux/ => '9.2',
      default => '9.2',
    },
    'Gentoo' => '9.5',
    'FreeBSD' => '93',
    'OpenBSD' => $::operatingsystemrelease ? {
      /5\.6/ => '9.3',
      /5\.[7-9]/ => '9.4',
      /6\.[0-9]/ => '9.5',
    },
    'Suse' => $::operatingsystem ? {
      'SLES' => $::operatingsystemrelease ? {
        /11\.[0-4]/ => '91',
        /12\.0/     => '93',
        /12\.[1-2]/ => '94',
        default     => '96',
      },
      'OpenSuSE' => $::operatingsystemrelease ? {
        /42\.[1-2]/ => '94',
        default     => '96',
      },
      default => undef,
    },
    default => undef,
  }
  $globals_version = pick($version, $default_version, 'unknown')
  if($globals_version == 'unknown') {
    fail('No preferred version defined or automatically detected.')
  }

  $default_postgis_version = $globals_version ? {
    '8.1'   => '1.3.6',
    '8.4'   => '2.0',
    '9.0'   => '2.1',
    '9.1'   => '2.1',
    '91'    => '2.1',
    '9.2'   => '2.3',
    '9.3'   => '2.3',
    '93'    => '2.3',
    '9.4'   => '2.3',
    '9.5'   => '2.3',
    '9.6'   => '2.3',
    default => undef,
  }
  $globals_postgis_version = $postgis_version ? {
    undef   => $default_postgis_version,
    default => $postgis_version,
  }

  # Setup of the repo only makes sense globally, so we are doing this here.
  if($manage_package_repo) {
    class { 'postgresql::repo':
      version => $globals_version,
      proxy   => $repo_proxy,
      baseurl => $repo_baseurl,
    }
  }
}
