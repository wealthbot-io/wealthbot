## sshd_config provider

This is a custom type and provider supplied by `augeasproviders`.

### manage simple entry

    sshd_config { "PermitRootLogin":
      ensure => present,
      value  => "yes",
    }

### manage array entry

    sshd_config { "AllowGroups":
      ensure => present,
      value  => ["sshgroups", "admins"],
    }

### manage entry in a Match block

    sshd_config { "X11Forwarding":
      ensure    => present,
      condition => "Host foo User root",
      value     => "yes",
    }

    sshd_config { "AllowAgentForwarding":
      ensure    => present,
      condition => "Host *.example.net",
      value     => "yes",
    }

### manage entries with same name in different blocks

    sshd_config { "X11Forwarding global":
      ensure => present,
      key    => "X11Forwarding",
      value  => "no",
    }

    sshd_config { "X11Forwarding foo":
      ensure    => present,
      key       => "X11Forwarding",
      condition => "User foo",
      value     => "yes",
    }

    sshd_config { "X11Forwarding root":
      ensure    => present,
      key       => "X11Forwarding",
      condition => "User root",
      value     => "no",
    }

### delete entry

    sshd_config { "PermitRootLogin":
      ensure => absent,
    }

    sshd_config { "AllowAgentForwarding":
      ensure    => absent,
      condition => "Host *.example.net User *",
    }

### manage entry in another sshd_config location

    sshd_config { "PermitRootLogin":
      ensure => present,
      value  => "yes",
      target => "/etc/ssh/another_sshd_config",
    }
