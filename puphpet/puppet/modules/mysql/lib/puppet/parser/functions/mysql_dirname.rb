# Returns the dirname of a path.
module Puppet::Parser::Functions
  newfunction(:mysql_dirname, type: :rvalue, doc: <<-EOS
    Returns the dirname of a path.
    EOS
             ) do |arguments|

    if arguments.empty?
      raise Puppet::ParseError, _('mysql_dirname(): Wrong number of arguments given (%{args_length} for 1)') % { args_length: args.length }
    end

    path = arguments[0]
    return File.dirname(path)
  end
end
