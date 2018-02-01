# download from go
define archive::go (
  String                    $server,
  Integer                   $port,
  String                    $url_path,
  String                    $md5_url_path,
  String                    $username,
  String                    $password,
  Enum['present', 'absent'] $ensure       = present,
  String                    $path         = $name,
  Optional[String]          $owner        = undef,
  Optional[String]          $group        = undef,
  Optional[String]          $mode         = undef,
  Optional[Boolean]         $extract      = undef,
  Optional[String]          $extract_path = undef,
  Optional[String]          $creates      = undef,
  Optional[Boolean]         $cleanup      = undef,
  Optional[Stdlib::Compat::Absolute_path] $archive_path = undef,
) {

  include ::archive::params

  if $archive_path {
    $file_path = "${archive_path}/${name}"
  } else {
    $file_path = $path
  }

  if $file_path !~ Stdlib::Compat::Absolute_path {
    fail("archive::go[${name}]: \$name or \$archive_path must be an absolute path!") # lint:ignore:trailing_comma
  }

  $go_url = "http://${server}:${port}"
  $file_url = "${go_url}/${url_path}"
  $md5_url = "${go_url}/${md5_url_path}"

  archive { $file_path:
    ensure        => $ensure,
    path          => $file_path,
    extract       => $extract,
    extract_path  => $extract_path,
    source        => $file_url,
    checksum      => go_md5($username, $password, $name, $md5_url),
    checksum_type => 'md5',
    creates       => $creates,
    cleanup       => $cleanup,
    username      => $username,
    password      => $password,
  }

  $file_owner = pick($owner, $archive::params::owner)
  $file_group = pick($group, $archive::params::group)
  $file_mode  = pick($mode, $archive::params::mode)

  file { $file_path:
    owner   => $file_owner,
    group   => $file_group,
    mode    => $file_mode,
    require => Archive[$file_path],
  }
}
