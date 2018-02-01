require 'base64'

Puppet::Parser::Functions::newfunction(:apache_pw_hash, :type => :rvalue, :doc => <<-EOS
Hashes a password in a format suitable for htpasswd files read by apache.

Currently uses SHA-hashes, because although this format is considered insecure, its the
most secure format supported by the most platforms.
EOS
) do |args|
  raise(Puppet::ParseError, "apache_pw_hash() wrong number of arguments. Given: #{args.size} for 1)") if args.size != 1
  raise(Puppet::ParseError, "apache_pw_hash(): first argument must be a string") unless args[0].is_a? String
  raise(Puppet::ParseError, "apache_pw_hash(): first argument must not be empty") if args[0].empty?

  password = args[0]
  return '{SHA}' + Base64.strict_encode64(Digest::SHA1.digest(password))
end
