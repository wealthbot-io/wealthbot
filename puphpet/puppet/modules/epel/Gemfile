source "https://rubygems.org"

group :test do
  gem 'puppetlabs_spec_helper', '~> 2.1.1'
  gem 'puppet', ENV['PUPPET_GEM_VERSION'] || '~> 4.0.0'
  gem 'rspec-puppet', '~> 2.5'
  gem 'rspec-puppet-facts'
  gem 'puppet-lint-absolute_classname-check'
  gem 'puppet-lint-leading_zero-check'
  gem 'puppet-lint-trailing_comma-check'
  gem 'puppet-lint-version_comparison-check'
  gem 'puppet-lint-classes_and_types_beginning_with_digits-check'
  gem 'puppet-lint-unquoted_string-check'
  gem 'metadata-json-lint'
  gem 'puppet-blacksmith'
  gem 'rubocop', '0.48.0'
  gem 'rubocop-rspec', '~> 1.15.0'
  gem 'simplecov-console'

end

group :development do
  gem 'travis'
  gem 'travis-lint'
  gem 'guard-rake'
end

group :system_tests do
  gem 'beaker'
  gem 'beaker-rspec'
  gem 'beaker-puppet_install_helper'
end

