require 'puppetlabs_spec_helper/module_spec_helper'
require 'simplecov'
if ENV['PARSER'] == 'future'
  RSpec.configure do |c|
    c.parser = 'future'
  end
end
if RUBY_VERSION >= '2.0.0'
  require 'coveralls'
  SimpleCov.formatter = Coveralls::SimpleCov::Formatter
  SimpleCov.start do
    add_filter 'spec/fixtures/modules/'
  end
end
