source 'https://rubygems.org'

group :test do
  gem 'rake', '10.5.0'
  gem 'puppet-lint'
  gem 'puppet-syntax'
  gem 'puppetlabs_spec_helper', '1.0.1'
  gem 'rspec-puppet', '2.2.0'
  gem 'rspec', '2.99.0'
end

group :development do
  gem 'travis'
  gem 'travis-lint'
  gem 'beaker'
  gem 'beaker-rspec'
  gem 'pry'
  gem 'guard-rake'
end


if puppetversion = ENV['PUPPET_VERSION']
  gem 'puppet', puppetversion
else
  gem 'puppet', '~> 3.8.0'
end
