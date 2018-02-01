#
class rabbitmq::install::rabbitmqadmin {

  if $rabbitmq::rabbitmqadmin_package {
    package{'rabbitmqadmin':
      ensure => 'present',
      name   => $rabbitmq::rabbitmqadmin_package,
    }
  } else {

    $python_package = $rabbitmq::params::python_package
    # Some systems (e.g., Ubuntu 16.04) don't ship Python 2 by default
    if $rabbitmq::manage_python {
      ensure_packages([$python_package])
      $rabbitmqadmin_require = [Archive['rabbitmqadmin'], Package[$python_package]]
    } else {
      $rabbitmqadmin_require = Archive['rabbitmqadmin']
    }

    if($rabbitmq::ssl and $rabbitmq::management_ssl) {
      $management_port = $rabbitmq::ssl_management_port
      $protocol        = 'https'
    } else {
      $management_port = $rabbitmq::management_port
      $protocol        = 'http'
    }

    $default_user = $rabbitmq::default_user
    $default_pass = $rabbitmq::default_pass
    $management_ip_address = $rabbitmq::management_ip_address

    if !($management_ip_address) {
      # Pull from localhost if we don't have an explicit bind address
      $curl_prefix = ''
      $sanitized_ip = '127.0.0.1'
    } elsif is_ipv6_address($management_ip_address) {
      $curl_prefix  = "--noproxy ${management_ip_address} -g -6"
      $sanitized_ip = join(enclose_ipv6(any2array($management_ip_address)), ',')
    } else {
      $curl_prefix  = "--noproxy ${management_ip_address}"
      $sanitized_ip = $management_ip_address
    }

    archive { 'rabbitmqadmin':
      path           => "${rabbitmq::rabbitmq_home}/rabbitmqadmin",
      source         => "${protocol}://${sanitized_ip}:${management_port}/cli/rabbitmqadmin",
      username       => $default_user,
      password       => $default_pass,
      allow_insecure => true,
      cleanup        => false,
      require        => [
        Class['rabbitmq::service'],
        Rabbitmq_plugin['rabbitmq_management']
      ],
    }

    file { '/usr/local/bin/rabbitmqadmin':
      owner   => 'root',
      group   => '0',
      source  => "${rabbitmq::rabbitmq_home}/rabbitmqadmin",
      mode    => '0755',
      require => $rabbitmqadmin_require,
    }
  }
}
