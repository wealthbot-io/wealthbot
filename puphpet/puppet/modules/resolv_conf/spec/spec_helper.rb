require 'puppetlabs_spec_helper/module_spec_helper'

if Puppet::Util::Package.versioncmp(Puppet.version, '4.5.0') >= 0
  RSpec.configure do |c|
    c.before do
      Puppet.settings[:strict] = :error
    end
  end
end

# put local configuration and setup into spec_helper_local
begin
  require 'spec_helper_local'
rescue LoadError => loaderror
  puts "Could not require spec_helper_local: #{loaderror.message}"
end
