# define: archive::nexus
# ======================
#
# archive wrapper for downloading files from Nexus using REST API. Nexus API:
# https://repository.sonatype.org/nexus-restlet1x-plugin/default/docs/path__artifact_maven_content.html
#
# Parameters
# ----------
#
# Examples
# --------
#
# archive::nexus { '/tmp/jtstand-ui-0.98.jar':
#   url        => 'https://oss.sonatype.org',
#   gav        => 'org.codehaus.jtstand:jtstand-ui:0.98',
#   repository => 'codehaus-releases',
#   packaging  => 'jar',
#   extract    => false,
# }
#
define archive::nexus (
  String            $url,
  String            $gav,
  String            $repository,
  Enum['present', 'absent'] $ensure  = present,
  Enum['none', 'md5', 'sha1', 'sha2','sh256', 'sha384', 'sha512'] $checksum_type   = 'md5',
  Boolean           $checksum_verify = true,
  String            $packaging       = 'jar',
  Boolean           $use_nexus3_urls = false,
  Optional[String]  $classifier      = undef,
  Optional[String]  $extension       = undef,
  Optional[String]  $username        = undef,
  Optional[String]  $password        = undef,
  Optional[String]  $user            = undef,
  Optional[String]  $owner           = undef,
  Optional[String]  $group           = undef,
  Optional[String]  $mode            = undef,
  Optional[Boolean] $extract         = undef,
  Optional[String]  $extract_path    = undef,
  Optional[String]  $extract_flags   = undef,
  Optional[String]  $extract_command = undef,
  Optional[String]  $creates         = undef,
  Optional[Boolean] $cleanup         = undef,
  Optional[String]  $proxy_server    = undef,
  Optional[String]  $proxy_type      = undef,
  Optional[Boolean] $allow_insecure  = undef,
) {

  include ::archive::params

  $artifact_info = split($gav, ':')

  $group_id = $artifact_info[0]
  $artifact_id = $artifact_info[1]
  $version = $artifact_info[2]

  $query_params = {

    'g' => $group_id,
    'a' => $artifact_id,
    'v' => $version,
    'r' => $repository,
    'p' => $packaging,
    'c' => $classifier,
    'e' => $extension,

  }.filter |$keys, $values| { $values != undef }

  if $use_nexus3_urls {
    if $classifier {
      $c = "-${classifier}"
    } else {
      $c = ''
    }
    $artifact_url = sprintf('%s/repository/%s/%s/%s/%s/%s-%s%s.%s', $url,
                            $repository, regsubst($group_id, '\.', '/', 'G'), $artifact_id,
                            $version, $artifact_id, $version, $c, $packaging)
    $checksum_url = sprintf('%s.%s', $artifact_url, $checksum_type)
  } else {
    $artifact_url = assemble_nexus_url($url, $query_params)
    $checksum_url = regsubst($artifact_url, "p=${packaging}", "p=${packaging}.${checksum_type}")
  }
  archive { $name:
    ensure          => $ensure,
    source          => $artifact_url,
    username        => $username,
    password        => $password,
    checksum_url    => $checksum_url,
    checksum_type   => $checksum_type,
    checksum_verify => $checksum_verify,
    extract         => $extract,
    extract_path    => $extract_path,
    extract_flags   => $extract_flags,
    extract_command => $extract_command,
    user            => $user,
    group           => $group,
    creates         => $creates,
    cleanup         => $cleanup,
    proxy_server    => $proxy_server,
    proxy_type      => $proxy_type,
    allow_insecure  => $allow_insecure,
  }

  $file_owner = pick($owner, $archive::params::owner)
  $file_group = pick($group, $archive::params::group)
  $file_mode  = pick($mode, $archive::params::mode)

  file { $name:
    owner   => $file_owner,
    group   => $file_group,
    mode    => $file_mode,
    require => Archive[$name],
  }
}
