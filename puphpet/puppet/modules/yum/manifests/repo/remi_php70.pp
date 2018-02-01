# = Class: yum::repo::remi_php70
#
# This class installs the remi-php70 repo
#
class yum::repo::remi_php70 {
  $releasever = $::operatingsystem ? {
    /(?i:Amazon)/ => '6',
    default       => '$releasever',  # Yum var
  }

  yum::managed_yumrepo { 'remi-php70':
    descr      => 'Remi\'s PHP 7.0 RPM repository for Enterprise Linux $releasever - $basearch',
    mirrorlist => "http://rpms.remirepo.net/enterprise/${releasever}/php70/mirror",
    enabled    => 1,
    gpgcheck   => 1,
    gpgkey     => 'http://rpms.remirepo.net/RPM-GPG-KEY-remi',
    priority   => 1
  }
}
