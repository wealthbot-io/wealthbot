## mounttab provider

This is a provider for a type distributed in the [puppetlabs-mount_providers
module](http://forge.puppetlabs.com/puppetlabs/mount_providers).

The provider needs to be explicitly given as `augeas` to use `augeasproviders`.

If editing a vfstab entry, slightly different options need to be passed
compared to a fstab entry.

### manage simple fstab entry

    mounttab { "/mnt":
      ensure   => present,
      device   => "/dev/myvg/mytest",
      fstype   => "ext4",
      options  => "defaults",
      provider => augeas,
    }

### manage full fstab entry

    mounttab { "/mnt":
      ensure   => present,
      device   => "/dev/myvg/mytest",
      fstype   => "ext4",
      options  => ["nosuid", "uid=12345"],
      dump     => "1",
      pass     => "2",
      provider => augeas,
    }

### manage fstab entry with default options

    mounttab { "/mnt":
      ensure   => present,
      device   => "/dev/myvg/mytest",
      fstype   => "ext4",
      provider => augeas,
    }

### delete fstab entry

    mounttab { "/":
      ensure   => absent,
      provider => augeas,
    }

### manage entry in another fstab location

    mounttab { "/home":
      ensure   => present,
      device   => "/dev/myvg/mytest",
      target   => "/etc/anotherfstab",
      provider => augeas
    }

### manage device in fstab entry only

    mounttab { "/home":
      ensure   => present,
      device   => "/dev/myvg/mytest",
      provider => augeas
    }

Note: dump and pass are both changing unless explicitly specified, see issue
[#16122](http://projects.puppetlabs.com/issues/16122).

### manage fstype in fstab entry only

    mounttab { "/home":
      ensure   => present,
      fstype   => "btrfs",
      provider => augeas,
    }

### manage options in fstab entry only

    mounttab { "/home":
      ensure   => present,
      options  => "nosuid",
      provider => augeas,
    }

### manage complex options in fstab entry only

    mounttab { "/home":
      ensure   => present,
      options  => [
        "nosuid",
        "uid=12345",
        'rootcontext="system_u:object_r:tmpfs_t:s0"',
      ],
      provider => augeas,
    }

### remove options from fstab entry

    mounttab { "/home":
      ensure   => present,
      options  => [],
      provider => augeas,
    }

### manage simple vfstab entry

    mounttab { "/mnt":
      ensure   => present,
      device   => "/dev/dsk/c1t1d1s1",
      fstype   => "ufs",
      atboot   => "yes",
      provider => augeas,
    }

### manage full vfstab entry

    mounttab { "/mnt":
      ensure      => present,
      device      => "/dev/dsk/c1t1d1s1",
      blockdevice => "/dev/foo/c1t1d1s1",
      fstype      => "ufs",
      pass        => "2",
      atboot      => "yes",
      options     => [ "nosuid", "nodev" ],
      provider    => augeas,
    }

### manage vfstab entry with default options

    mounttab { "/mnt":
      ensure   => present,
      device   => "/dev/myvg/mytest",
      fstype   => "ext4",
      provider => augeas,
    }

### delete vfstab entry

    mounttab { "/":
      ensure   => absent,
      provider => augeas,
    }

### remove options from vfstab entry

    mounttab { "/home":
      ensure   => present,
      options  => [],
      provider => augeas,
    }
