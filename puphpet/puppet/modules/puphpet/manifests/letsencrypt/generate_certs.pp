# == Define Resource Type: puphpet::letsencrypt::generate_certs
#
# Generates SSL certificates using Let's Encrypt certbot-auto tool
#
define puphpet::letsencrypt::generate_certs (
  $webserver_service,
  $domains = $::puphpet::letsencrypt::params::domains
){

  include puphpet::letsencrypt::params

  $pre_hook = $webserver_service ? {
    false   => '',
    default => "service ${webserver_service} stop || true"
  }

  $post_hook = $webserver_service ? {
    false   => '',
    default => "service ${webserver_service} start || true"
  }

  $cmd_base = join([
    $puphpet::letsencrypt::params::certbot,
    'certonly',
    '--agree-tos',
    '--keep-until-expiring',
    '--standalone',
    '--standalone-supported-challenges http-01',
    '--noninteractive',
    "--email '${puphpet::params::hiera['letsencrypt']['settings']['email']}'",
  ], ' ')

  each( $domains ) |$key, $domain| {
    $hosts = array_true($domain, 'hosts') ? {
      true    => join($domain['hosts'], ' -d '),
      default => $domain
    }

    $first_host = array_true($domain, 'hosts') ? {
      true    => $domain['hosts'][0],
      default => $domain
    }

    $privkey_pem   = "/etc/letsencrypt/live/${first_host}/privkey.pem"
    $fullchain_pem = "/etc/letsencrypt/live/${first_host}/fullchain.pem"
    $combined_pem  = "/etc/letsencrypt/combined/${first_host}.pem"

    $cmd_combined_dir = 'mkdir -p /etc/letsencrypt/combined'
    $cmd_combine = "/bin/cat ${privkey_pem} ${fullchain_pem} | /usr/bin/tee ${combined_pem} > /dev/null && /bin/chmod 700 ${combined_pem}"
    $cmd_final   = "${cmd_base} --pre-hook '${pre_hook}' --post-hook '${cmd_combined_dir} && ${cmd_combine} && ${post_hook}' -d ${hosts}"

    $hour   = seeded_rand(23, $::fqdn)
    $minute = seeded_rand(59, $::fqdn)

    exec { "generate ssl cert for ${first_host}":
      command => $cmd_final,
      creates => $fullchain_pem,
      group   => 'root',
      user    => 'root',
      path    => [ '/bin', '/sbin/', '/usr/sbin/', '/usr/bin' ],
      require => [
        Class['Puphpet::Letsencrypt::Certbot'],
        Puphpet::Firewall::Port['80'],
      ],
    }

    if ! defined(File['/etc/letsencrypt/combined']) {
      file { '/etc/letsencrypt/combined':
        ensure  => directory,
        require => Exec["generate ssl cert for ${first_host}"]
      }
    }

    exec { "generate combined ssl cert for ${first_host}":
      command => $cmd_combine,
      creates => $combined_pem,
      group   => 'root',
      user    => 'root',
      path    => [ '/bin', '/sbin/', '/usr/sbin/', '/usr/bin' ],
      require => [
        File['/etc/letsencrypt/combined'],
        Exec["generate ssl cert for ${first_host}"],
      ],
    }

    cron { "letsencrypt cron for ${first_host}":
       command  => $cmd_final,
       minute   => "${minute}",
       hour     => "${hour}",
       weekday  => '*',
       month    => '*',
       monthday => '*',
       user     => 'root',
    }
  }

}
