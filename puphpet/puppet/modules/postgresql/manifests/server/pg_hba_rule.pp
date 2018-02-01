# This resource manages an individual rule that applies to the file defined in
# $target. See README.md for more details.
define postgresql::server::pg_hba_rule(
  Enum['local', 'host', 'hostssl', 'hostnossl'] $type,
  String $database,
  String $user,
  String $auth_method,
  Optional[String] $address       = undef,
  String $description             = 'none',
  Optional[String] $auth_option   = undef,
  Variant[String, Integer] $order = 150,

  # Needed for testing primarily, support for multiple files is not really
  # working.
  Stdlib::Absolutepath $target  = $postgresql::server::pg_hba_conf_path,
  String $postgresql_version    = $postgresql::server::_version
) {

  #Allow users to manage pg_hba.conf even if they are not managing the whole PostgreSQL instance
  if !defined( 'postgresql::server' ) {
    $manage_pg_hba_conf = true
  }
  else {
    $manage_pg_hba_conf = $postgresql::server::manage_pg_hba_conf
  }

  if $manage_pg_hba_conf == false {
      fail('postgresql::server::manage_pg_hba_conf has been disabled, so this resource is now unused and redundant, either enable that option or remove this resource from your manifests')
  } else {

    if($type =~ /^host/ and $address == undef) {
      fail('You must specify an address property when type is host based')
    }

    $allowed_auth_methods = $postgresql_version ? {
      '9.6' => ['trust', 'reject', 'md5', 'password', 'gss', 'sspi', 'ident', 'peer', 'ldap', 'radius', 'cert', 'pam', 'bsd'],
      '9.5' => ['trust', 'reject', 'md5', 'password', 'gss', 'sspi', 'ident', 'peer', 'ldap', 'radius', 'cert', 'pam'],
      '9.4' => ['trust', 'reject', 'md5', 'password', 'gss', 'sspi', 'ident', 'peer', 'ldap', 'radius', 'cert', 'pam'],
      '9.3' => ['trust', 'reject', 'md5', 'password', 'gss', 'sspi', 'krb5', 'ident', 'peer', 'ldap', 'radius', 'cert', 'pam'],
      '9.2' => ['trust', 'reject', 'md5', 'password', 'gss', 'sspi', 'krb5', 'ident', 'peer', 'ldap', 'radius', 'cert', 'pam'],
      '9.1' => ['trust', 'reject', 'md5', 'password', 'gss', 'sspi', 'krb5', 'ident', 'peer', 'ldap', 'radius', 'cert', 'pam'],
      '9.0' => ['trust', 'reject', 'md5', 'password', 'gss', 'sspi', 'krb5', 'ident', 'ldap', 'radius', 'cert', 'pam'],
      '8.4' => ['trust', 'reject', 'md5', 'password', 'gss', 'sspi', 'krb5', 'ident', 'ldap', 'cert', 'pam'],
      '8.3' => ['trust', 'reject', 'md5', 'crypt', 'password', 'gss', 'sspi', 'krb5', 'ident', 'ldap', 'pam'],
      '8.2' => ['trust', 'reject', 'md5', 'crypt', 'password', 'krb5', 'ident', 'ldap', 'pam'],
      '8.1' => ['trust', 'reject', 'md5', 'crypt', 'password', 'krb5', 'ident', 'pam'],
      default => ['trust', 'reject', 'md5', 'password', 'gss', 'sspi', 'krb5', 'ident', 'peer', 'ldap', 'radius', 'cert', 'pam', 'crypt', 'bsd']
    }

    assert_type(Enum[$allowed_auth_methods], $auth_method)

    # Create a rule fragment
    $fragname = "pg_hba_rule_${name}"
    concat::fragment { $fragname:
      target  => $target,
      content => template('postgresql/pg_hba_rule.conf'),
      order   => $order,
    }
  }
}
