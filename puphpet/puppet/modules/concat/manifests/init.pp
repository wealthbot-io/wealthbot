# Sets up so that you can use fragments to build a final config file,
#
# @param ensure
#   Present/Absent
# @param path
#   The path to the final file. Use this in case you want to differentiate
#   between the name of a resource and the file path.  Note: Use the name you
#   provided in the target of your fragments.
# @param owner
#   Who will own the file
# @param group
#   Who will own the file
# @param mode
#   The mode of the final file
# @param show_diff
#   Use metaparam for files to show/hide diffs for reporting when using eyaml
#   secrets.  Defaults to true
# @param warn
#   Adds a normal shell style comment top of the file indicating that it is
#   built by puppet.
#   Before 2.0.0, this parameter would add a newline at the end of the warn
#   message. To improve flexibilty, this was removed. Please add it explicitely
#   if you need it.
# @param backup
#   Controls the filebucketing behavior of the final file and see File type
#   reference for its use.  Defaults to 'puppet'
# @param replace
#   Whether to replace a file that already exists on the local system
# @param order
#   Select whether to order associated fragments by 'alpha' or 'numeric'.
#   Defaults to 'alpha'.
# @param ensure_newline
#   Specifies whether to ensure there's a new line at the end of each fragment.
#   Valid options: 'true' and 'false'. Default value: 'false'.
# @param selinux_ignore_defaults
# @param selrange
# @param selrole
# @param seltype
# @param seluser
# @param validate_cmd
#   Specifies a validation command to apply to the destination file.
#   Requires Puppet version 3.5 or newer. Valid options: a string to be passed
#   to a file resource. Default value: undefined.
# @param format
#   Specify what data type to merge the fragments as.
#   Valid options: 'plain', 'yaml', 'json', 'json-pretty'. Default value: `plain`.
# @param force
#   Specifies whether to merge data structures, keeping the values with higher order.
#   Used when format is specified as a value other than 'plain'.
#   Valid options: `true` and `false`. Default value: `false`.
#
#
define concat(
  Enum['present', 'absent']          $ensure                  = 'present',
  Stdlib::Absolutepath               $path                    = $name,
  Optional[Variant[String, Integer]] $owner                   = undef,
  Optional[Variant[String, Integer]] $group                   = undef,
  String                             $mode                    = '0644',
  Variant[Boolean, String]           $warn                    = false,
  Boolean                            $show_diff               = true,
  Variant[Boolean, String]           $backup                  = 'puppet',
  Boolean                            $replace                 = true,
  Enum['alpha','numeric']            $order                   = 'alpha',
  Boolean                            $ensure_newline          = false,
  Optional[String]                   $validate_cmd            = undef,
  Optional[Boolean]                  $selinux_ignore_defaults = undef,
  Optional[String]                   $selrange                = undef,
  Optional[String]                   $selrole                 = undef,
  Optional[String]                   $seltype                 = undef,
  Optional[String]                   $seluser                 = undef,
  Optional[String]                   $format                  = 'plain',
  Optional[Boolean]                  $force                   = false,
) {

  $safe_name            = regsubst($name, '[/:~\n\s\+\*\(\)@]', '_', 'G')
  $default_warn_message = "# This file is managed by Puppet. DO NOT EDIT.\n"

  case $warn {
    true: {
      $warn_message = $default_warn_message
      $_append_header = true
    }
    false: {
      $warn_message = ''
      $_append_header = false
    }
    default: {
      $warn_message = $warn
      $_append_header = true
    }
  }

  if $ensure == 'present' {
    concat_file { $name:
      tag                     => $safe_name,
      path                    => $path,
      owner                   => $owner,
      group                   => $group,
      mode                    => $mode,
      selinux_ignore_defaults => $selinux_ignore_defaults,
      selrange                => $selrange,
      selrole                 => $selrole,
      seltype                 => $seltype,
      seluser                 => $seluser,
      replace                 => $replace,
      backup                  => $backup,
      show_diff               => $show_diff,
      order                   => $order,
      ensure_newline          => $ensure_newline,
      validate_cmd            => $validate_cmd,
      format                  => $format,
      force                   => $force,
    }

    if $_append_header {
      concat_fragment { "${name}_header":
        target  => $name,
        tag     => $safe_name,
        content => $warn_message,
        order   => '0',
      }
    }
  } else {
    concat_file { $name:
      ensure => $ensure,
      tag    => $safe_name,
      path   => $path,
      backup => $backup,
    }
  }
}
