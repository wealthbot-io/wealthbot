# Class supervisord::install
#
# Installs supervisor package (defaults to using pip)
#
class supervisord::install inherits supervisord {
  if $::supervisord::pip_proxy and $::supervisord::package_provider == 'pip' {
    exec { 'pip-install-supervisor':
      user        => root,
      path        => ['/usr/bin','/bin'],
      environment => [ "http_proxy=${supervisord::pip_proxy}", "https_proxy=${supervisord::pip_proxy}" ],
      command     => "pip install ${supervisord::package_name}",
      unless      => 'which supervisorctl',
    }
  }
  else {
    package { $supervisord::package_name:
      ensure          => $supervisord::package_ensure,
      provider        => $supervisord::package_provider,
      install_options => $supervisord::package_install_options,
    }
  }
}
