require 'semver'

module Puppet::Parser::Functions
  newfunction(:validate_nodejs_version) do |args|
    version = SemVer.new(args[0])
    if version < SemVer.new('v0.12.0')
      raise Puppet::ParseError, ('All NodeJS versions below `v0.12.0` are not supported!')
    end
  end
end
