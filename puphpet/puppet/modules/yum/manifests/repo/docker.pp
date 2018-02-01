# = Class: yum::repo::docker
#
# This class installs the official Docker repo
#
class yum::repo::docker (
  $baseurl = undef,
  $stability = 'main',
) {
  case $::operatingsystem {
    'RedHat', 'CentOS', 'Scientific': {
      $os_release = 'centos'
    }
    default: {
      $os_release = $::operatingsystem
    }
  }

  if $baseurl {
    validate_re(
      $baseurl,
      '^(?:https?|ftp):\/\/[\da-zA-Z-][\da-zA-Z\.-]*\.[a-zA-Z]{2,6}\.?(?:\:[0-9]{1,5})?(?:\/[\w~-]*)*$',
      '$baseurl must be a Clean URL with no query-string, a fully-qualified hostname and no trailing slash.'
    )
    $baseurl_ensured = $baseurl
  } else {
    $baseurl_ensured = "https://yum.dockerproject.org/repo/${stability}/${os_release}/\$releasever"
  }

  yum::managed_yumrepo { 'docker':
    descr          => 'Docker repository',
    baseurl        => $baseurl_ensured,
    enabled        => 1,
    gpgcheck       => 1,
    failovermethod => 'priority',
    gpgkey         => 'https://yum.dockerproject.org/gpg',
    autokeyimport  => 'yes',
    priority       => 5,
  }

}
