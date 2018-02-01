# requires
#   puppetlabs-apt
#   puppetlabs-stdlib
class rabbitmq::repo::apt(
  String $location               = 'https://packagecloud.io/rabbitmq/rabbitmq-server',
  String $repos                  = 'main',
  Boolean $include_src           = false,
  String $key                    = '418A7F2FB0E1E6E7EABF6FE8C2E73424D59097AB',
  String $key_source             = $rabbitmq::package_gpg_key,
  Optional[String] $key_content  = $rabbitmq::key_content,
  Optional[String] $architecture = undef,
  ) {

  $pin = $rabbitmq::package_apt_pin

  # ordering / ensure to get the last version of repository
  Class['rabbitmq::repo::apt']
  -> Class['apt::update']
  -> Package<| title == 'rabbitmq-server' |>

  $osname = downcase($facts['os']['name'])
  apt::source { 'rabbitmq':
    ensure       => present,
    location     => "${location}/${osname}",
    repos        => $repos,
    include      => { 'src' => $include_src },
    key          => {
      'id'      => $key,
      'source'  => $key_source,
      'content' =>  $key_content,
    },
    architecture => $architecture,
  }

  if $pin {
    apt::pin { 'rabbitmq':
      packages => '*',
      priority => $pin,
      origin   => 'packagecloud.io',
    }
  }
}
