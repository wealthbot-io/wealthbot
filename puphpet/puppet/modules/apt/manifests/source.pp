# source.pp
# add an apt source
define apt::source(
  Optional[String] $location                    = undef,
  String $comment                               = $name,
  String $ensure                                = present,
  Optional[String] $release                     = undef,
  String $repos                                 = 'main',
  Optional[Variant[Hash]] $include              = {},
  Optional[Variant[String, Hash]] $key          = undef,
  Optional[Variant[Hash, Numeric, String]] $pin = undef,
  Optional[String] $architecture                = undef,
  Boolean $allow_unsigned                       = false,
  Boolean $notify_update                        = true,
) {

  # This is needed for compat with 1.8.x
  include ::apt

  $_before = Apt::Setting["list-${title}"]

  if !$release {
    if $facts['lsbdistcodename'] {
      $_release = $facts['lsbdistcodename']
    } else {
      fail('lsbdistcodename fact not available: release parameter required')
    }
  } else {
    $_release = $release
  }

  # Some releases do not support https transport with default installation
  $_transport_https_releases = [ 'wheezy', 'jessie', 'stretch', 'trusty', 'xenial' ]

  if $ensure == 'present' {
    if ! $location {
      fail('cannot create a source entry without specifying a location')
    } elsif $_release in $_transport_https_releases {
      $method = split($location, '[:\/]+')[0]
      if $method == 'https' {
        ensure_packages('apt-transport-https')
      }
    }
  }

  $includes = merge($::apt::include_defaults, $include)

  if $key {
    if $key =~ Hash {
      unless $key['id'] {
        fail('key hash must contain at least an id entry')
      }
      $_key = merge($::apt::source_key_defaults, $key)
    } else {
      $_key = { 'id' => assert_type(String[1], $key) }
    }
  }

  $header = epp('apt/_header.epp')

  $sourcelist = epp('apt/source.list.epp', {
    'comment'          => $comment,
    'includes'         => $includes,
    'opt_architecture' => $architecture,
    'allow_unsigned'   => $allow_unsigned,
    'location'         => $location,
    'release'          => $_release,
    'repos'            => $repos,
  })

  apt::setting { "list-${name}":
    ensure        => $ensure,
    content       => "${header}${sourcelist}",
    notify_update => $notify_update,
  }

  if $pin {
    if $pin =~ Hash {
      $_pin = merge($pin, { 'ensure' => $ensure, 'before' => $_before })
    } elsif ($pin =~ Numeric or $pin =~ String) {
      $url_split = split($location, '[:\/]+')
      $host      = $url_split[1]
      $_pin = {
        'ensure'   => $ensure,
        'priority' => $pin,
        'before'   => $_before,
        'origin'   => $host,
      }
    } else {
      fail('Received invalid value for pin parameter')
    }
    create_resources('apt::pin', { "${name}" => $_pin })
  }

  # We do not want to remove keys when the source is absent.
  if $key and ($ensure == 'present') {
    if $_key =~ Hash {
      apt::key { "Add key: ${$_key['id']} from Apt::Source ${title}":
        ensure  => present,
        id      => $_key['id'],
        server  => $_key['server'],
        content => $_key['content'],
        source  => $_key['source'],
        options => $_key['options'],
        before  => $_before,
      }
    }
  }
}
