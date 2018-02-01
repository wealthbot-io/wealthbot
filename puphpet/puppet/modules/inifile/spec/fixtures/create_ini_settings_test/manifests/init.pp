# simple test class
class create_ini_settings_test {
  $settings = {  section1 => {
      setting1 => val1
    },
    section2 => {
      setting2 => val2,
      setting3 => {
        ensure => absent
      }
    }
  }
  $defaults = {
    path => '/tmp/foo.ini'
  }
  create_ini_settings($settings,$defaults)
}
