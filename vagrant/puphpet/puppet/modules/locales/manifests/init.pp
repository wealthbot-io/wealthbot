class locales(
  $available     = ['en_US.UTF-8 UTF-8'],
  $default_value = 'en_US.UTF-8',
) {
  package { 'locales':
    ensure => present,
  }

  case $::operatingsystem {
    ubuntu: { $localegenfile = '/var/lib/locales/supported.d/local' }
    default: { $localegenfile = '/etc/locale.gen' }
  }

  file { $localegenfile:
    content => inline_template('<%= @available.join("\n") + "\n" %>'),
  }

  file { '/etc/default/locale':
    content => template('locales/default_locales.erb')
  }

  exec { '/usr/sbin/locale-gen':
    subscribe   => [File[$localegenfile], File['/etc/default/locale']],
    refreshonly => true,
  }

  exec { '/usr/sbin/update-locale':
    subscribe   => [File[$localegenfile], File['/etc/default/locale']],
    refreshonly => true,
  }

  Package[locales] -> File[$localegenfile] -> File['/etc/default/locale']
  -> Exec['/usr/sbin/locale-gen'] -> Exec['/usr/sbin/update-locale']
}
