module AugeasSpec
  class Error < StandardError
  end
end

require 'augeas_spec/augparse'
require 'augeas_spec/fixtures'

RSpec.configure do |config|
  config.extend AugeasSpec::Augparse
  config.extend AugeasSpec::Fixtures
  config.include AugeasSpec::Augparse
  config.include AugeasSpec::Fixtures

  config.before :each do
    Puppet::Util::Storage.stubs(:store)
  end
end
