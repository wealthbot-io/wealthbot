
require 'ipaddr'

module Puppet::Util
  # IPCidr object wrapper for IPAddr
  class IPCidr < IPAddr
    def initialize(ipaddr, family = Socket::AF_UNSPEC)
      super(ipaddr, family)
    rescue ArgumentError => e
      raise ArgumentError, "Invalid address from IPAddr.new: #{ipaddr}" if e.message =~ %r{invalid address}
      raise e
    end

    def netmask
      _to_string(@mask_addr)
    end

    def prefixlen
      m = case @family
          when Socket::AF_INET
            IN4MASK
          when Socket::AF_INET6
            IN6MASK
          else
            raise 'unsupported address family'
          end
      return Regexp.last_match(1).length if %r{\A(1*)(0*)\z} =~ (@mask_addr & m).to_s(2)
      raise 'bad addr_mask format'
    end

    def cidr
      cidr = '%s/%s' % [to_s, prefixlen]
      cidr
    end
  end
end
