class puphpet::mysql::repo::debian (
  $version = $::puphpet::mysql::params::version
) {

  $os = downcase($::operatingsystem)

  if $version in ['56', '5.6', 56, 5.6] {
    $repo_version = '5.6'
    $dist         = $::lsbdistcodename ?{
      'xenial' => 'wily', # MySQL 5.6 not available for Xenial, have to use Wily
      default  => $::lsbdistcodename,
    }
  } else {
    $repo_version = '5.7'
    $dist         = $::lsbdistcodename
  }

  if ! defined(Apt::Key['A4A9406876FCBD3C456770C88C718D3B5072E1F5']) {
    apt::key { 'A4A9406876FCBD3C456770C88C718D3B5072E1F5':
      server => 'hkp://keyserver.ubuntu.com:80'
    }
  }

  apt::pin { 'mysql-pin':
    priority        => 1002,
    version         => "${repo_version}.*",
    packages        => [
      'mysql-client',
      'mysql-common',
      'mysql-server',
      'mysql-testsuite',
    ],
  }

  if ! defined(Apt::Source['mysql-apt-config']) {
    ::apt::source { 'repo.mysql.com-apt':
      location => 'http://repo.mysql.com/apt/ubuntu',
      release  => $dist,
      repos    => "mysql-${repo_version}",
      require  => [
        Apt::Pin['mysql-pin'],
        Apt::Key['A4A9406876FCBD3C456770C88C718D3B5072E1F5']
      ],
      before   => [
        Class['mysql::client'],
        Class['mysql::server'],
      ],
    }
  }

  if ! defined(Apt::Source['repo.mysql.com-apt']) {
    ::apt::source { 'repo.mysql.com-apt':
      location => 'http://repo.mysql.com/apt/ubuntu',
      release  => $dist,
      repos    => "mysql-${repo_version}",
      require  => [
        Apt::Pin['mysql-pin'],
        Apt::Key['A4A9406876FCBD3C456770C88C718D3B5072E1F5']
      ],
      before   => [
        Class['mysql::client'],
        Class['mysql::server'],
      ],
    }
  }

  if ! defined(Apt::Source['repo.mysql.com-apt']) {
    ::apt::source { 'repo.mysql.com-apt':
      location => 'http://repo.mysql.com/apt/ubuntu',
      release  => $dist,
      repos    => "mysql-${repo_version}",
      require  => [
        Apt::Pin['mysql-pin'],
        Apt::Key['A4A9406876FCBD3C456770C88C718D3B5072E1F5']
      ],
      before   => [
        Class['mysql::client'],
        Class['mysql::server'],
      ],
    }
  }

}
