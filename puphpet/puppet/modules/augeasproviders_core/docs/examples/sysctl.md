## sysctl provider

This is a custom type and provider supplied by `augeasproviders`.

### manage simple entry

    sysctl { "net.ipv4.ip_forward":
      ensure => present,
      value  => "1",
    }

### manage entry with comment

    sysctl { "net.ipv4.ip_forward":
      ensure  => present,
      value   => "1",
      comment => "test",
    }

### delete entry

    sysctl { "kernel.sysrq":
      ensure => absent,
    }

### remove comment from entry

    sysctl { "kernel.sysrq":
      ensure  => present,
      comment => "",
    }

### manage entry in another sysctl.conf location

    sysctl { "net.ipv4.ip_forward":
      ensure => present,
      value  => "1",
      target => "/etc/sysctl.d/forwarding.conf",
    }

### do not update value with the `sysctl` command

    sysctl { "net.ipv4.ip_forward":
      ensure => present,
      value  => "1",
      apply  => false,
    }
