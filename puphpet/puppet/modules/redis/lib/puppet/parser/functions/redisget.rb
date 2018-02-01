require 'redis'

module Puppet::Parser::Functions
  newfunction(:redisget, :type => :rvalue, :doc => <<-DOC
Returns the value of the key being looked up or nil if the key does not
exist. Takes two arguments with an optional third. The first being a string
value of the key to be looked up, the second is the URL to the Redis service
and the third optional argument is a default value to be used if the lookup
fails.
@param redis_key [String] The key to look up in redis.
@param redis_uri [String] The endpoint of the Redis instance.
@param default_value [String] The value to return if the key is not found or
  the connection to Redis fails
@return [String] The value of the key from redis
@return [String] An empty string eg. `''`
@example Calling the function.
  $version = redisget('version.myapp', 'redis://redis.example.com:6379')
@example Calling the function with a default if failure occurs
  $version = redisget('version.myapp', 'redis://redis.example.com:6379', $::myapp_version)
DOC
  ) do |args|

    raise(Puppet::ParseError, "redisget(): Wrong number of arguments given (#{args.size} for 2 or 3)") if args.size != 2 and args.size != 3

    key = args[0]
    url = args[1]

    if args.size == 3
      default = args[2]
      raise(Puppet::ParseError, "redisget(): Wrong argument type given (#{default.class} for String) for arg3 (default)") if default.is_a?(String) == false
    end

    raise(Puppet::ParseError, "redisget(): Wrong argument type given (#{key.class} for String) for arg1 (key)") if key.is_a?(String) == false
    raise(Puppet::ParseError, "redisget(): Wrong argument type given (#{url.class} for String) for arg2 (url)") if url.is_a?(String) == false

    begin
      Redis.new(:url => url).get(key) || default
    rescue Redis::CannotConnectError, SocketError => e
      raise Puppet::Error, "connection to redis server failed - #{e}" unless default
      debug "Connection to redis failed with #{e} - Returning default value of #{default}"
      default
    end

  end
end
