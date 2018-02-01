# = Class: yum::repo::remi_php56
#
# This class installs the remi-php56 repo
#
class yum::repo::remi_php56 {
  $releasever = $::operatingsystem ? {
    /(?i:Amazon)/ => '6',
    default       => '$releasever',  # Yum var
  }

  yum::managed_yumrepo { 'remi-php56':
    descr      => 'Remi\'s PHP 5.6 RPM repository for Enterprise Linux $releasever - $basearch',
    mirrorlist => "http://rpms.remirepo.net/enterprise/${releasever}/php56/mirror",
    enabled    => 1,
    gpgcheck   => 1,
    gpgkey     => 'http://rpms.remirepo.net/RPM-GPG-KEY-remi',
    priority   => 1
  }
}
