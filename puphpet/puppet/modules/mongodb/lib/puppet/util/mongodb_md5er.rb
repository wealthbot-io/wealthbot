require 'digest/md5'

module Puppet
  module Util
    class MongodbMd5er
      def self.md5(username, password)
        Digest::MD5.hexdigest("#{username}:mongo:#{password}")
      end
    end
  end
end
