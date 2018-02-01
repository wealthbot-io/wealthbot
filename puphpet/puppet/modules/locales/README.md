# puppet-locales [![Build Status](https://secure.travis-ci.org/saz/puppet-locales.png)](https://travis-ci.org/saz/puppet-locales)

Manage locales via Puppet

### Supported Puppet versions
* Puppet >= 4
* Last version supporting Puppet 3: v2.4.0

## Usage

By default, en and de locales will be generated.

```
  class { 'locales': }
```

Configure a bunch of locales.

```
  class { 'locales': 
    locales   => ['en_US.UTF-8 UTF-8', 'fr_CH.UTF-8 UTF-8'],
  }
```

Advanced usage allows you to select which locales will be configured as well as the default one.


```
  class { 'locales':
    default_locale  => 'en_US.UTF-8',
    locales         => ['en_US.UTF-8 UTF-8', 'fr_CH.UTF-8 UTF-8'],
  }
```

You can also set specific locale environment variables. See the locale man-page
for available LC_* environment variables and their descriptions:

```
  class { 'locales':
    default_locale  => 'en_US.UTF-8',
    locales         => ['en_US.UTF-8 UTF-8', 'fr_CH.UTF-8 UTF-8', 'en_DK.UTF-8 UTF-8', 'de_DE.UTF-8 UTF-8' ],
    lc_time         => 'en_DK.UTF-8',
    lc_paper        => 'de_DE.UTF-8',
  }
```

## Other class parameters
* locales: Name of locales to generate, default: ['en_US.UTF-8 UTF-8', 'de_DE.UTF-8 UTF-8']
* ensure: present or absent, default: present
* default_locale: string, default: 'C'. Set the default locale.
* lc_ctype: string, default: undef. Character classification and case conversion.
* lc_collate: string, default: undef. Collation order.
* lc_time: string, default: undef. Date and time formats.
* ...
* autoupgrade: true or false, default: false. Auto-upgrade package, if there is a newer version.
* package: string, default: OS specific. Set package name, if platform is not supported.
* config_file: string, default: OS specific. Set config_file, if platform is not supported.
* locale_gen_command: string, default: OS specific. Set locale_gen_command, if platform is not supported.
* Suse specific:
  * root_uses_lang: if set to 'ctype', root will be stay POSIX, set to 'yes' to change root to the global language as well. Defaults to 'ctype'.
  * installed_languages: blank for english, otherwise space seperated list.  Used by Yast2 only.
  * auto_detect_utf8: Workaround for missing forward of LANG and LC variables of e.g. ssh login connections.  Defaults to 'no'.
  * input_method: A default input method to be used in X11. For more details see the comments at the top of /etc/X11/xim on a Suse system.
