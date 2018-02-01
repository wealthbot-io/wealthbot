# Define: archive::artifactory
# ============================
#
# archive wrapper for downloading files from artifactory
#
# Parameters
# ----------
#
# * path: fully qualified filepath for the download the file or use archive_path and only supply filename. (namevar).
# * ensure: ensure the file is present/absent.
# * url: artifactory download url filepath. NOTE: replaces server, port, url_path parameters.
# * server: artifactory server name (deprecated).
# * port: artifactory server port (deprecated).
# * url_path: artifactory file path http://{server}:{port}/artifactory/{url_path} (deprecated).
# * owner: file owner (see archive params for defaults).
# * group: file group (see archive params for defaults).
# * mode: file mode (see archive params for defaults).
# * archive_path: the parent directory of local filepath.
# * extract: whether to extract the files (true/false).
# * creates: the file created when the archive is extracted (true/false).
# * cleanup: remove archive file after file extraction (true/false).
#
# Examples
# --------
#
# archive::artifactory { '/tmp/logo.png':
#   url   => 'https://repo.jfrog.org/artifactory/distributions/images/Artifactory_120x75.png',
#   owner => 'root',
#   group => 'root',
#   mode  => '0644',
# }
#
# $dirname = 'gradle-1.0-milestone-4-20110723151213+0300'
# $filename = "${dirname}-bin.zip"
#
# archive::artifactory { $filename:
#   archive_path => '/tmp',
#   url          => "http://repo.jfrog.org/artifactory/distributions/org/gradle/${filename}",
#   extract      => true,
#   extract_path => '/opt',
#   creates      => "/opt/${dirname}",
#   cleanup      => true,
# }
#
define archive::artifactory (
  String                                  $path         = $name,
  Enum['present', 'absent']               $ensure       = present,
  Optional[Pattern[/^https?:\/\//]]       $url          = undef,
  Optional[String]                        $server       = undef,
  Optional[Integer]                       $port         = undef,
  Optional[String]                        $url_path     = undef,
  Optional[String]                        $owner        = undef,
  Optional[String]                        $group        = undef,
  Optional[String]                        $mode         = undef,
  Optional[Boolean]                       $extract      = undef,
  Optional[String]                        $extract_path = undef,
  Optional[String]                        $creates      = undef,
  Optional[Boolean]                       $cleanup      = undef,
  Optional[Stdlib::Compat::Absolute_path] $archive_path = undef,
) {

  include ::archive::params

  if $archive_path {
    $file_path = "${archive_path}/${name}"
  } else {
    $file_path = $path
  }

  if $file_path !~ Stdlib::Compat::Absolute_path {
    fail("archive::artifactory[${name}]: \$name or \$archive_path must be an absolute path!") # lint:ignore:trailing_comma
  }

  if $url {
    $file_url = $url
    $sha1_url = regsubst($url, '/artifactory/', '/artifactory/api/storage/')
  } elsif $server and $port and $url_path {
    warning('archive::artifactory attribute: server, port, url_path are deprecated')
    $art_url = "http://${server}:${port}/artifactory"
    $file_url = "${art_url}/${url_path}"
    $sha1_url = "${art_url}/api/storage/${url_path}"
  } else {
    fail('Please provide fully qualified url path for artifactory file.')
  }

  archive { $file_path:
    ensure        => $ensure,
    path          => $file_path,
    extract       => $extract,
    extract_path  => $extract_path,
    source        => $file_url,
    checksum      => artifactory_sha1($sha1_url),
    checksum_type => 'sha1',
    creates       => $creates,
    cleanup       => $cleanup,
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
