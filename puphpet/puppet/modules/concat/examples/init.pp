concat { '/tmp/concat':
  ensure => present,
  owner  => 'root',
  group  => 'root',
  mode   => '0644',
}
