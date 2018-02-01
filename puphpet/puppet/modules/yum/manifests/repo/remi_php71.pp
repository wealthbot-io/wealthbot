# = Class: yum::repo::remi_php71
#
# This class installs the remi-php71 repo
#
class yum::repo::remi_php71 {
  $releasever = $::operatingsystem ? {
    /(?i:Amazon)/ => '6',
    default       => '$releasever',  # Yum var
  }

  yum::managed_yumrepo { 'remi-php71':
    descr      => 'Remi\'s PHP 7.1 RPM repository for Enterprise Linux $releasever - $basearch',
    mirrorlist => "http://rpms.remirepo.net/enterprise/${releasever}/php71/mirror",
    enabled    => 1,
    gpgcheck   => 1,
    gpgkey     => 'http://rpms.remirepo.net/RPM-GPG-KEY-remi',
    priority   => 1
  }
}
