source ENV['GEM_SOURCE'] || 'https://rubygems.org'

group :test do
  gem 'puppet', (ENV['PUPPET_VERSION'] || '~> 4.10'), :require => false

  gem 'metadata-json-lint'
  gem 'specinfra', '~> 2.60'
  gem 'xmlrpc'

  gem 'ci_reporter_rspec'
  gem 'facter'
  gem 'pry'
  gem 'puppet-lint'
  gem 'puppet-strings'
  gem 'puppet-syntax'
  gem 'puppetlabs_spec_helper'
  gem 'rake'
  gem 'rspec', '~> 3.0'
  gem 'rspec-puppet', '~> 2.6'
  gem 'rspec-puppet-facts'
  gem 'rspec-puppet-utils'
  gem 'rspec-retry'
  gem 'rubocop'
  gem 'rubysl-securerandom'
  gem 'webmock'

  # Extra Puppet-lint gems
  gem 'puppet-lint-appends-check',
      :git => 'https://github.com/voxpupuli/puppet-lint-appends-check',
      :ref => '07be8ce22d69353db055820b60bb77fe020238a6',
      :require => false
  gem 'puppet-lint-empty_string-check', :require => false
  gem 'puppet-lint-file_ensure-check', :require => false
  gem 'puppet-lint-leading_zero-check', :require => false
  gem 'puppet-lint-param-docs', :require => false
  gem 'puppet-lint-trailing_comma-check', :require => false
  gem 'puppet-lint-undef_in_function-check', :require => false
  gem 'puppet-lint-unquoted_string-check', :require => false
  gem 'puppet-lint-version_comparison-check', :require => false
end

group :development do
  gem 'puppet-blacksmith'
end

group :system_tests do
  gem 'beaker', '~> 3.7'
  gem 'beaker-rspec', '~> 6.0'
  gem 'docker-api', '~> 1.0'
  gem 'infrataster'
end
