require_relative 'util/nodejs_functions'

module Puppet::Parser::Functions
  newfunction(:evaluate_version, :type => :rvalue) do |args|
    raise(Puppet::ParseError, "evaluate_version(): too few arguments") if args.size < 1

    version = args[0]
    return get_latest_version if version == 'latest'
    return get_lts_version if version == 'lts'

    if version =~ /^(?:(v)?)[0-9]+\.[0-9]+\.[0-9]+/
      # if the version is matched, but contains no `v` as prefix, it will
      # be added automatically
      return 'v' + version if version =~ /^[^v](.*)/
      return version
    end

    return get_version_from_branch version if version =~ /^(?:(v)?)[0-9]+\.([0-9]+|x)$/

    raise Puppet::ParseError, "evaluate_version(): version must be `lts`, `latest` or look like `x.x.x`"
  end
end
