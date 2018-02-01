require 'puppetlabs_spec_helper/module_spec_helper'
require 'rspec-puppet-utils'
require 'rspec/mocks'
require 'rspec-puppet-facts'
include RspecPuppetFacts

unless RUBY_VERSION =~ %r{^1.9}
  require 'coveralls'
  Coveralls.wear!
end

#
# Require all support files
#
Dir['./spec/support/**/*.rb'].sort.each { |f| require f }

RSpec.configure do |c|
  c.formatter = 'documentation'
  c.mock_framework = :rspec
end
