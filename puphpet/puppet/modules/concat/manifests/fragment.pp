# Creates a concat_fragment in the catalogue
#
# @param target The file that these fragments belong to
# @param content If present puts the content into the file
# @param source If content was not specified, use the source
# @param order
#   By default all files gets a 10_ prefix in the directory you can set it to
#   anything else using this to influence the order of the content in the file
#
define concat::fragment(
  String                             $target,
  Optional[String]                   $content = undef,
  Optional[Variant[String, Array]]   $source  = undef,
  Variant[String, Integer]           $order   = '10',
) {
  $resource = 'Concat::Fragment'

  if ($order =~ String and $order =~ /[:\n\/]/) {
    fail("${resource}['${title}']: 'order' cannot contain '/', ':', or '\n'.")
  }

  if ! ($content or $source) {
    crit('No content, source or symlink specified')
  } elsif ($content and $source) {
    fail("${resource}['${title}']: Can't use 'source' and 'content' at the same time.")
  }

  $safe_target_name = regsubst($target, '[/:~\n\s\+\*\(\)@]', '_', 'GM')

  concat_fragment { $name:
    target  => $target,
    tag     => $safe_target_name,
    order   => $order,
    content => $content,
    source  => $source,
  }
}
