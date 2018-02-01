## pg_hba provider

This is a custom type and provider supplied by `augeasproviders`.

### Composite namevars

This type supports composite namevars in order to easily specify the entry you want to manage. The format for composite namevars is:

    local to <user> on <database> [in <target>]

if defining a local (socket) rule, or:

    <type> to <user> on <database> from <address> [in <target>]

otherwise.

In each form, `in <target>` is optional. You can also use a personalized namevar and specify all parameters manually.


### manage simple local entry

    pg_hba { 'local to all on all':
      ensure => present,
      method => 'md5',
      target => '/etc/postgresql/9.1/main/pg_hba.conf',
    }

### manage simple host entry

    pg_hba { 'host to all on all from 192.168.0.1':
      ensure => present,
      method => 'md5',
      target => '/etc/postgresql/9.1/main/pg_hba.conf',
    }

### multiple users and databases

    pg_hba { 'host to user1,user2 on db1,db2 from 192.168.0.1':
      ensure => present,
      method => 'md5',
      target => '/etc/postgresql/9.1/main/pg_hba.conf',
    }

    pg_hba { 'Allow +foo and @bar to mydb and yourdb':
      ensure   => present,
      user     => ['+foo', '@bar'],
      database => ['mydb', 'yourdb'],
      method   => 'md5',
      target   => '/etc/postgresql/9.1/main/pg_hba.conf',
    }

### using a personalized namevar

    pg_hba { 'Default entry':
      type     => 'local',
      user     => 'all',
      database => 'all',
      method   => 'md5',
      target   => '/etc/postgresql/9.1/main/pg_hba.conf',
    }

### pass options for the method

    pg_hba { 'Default entry with option':
      method  => 'ident',
      options => { 'sameuser' => undef },
      target  => '/etc/postgresql/9.1/main/pg_hba.conf',
    }

    pg_hba { 'host to all on all from .dev.example.com in /etc/postgresql/9.1/main/pg_hba.conf':
      method  => 'ldap',
      options => {
        'ldapserver' => 'auth.example.com',
        'ldaptls'    => '1',
        'ldapprefix' => 'uid=',
        'ldapsuffix' => ',ou=people,dc=example,dc=com',
      },
    }

### insert entry in specific position

    pg_hba { 'local to all on all':
      ensure   => present,
      method   => 'md5',
      position => 'before first entry',
      target   => '/etc/postgresql/9.1/main/pg_hba.conf',
    }

    pg_hba { 'local to all on all':
      ensure   => present,
      method   => 'md5',
      position => 'after last entry',
      target   => '/etc/postgresql/9.1/main/pg_hba.conf',
    }

    pg_hba { 'local to all on all':
      ensure   => present,
      method   => 'md5',
      position => 'before last local',
      target   => '/etc/postgresql/9.1/main/pg_hba.conf',
    }

    pg_hba { 'local to all on all':
      ensure   => present,
      method   => 'md5',
      position => 'after first hostssl',
      target   => '/etc/postgresql/9.1/main/pg_hba.conf',
    }

    pg_hba { 'local to all on all':
      ensure   => present,
      method   => 'md5',
      position => 'after first anyhost', # any type matching host.*
      target   => '/etc/postgresql/9.1/main/pg_hba.conf',
    }

    pg_hba { 'local to all on all':
      ensure   => present,
      method   => 'md5',
      position => 'before 5', # Before the fifth entry
      target   => '/etc/postgresql/9.1/main/pg_hba.conf',
    }

    pg_hba { 'local to all on all':
      ensure   => present,
      method   => 'md5',
      position => '*[database="all" and user="admin"][1]', # First entry for database 'all' and user 'admin'
      target   => '/etc/postgresql/9.1/main/pg_hba.conf',
    }

### ensure position is correct

    pg_hba { 'local to all on all':
      ensure   => positioned,
      method   => 'md5',
      position => 'before first entry',
      target   => '/etc/postgresql/9.1/main/pg_hba.conf',
    }

### delete entry

    pg_hba { 'local to all on all':
      ensure => absent,
      target => '/etc/postgresql/9.1/main/pg_hba.conf',
    }

    pg_hba { 'host to all on all from 192.168.0.1':
      ensure    => absent,
      target => '/etc/postgresql/9.1/main/pg_hba.conf',
    }
