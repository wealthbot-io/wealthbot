class { 'resolvconf':
  nameserver => [ '8.8.8.8', '8.8.4.4' ],
  search     => [ 'example.lan', 'example.com' ],
}
