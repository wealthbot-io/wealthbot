# = Class: yum::repo::remi_safe
#
# This class installs the remi safe repo
#
class yum::repo::remi_safe {
  $releasever = $::operatingsystem ? {
    /(?i:Amazon)/ => '6',
    default       => '$releasever',  # Yum var
  }

  yum::managed_yumrepo { 'remi-safe':
    descr      => 'Safe Remi\'s RPM repository for Enterprise Linux $releasever - $basearch',
    mirrorlist => "http://rpms.remirepo.net/enterprise/${releasever}/safe/mirror",
    enabled    => 1,
    gpgcheck   => 1,
    gpgkey     => 'http://rpms.remirepo.net/RPM-GPG-KEY-remi',
    priority   => 1
  }
}
