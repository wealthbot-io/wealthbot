# == Definition: archive::download
#
# Archive downloader with integrity verification.
#
# Parameters:
#
# - *$url:
# - *$digest_url:
# - *$digest_string: Default value undef
# - *$digest_type: Default value "md5".
# - *$timeout: Default value 120. (ignored)
# - *$src_target: Default value "/usr/src".
# - *$allow_insecure: Default value false.
# - *$follow_redirects: Default value false.
# - *$verbose: Default value true.
# - *$proxy_server: Default value undef.
# - *$user: The user used to download the archive
#
# Example usage:
#
#  archive::download {"apache-tomcat-6.0.26.tar.gz":
#    ensure => present,
#    url    => "http://archive.apache.org/dist/tomcat/tomcat-6/v6.0.26/bin/apache-tomcat-6.0.26.tar.gz",
#  }
#
#  archive::download {"apache-tomcat-6.0.26.tar.gz":
#    ensure        => present,
#    digest_string => "f9eafa9bfd620324d1270ae8f09a8c89",
#    url           => "http://archive.apache.org/dist/tomcat/tomcat-6/v6.0.26/bin/apache-tomcat-6.0.26.tar.gz",
#  }
#
define archive::download (
  String                        $url,
  Enum['present', 'absent']     $ensure  = present,
  Boolean                       $checksum         = true,
  Optional[String]              $digest_url       = undef,
  Optional[String]              $digest_string    = undef,
  Optional[Enum['none', 'md5', 'sha1', 'sha2','sh256', 'sha384', 'sha512']] $digest_type      = 'md5',   # bad default!
  Integer                       $timeout          = 120,     # ignored
  Stdlib::Compat::Absolute_path $src_target       = '/usr/src',
  Boolean                       $allow_insecure   = false,
  Boolean                       $follow_redirects = false,   # ignored (default)
  Boolean                       $verbose          = true,    # ignored
  String                        $path             = $::path, # ignored
  Optional[String]              $proxy_server     = undef,
  Optional[String]              $user             = undef,
) {
  $target = ($title =~ Stdlib::Compat::Absolute_path) ? {
    false   => "${src_target}/${title}",
    default => $title,
  }

  archive { $target:
    ensure          => $ensure,
    source          => $url,
    checksum_verify => $checksum,
    checksum        => $digest_string,
    checksum_type   => $digest_type,
    checksum_url    => $digest_url,
    proxy_server    => $proxy_server,
    user            => $user,
    allow_insecure  => $allow_insecure,
  }
}
