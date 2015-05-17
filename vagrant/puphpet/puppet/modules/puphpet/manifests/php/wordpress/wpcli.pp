# This depends on puppetlabs-vcsrepo: https://github.com/puppetlabs/puppetlabs-vcsrepo.git
# This depends on puppet-composer: https://github.com/tPl0ch/puppet-composer.git
# Installs WPCLI system-wide
class puphpet::php::wordpress::wpcli (
  $version
) {

  if $wpcli_values['version'] != undef
    and (hash_key_equals($php_values, 'install', 1)
          or hash_key_equals($hhvm_values, 'install', 1))
    and (hash_key_equals($php_values, 'composer', 1)
          or hash_key_equals($hhvm_values, 'composer', 1))
  {
    $wpcli_github   = 'https://github.com/wp-cli/wp-cli.git'
    $wpcli_location = '/usr/share/wp-cli'

    exec { 'delete-wpcli-path-if-not-git-repo':
      command => "rm -rf ${wpcli_location}",
      onlyif  => "test ! -d ${wpcli_location}/.git",
      path    => [ '/bin/', '/sbin/', '/usr/bin/', '/usr/sbin/' ],
    } ->
    vcsrepo { $wpcli_location:
      ensure   => present,
      provider => git,
      source   => $wpcli_github,
      revision => $wpcli_values['version'],
    } ->
    composer::exec { 'wp-cli':
      cmd     => 'install',
      cwd     => $wpcli_location,
      require => Vcsrepo[$wpcli_location],
    } ->
    file { "${wpcli_location}/bin/wp":
      ensure => present,
      mode   => '+x',
    }
    file { 'symlink wp-cli':
      ensure => link,
      path   => '/usr/bin/wp',
      mode   => '0766',
      target => "${wpcli_location}/bin/wp",
    }
  }

}
