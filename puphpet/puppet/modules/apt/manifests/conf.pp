define apt::conf (
  Optional[String] $content          = undef,
  Enum['present', 'absent'] $ensure  = present,
  Variant[String, Integer] $priority = 50,
  Optional[Boolean] $notify_update   = undef,
) {

  unless $ensure == 'absent' {
    unless $content {
      fail('Need to pass in content parameter')
    }
  }

  $confheadertmp = epp('apt/_conf_header.epp')
  apt::setting { "conf-${name}":
    ensure        => $ensure,
    priority      => $priority,
    content       => "${confheadertmp}${content}",
    notify_update => $notify_update,
  }
}
