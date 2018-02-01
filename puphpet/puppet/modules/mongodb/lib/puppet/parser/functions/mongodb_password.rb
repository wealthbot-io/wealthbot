require File.expand_path(File.join(File.dirname(__FILE__), '..', '..', 'util', 'mongodb_md5er'))

module Puppet::Parser::Functions
  newfunction(:mongodb_password, type: :rvalue, doc: <<-EOS
    Returns the mongodb password hash from the clear text password.
    EOS
             ) do |args|

    if args.size != 2
      raise(Puppet::ParseError, 'mongodb_password(): Wrong number of arguments ' \
        "given (#{args.size} for 2)")
    end

    Puppet::Util::MongodbMd5er.md5(args[0], args[1])
  end
end
