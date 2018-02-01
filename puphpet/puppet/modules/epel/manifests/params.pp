# Optional parameters in setting up EPEL
class epel::params {
  # Setting to 'absent' will fall back to the yum.conf
  # Setting proxy here will be the default for all repos.
  #
  #  If you wish to set a proxy for an individual set of repos,
  #   you can declare $proxy in that class, and should scope to
  #   the most specific declaration of proxy.
  $proxy = 'absent'

  if getvar('::operatingsystemmajrelease') {
    $os_maj_release = $::operatingsystemmajrelease
  } else {
    $os_versions    = split("${::operatingsystemrelease}", '[.]') # lint:ignore:only_variable_string
    $os_maj_release = $os_versions[0]
  }

  if versioncmp($os_maj_release, '5') > 0 {
      $url_path = 'metalink'
  } else {
      $url_path = 'mirrorlist'
  }

  $epel_mirrorlist                        = "https://mirrors.fedoraproject.org/${url_path}?repo=epel-${os_maj_release}&arch=\$basearch"
  $epel_baseurl                           = 'absent'
  $epel_failovermethod                    = 'priority'
  $epel_proxy                             = $proxy
  $epel_enabled                           = '1'
  $epel_gpgcheck                          = '1'
  $epel_testing_mirrorlist                = "https://mirrors.fedoraproject.org/${url_path}?repo=testing-epel${os_maj_release}&arch=\$basearch"
  $epel_testing_baseurl                   = 'absent'
  $epel_managed                           = true
  $epel_testing_failovermethod            = 'priority'
  $epel_testing_proxy                     = $proxy
  $epel_testing_enabled                   = '0'
  $epel_testing_gpgcheck                  = '1'
  $epel_source_mirrorlist                 = "https://mirrors.fedoraproject.org/${url_path}?repo=epel-source-${os_maj_release}&arch=\$basearch"
  $epel_testing_managed                   = true
  $epel_source_baseurl                    = 'absent'
  $epel_source_failovermethod             = 'priority'
  $epel_source_proxy                      = $proxy
  $epel_source_enabled                    = '0'
  $epel_source_gpgcheck                   = '1'
  $epel_debuginfo_mirrorlist              = "https://mirrors.fedoraproject.org/${url_path}?repo=epel-debug-${os_maj_release}&arch=\$basearch"
  $epel_source_managed                    = true
  $epel_debuginfo_baseurl                 = 'absent'
  $epel_debuginfo_failovermethod          = 'priority'
  $epel_debuginfo_proxy                   = $proxy
  $epel_debuginfo_enabled                 = '0'
  $epel_debuginfo_gpgcheck                = '1'
  $epel_testing_source_mirrorlist         = "https://mirrors.fedoraproject.org/${url_path}?repo=testing-source-epel${os_maj_release}&arch=\$basearch"
  $epel_testing_source_baseurl            = 'absent'
  $epel_debuginfo_managed                 = true
  $epel_testing_source_failovermethod     = 'priority'
  $epel_testing_source_proxy              = $proxy
  $epel_testing_source_enabled            = '0'
  $epel_testing_source_gpgcheck           = '1'
  $epel_testing_debuginfo_mirrorlist      = "https://mirrors.fedoraproject.org/${url_path}?repo=testing-debug-epel${os_maj_release}&arch=\$basearch"
  $epel_testing_debuginfo_baseurl         = 'absent'
  $epel_testing_source_managed            = true
  $epel_testing_debuginfo_failovermethod  = 'priority'
  $epel_testing_debuginfo_proxy           = $proxy
  $epel_testing_debuginfo_enabled         = '0'
  $epel_testing_debuginfo_gpgcheck        = '1'
  $epel_testing_debuginfo_managed         = true
  $epel_gpg_managed                       = true
}
