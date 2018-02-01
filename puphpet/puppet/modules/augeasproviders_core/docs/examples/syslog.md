## syslog provider

This is a custom type, with two providers supplied by `augeasproviders`.  A
`syslog` provider handles basic syslog configs, while an `rsyslog` provider
handles the extended rsyslog config (this requires Augeas 1.0.0).

### manage entry

    syslog { "my test":
      ensure      => present,
      facility    => "local2",
      level       => "*",
      action_type => "file",
      action      => "/var/log/test.log",
    }

### manage entry with no file sync

    syslog { "cron.*":
      ensure      => present,
      facility    => "cron",
      level       => "*",
      action_type => "file",
      action      => "/var/log/cron",
      no_sync     => true,
    }

### manage remote hostname entry

    syslog { "my test":
      ensure      => present,
      facility    => "local2",
      level       => "*",
      action_type => "hostname",
      action      => "centralserver",
    }

### manage user destination entry

    syslog { "my test":
      ensure      => present,
      facility    => "local2",
      level       => "*",
      action_type => "user",
      action      => "root",
    }

### manage program entry

    syslog { "my test":
      ensure      => present,
      facility    => "local2",
      level       => "*",
      action_type => "program",
      action      => "/usr/bin/foo",
    }

### delete entry

    syslog { "mail.*":
      ensure      => absent,
      facility    => "mail",
      level       => "*",
      action_type => "file",
      action      => "/var/log/maillog",
    }

### manage entry in rsyslog

    syslog { "my test":
      ensure      => present,
      facility    => "local2",
      level       => "*",
      action_type => "file",
      action      => "/var/log/test.log",
      provider    => "rsyslog",
    }

### manage entry in another syslog location

    syslog { "my test":
      ensure      => present,
      facility    => "local2",
      level       => "*",
      action_type => "file",
      action      => "/var/log/test.log",
      target      => "/etc/mysyslog.conf",
    }
