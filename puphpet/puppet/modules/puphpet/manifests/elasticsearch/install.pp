# == Class: puphpet::elasticsearch::install
#
# Installs Elasticsearch engine.
# Installs Java and opens ports
#
# Usage:
#
#  class { 'puphpet::elasticsearch::install': }
#
class puphpet::elasticsearch::install
  inherits puphpet::params
{

  $elasticsearch = $puphpet::params::hiera['elasticsearch']

  if ! defined(Puphpet::Firewall::Port['9200']) {
    puphpet::firewall::port { '9200': }
  }

  $settings = $elasticsearch['settings']

  if ! defined(Class['java']) and $settings['java_install'] {
    class { 'java':
      distribution => 'jre',
    }
  }

  if array_true($settings, 'version') {
    $version = "${settings['version']}"
  } else {
    $version      = false
    $repo_version = '6.x'
  }

  if $version {
    if versioncmp($version, '6') >= 0 {
      $repo_version = '6.x'
    }
    elsif versioncmp($version, '5') >= 0 {
      $repo_version = '5.x'
    }
    elsif versioncmp($version, '2.4') >= 0 {
      $repo_version = '2.4'
    }
    elsif versioncmp($version, '2.3') >= 0 {
      $repo_version = '2.3'
    }
    elsif versioncmp($version, '2.2') >= 0 {
      $repo_version = '2.2'
    }
    elsif versioncmp($version, '2.1') >= 0 {
      $repo_version = '2.1'
    }
    elsif versioncmp($version, '2.0') >= 0 {
      $repo_version = '2.0'
    }
  }

  $merged = delete(merge($settings, {
    'manage_repo'  => true,
    'version'      => $version,
    'repo_version' => $repo_version,
  }), ['java_install'])

  create_resources('class', { 'elasticsearch' => $merged })

  # config file could contain no instance keys
  $instances = array_true($elasticsearch, 'instances') ? {
    true    => $elasticsearch['instances'],
    default => { }
  }

  each( $instances ) |$key, $instance| {
    $name = $instance['name']

    create_resources( elasticsearch::instance, { "${name}" => $instance })
  }

}
