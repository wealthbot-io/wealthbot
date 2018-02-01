## kernel_parameter provider

This is a custom type and provider supplied by `augeasproviders`.  It supports
both GRUB Legacy (0.9x) and GRUB 2 configurations.

### manage parameter without value

    kernel_parameter { "quiet":
      ensure => present,
    }

### manage parameter with value

    kernel_parameter { "elevator":
      ensure  => present,
      value   => "deadline",
    }

### manage parameter with multiple values

    kernel_parameter { "rd_LVM_LV":
      ensure  => present,
      value   => ["vg/lvroot", "vg/lvvar"],
    }

### manage parameter on certain boot types

Bootmode defaults to "all", so settings are applied for all boot types usually.

Apply only to normal boots:

    kernel_parameter { "quiet":
      ensure   => present,
      bootmode => "normal",
    }

Only recovery mode boots (unsupported with GRUB 2):

    kernel_parameter { "quiet":
      ensure   => present,
      bootmode => "recovery",
    }

### delete entry

    kernel_parameter { "rhgb":
      ensure => absent,
    }

### manage parameter in another config location

    kernel_parameter { "elevator":
      ensure => present,
      value  => "deadline",
      target => "/mnt/boot/grub/menu.lst",
    }
