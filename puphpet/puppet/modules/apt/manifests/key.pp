# == Define: apt::key
define apt::key (
    String $id                           = $title,
    Enum['present', 'absent'] $ensure    = present,
    Optional[String] $content            = undef,
    Optional[String] $source             = undef,
    String $server                       = $::apt::keyserver,
    Optional[String] $options            = undef,
    ) {

  assert_type(
    Pattern[
      /\A(0x)?[0-9a-fA-F]{8}\Z/,
      /\A(0x)?[0-9a-fA-F]{16}\Z/,
      /\A(0x)?[0-9a-fA-F]{40}\Z/,
    ], $id)

  if $source {
    assert_type(Pattern[/\Ahttps?:\/\//, /\Aftp:\/\//, /\A\/\w+/], $source)
  }

  if $server {
    assert_type(Pattern[/\A((hkp|http|https):\/\/)?([a-z\d])([a-z\d-]{0,61}\.)+[a-z\d]+(:\d{2,5})?$/], $server)
  }

  case $ensure {
    present: {
      if defined(Anchor["apt_key ${id} absent"]){
        fail("key with id ${id} already ensured as absent")
      }

      if !defined(Anchor["apt_key ${id} present"]) {
        apt_key { $title:
          ensure  => $ensure,
          id      => $id,
          source  => $source,
          content => $content,
          server  => $server,
          options => $options,
        } -> anchor { "apt_key ${id} present": }

        case $facts['os']['name'] {
          'Debian': {
            if versioncmp($facts['os']['release']['major'], '9') >= 0 {
              ensure_packages(['dirmngr'])
              Apt::Key<| title == $title |>
            }
          }
          'Ubuntu': {
            if versioncmp($facts['os']['release']['full'], '17.04') >= 0 {
              ensure_packages(['dirmngr'])
              Apt::Key<| title == $title |>
            }
          }
          default: { }
        }
      }
    }

    absent: {
      if defined(Anchor["apt_key ${id} present"]){
        fail("key with id ${id} already ensured as present")
      }

      if !defined(Anchor["apt_key ${id} absent"]){
        apt_key { $title:
          ensure  => $ensure,
          id      => $id,
          source  => $source,
          content => $content,
          server  => $server,
          options => $options,
        } -> anchor { "apt_key ${id} absent": }
      }
    }

    default: {
      fail "Invalid 'ensure' value '${ensure}' for apt::key"
    }
  }
}
