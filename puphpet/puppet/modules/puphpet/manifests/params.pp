class puphpet::params (
  $extra_config_files = []
) {

  $puphpet_core_dir  = pick(getvar('::puphpet_core_dir'), '/opt/puphpet')
  $puphpet_state_dir = pick(getvar('::puphpet_state_dir'), '/opt/puphpet-state')
  $ssh_username      = pick(getvar('::ssh_username'), 'root')
  $provisioner_type  = pick(getvar('::provisioner_type'), 'remote')

  $puphpet_manifest_dir = "${puphpet_core_dir}/puppet/modules/puphpet"

  $base_configs = [
    "${puphpet_core_dir}/config.yaml",
    "${puphpet_core_dir}/config-${provisioner_type}.yaml",
  ]

  $custom_config = ["${puphpet_core_dir}/config-custom.yaml"]

  $yaml = merge_yaml($base_configs, $extra_config_files, $custom_config)

  $strategy = {
    'strategy'          => 'deep',
    'merge_hash_arrays' => true
  }

  $hiera = {
    vm             => lookup('vagrantfile',     Hash, $strategy, {}),
    apache         => $yaml['apache'],
    beanstalkd     => lookup('beanstalkd',      Hash, $strategy, {}),
    blackfire      => lookup('blackfire',       Hash, $strategy, {}),
    cron           => lookup('cron',            Hash, $strategy, {}),
    drush          => lookup('drush',           Hash, $strategy, {}),
    elasticsearch  => lookup('elastic_search',  Hash, $strategy, {}),
    firewall       => lookup('firewall',        Hash, $strategy, {}),
    letsencrypt    => lookup('letsencrypt',     Hash, $strategy, {}),
    locales        => lookup('locale',          Hash, $strategy, {}),
    mailhog        => lookup('mailhog',         Hash, $strategy, {}),
    mariadb        => lookup('mariadb',         Hash, $strategy, {}),
    mongodb        => lookup('mongodb',         Hash, $strategy, {}),
    mysql          => lookup('mysql',           Hash, $strategy, {}),
    nginx          => $yaml['nginx'],
    nodejs         => lookup('nodejs',          Hash, $strategy, {}),
    php            => lookup('php',             Hash, $strategy, {}),
    postgresql     => lookup('postgresql',      Hash, $strategy, {}),
    python         => lookup('python',          Hash, $strategy, {}),
    rabbitmq       => lookup('rabbitmq',        Hash, $strategy, {}),
    redis          => lookup('redis',           Hash, $strategy, {}),
    resolv         => lookup('resolv',          Hash, $strategy, {}),
    ruby           => lookup('ruby',            Hash, $strategy, {}),
    server         => lookup('server',          Hash, $strategy, {}),
    sqlite         => lookup('sqlite',          Hash, $strategy, {}),
    users_groups   => lookup('users_groups',    Hash, $strategy, {}),
    wpcli          => lookup('wpcli',           Hash, $strategy, {}),
    xdebug         => lookup('xdebug',          Hash, $strategy, {}),
  }

}
