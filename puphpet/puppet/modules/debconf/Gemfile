source ENV['GEM_SOURCE'] || 'https://rubygems.org'

group :test do
  gem 'metadata-json-lint',     :require => false
  gem 'puppet-lint',            :require => false
  gem 'puppet_facts',           :require => false
  gem 'puppetlabs_spec_helper', :require => false
  gem 'rspec-puppet',           :require => false
  gem 'rspec-puppet-facts',     :require => false
  gem 'json', '< 2.0.0',        :require => false if RUBY_VERSION < '2.0.0'
  gem 'json_pure', '< 2.0.0',   :require => false if RUBY_VERSION < '2.0.0'
end

ENV['PUPPET_GEM_VERSION'].nil? ? puppetversion = '~> 4.0' : puppetversion = ENV['PUPPET_GEM_VERSION'].to_s
gem 'puppet', puppetversion, :require => false, :groups => [:test]
