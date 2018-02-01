# = Class: yum::repo::rpmforge
#
# This class installs the rpmforge repo
#
class yum::repo::rpmforge {
$osver = split($::operatingsystemrelease, '[.]')
  case $osver[0] {
    '7': {
      $baseurl = 'http://mirrorlist.repoforge.org/el7/$basearch/rpmforge'
      $mirrorlist = 'http://mirrorlist.repoforge.org/el7/mirrors-rpmforge'
    }
    '6': {
      $baseurl = 'http://mirrorlist.repoforge.org/el6/$basearch/rpmforge'
      $mirrorlist = 'http://mirrorlist.repoforge.org/el6/mirrors-rpmforge'
    }
    '5': {
      $baseurl = 'http://mirrorlist.repoforge.org/el5/$basearch/rpmforge'
      $mirrorlist = 'http://mirrorlist.repoforge.org/el5/mirrors-rpmforge'
    }
    default: { fail('Unsupported version of Enterprise Linux') }
  }
  yum::managed_yumrepo { 'rpmforge':
    baseurl       => $baseurl,
    mirrorlist    => $mirrorlist,
    descr         => 'RHEL $releasever - RPMforge.net - dag',
    enabled       => 1,
    gpgcheck      => 1,
    gpgkey        => 'file:///etc/pki/rpm-gpg/RPM-GPG-KEY-rpmforge-dag',
    gpgkey_source => 'puppet:///modules/yum/rpm-gpg/RPM-GPG-KEY-rpmforge-dag',
    priority      => 30,
  }

}

