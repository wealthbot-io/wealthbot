# = Class: yum::repo::remi_php55
#
# This class installs the remi-php55 repo
#
class yum::repo::remi_php55 {
  $releasever = $::operatingsystem ? {
    /(?i:Amazon)/ => '6',
    default       => '$releasever',  # Yum var
  }

  yum::managed_yumrepo { 'remi-php55':
    descr      => 'Remi\'s PHP 5.5 RPM repository for Enterprise Linux $releasever - $basearch',
    mirrorlist => "http://rpms.remirepo.net/enterprise/${releasever}/php55/mirror",
    enabled    => 1,
    gpgcheck   => 1,
    gpgkey     => 'http://rpms.remirepo.net/RPM-GPG-KEY-remi',
    priority   => 1
  }
}
