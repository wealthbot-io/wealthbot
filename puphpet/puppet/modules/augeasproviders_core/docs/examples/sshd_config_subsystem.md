## sshd_config_subsystem provider

This is a custom type and provider supplied by `augeasproviders`.

### manage entry

    sshd_config_subsystem { "sftp":
      ensure  => present,
      command => "/usr/lib/openssh/sftp-server",
    }

### delete entry

    sshd_config_subsystem { "sftp":
      ensure => absent,
    }

### manage entry in another sshd_config location

    sshd_config_subsystem { "sftp":
      ensure  => present,
      command => "/usr/lib/openssh/sftp-server",
      target  => "/etc/ssh/another_sshd_config",
    }
