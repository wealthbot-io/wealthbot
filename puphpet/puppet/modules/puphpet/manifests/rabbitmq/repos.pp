class puphpet::rabbitmq::repos {

  include ::rabbitmq::params
  include ::puphpet::rabbitmq::params

  case $::osfamily {
    'RedHat', 'SUSE': {
      Class['::puphpet::rabbitmq::repos']
      -> Package <| title == 'rabbitmq-server' |>

      exec { "rpm --import ${::rabbitmq::package_gpg_key}":
        path   => ['/bin','/usr/bin','/sbin','/usr/sbin'],
        unless => 'rpm -q gpg-pubkey-6026dfca-573adfde 2>/dev/null',
      }
    }
    'Debian': {
      $osname = downcase($facts['os']['name'])
      apt::source { 'rabbitmq':
        ensure   => present,
        location => "${puphpet::rabbitmq::params::repo_location}/${osname}",
        repos    => 'main',
        include  => { 'src' => false },
        key      => {
          'id'      => $puphpet::rabbitmq::params::gpg_key,
          'source'  => $::rabbitmq::package_gpg_key,
          'content' => $::rabbitmq::key_content,
        },
      }
    }
  }

}
