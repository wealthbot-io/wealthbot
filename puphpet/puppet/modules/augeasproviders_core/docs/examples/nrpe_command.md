## nrpe_command provider

This is a custom type and provider supplied by `augeasproviders`.

### manage entry

    nrpe_command { "check_spec_test":
      ensure  => present,
      command => "/usr/bin/check_my_thing -p 'some command with \"multiple [types]\" of quotes' -x and-stuff",
    }

### delete entry

    nrpe_command { "check_test":
      ensure => absent,
    }
