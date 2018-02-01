concat { 'testconcat':
  ensure    => present,
  path      => '/tmp/concat',
  owner     => 'root',
  group     => 'root',
  mode      => '0664',
  show_diff => true,
}

concat::fragment { '1':
  target  => 'testconcat',
  content => '1',
  order   => '01',
}

concat::fragment { '2':
  target  => 'testconcat',
  content => '2',
  order   => '02',
}
