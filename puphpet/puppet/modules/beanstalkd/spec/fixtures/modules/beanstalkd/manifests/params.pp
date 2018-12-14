class beanstalkd::params {
  $defaultpackagename = 'beanstalkd'
  $defaultservicename = 'beanstalkd'
  $user               = 'beanstalkd'
  $hasstatus          = 'true'

  if $::osfamily == 'Debian' {
    if ($::operatingsystem == 'Ubuntu' and versioncmp($::operatingsystemrelease, '16') >= 0)
      or ($::operatingsystem == 'Debian' and versioncmp($::operatingsystemrelease, '8') >= 0)
    {
      $mode                = '0644'

      $configfile          = '/etc/default/beanstalkd'
      $configtemplate      = "${module_name}/debian/beanstalkd_sysconfig.erb"

      $servicefile         = '/lib/systemd/system/beanstalkd.service'
      $servicefiletemplate = "${module_name}/debian/beanstalkd_service.erb"

      $reloadconfig        = '/bin/systemctl daemon-reload'
      $restart             = '/bin/systemctl restart beanstalkd'
    } else {
      $mode                = '0755'

      $configfile          = '/etc/init.d/beanstalkd'
      $configtemplate      = "${module_name}/debian/beanstalkd.erb"

      $servicefile         = undef
      $servicefiletemplate = undef

      $reload              = undef
      $restart             = '/etc/init.d/beanstalkd restart'
    }
  }

  elsif $::osfamily == 'RedHat' {
    $osr_array = split($::operatingsystemrelease,'[\/\.]')
    $distrelease = $osr_array[0]

    $mode                = '0644'

    $configfile          = '/etc/sysconfig/beanstalkd'
    $configtemplate      = "${module_name}/redhat/beanstalkd_sysconfig.erb"

    if versioncmp($distrelease, '7') >= 0 {
      $servicefile         = '/usr/lib/systemd/system/beanstalkd.service'
      $servicefiletemplate = "${module_name}/redhat/beanstalkd_service.erb"

      $reloadconfig        = '/bin/systemctl daemon-reload'
      $restart             = '/bin/systemctl restart beanstalkd'
    } else {
      $servicefile         = undef
      $servicefiletemplate = undef

      $reloadconfig        = undef
      $restart             = '/etc/init.d/beanstalkd restart'
    }
  }

  else {
    # TODO: add more OS support!
    fail("ERROR [${module_name}]: I don't know how to manage this OS: ${::operatingsystem}")
  }
}
