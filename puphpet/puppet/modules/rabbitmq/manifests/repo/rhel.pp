# Class: rabbitmq::repo::rhel
# Makes sure that the Packagecloud repo is installed
class rabbitmq::repo::rhel(
    $location          = "https://packagecloud.io/rabbitmq/rabbitmq-server/el/${facts['os'][release][major]}/\$basearch",
    String $key_source = $rabbitmq::package_gpg_key,
  ) {

  Class['rabbitmq::repo::rhel'] -> Package<| title == 'rabbitmq-server' |>

  yumrepo { 'rabbitmq':
    ensure   => present,
    name     => 'rabbitmq_rabbitmq-server',
    baseurl  => $location,
    gpgkey   => $key_source,
    enabled  => 1,
    gpgcheck => 1,
  }

  # This may still be needed to prevent warnings
  # packagecloud key is gpg-pubkey-d59097ab-52d46e88
  exec { "rpm --import ${key_source}":
    path   => ['/bin','/usr/bin','/sbin','/usr/sbin'],
    unless => 'rpm -q gpg-pubkey-6026dfca-573adfde 2>/dev/null',
  }
}
