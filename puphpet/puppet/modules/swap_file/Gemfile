source 'http://rubygems.org'

group :test do
  if puppetversion = ENV['PUPPET_GEM_VERSION']
    gem 'puppet', puppetversion, :require => false
  else
    gem 'puppet', ENV['PUPPET_VERSION'] || '~> 3.8.0'
  end

  # rspec must be v2 for ruby 1.8.7
  if RUBY_VERSION >= '1.8.7' and RUBY_VERSION < '1.9'
    gem 'rspec', '~> 2.0'
  end

  gem 'json_pure', '<= 2.0.1',  :require => false if RUBY_VERSION < '2.0.0'
  gem 'safe_yaml', '~> 1.0.4'

  gem 'rake'
  gem 'puppet-lint'
  gem 'rspec-puppet', :git => 'https://github.com/rodjek/rspec-puppet.git'
  gem 'puppet-syntax'
  gem 'puppetlabs_spec_helper'
  gem 'simplecov'
  gem 'simplecov-console'
  gem 'metadata-json-lint'
end

group :development do
  gem 'puppet-blacksmith'
  gem 'rubocop' if RUBY_VERSION >= '2.0.0'
  gem 'rubocop-rspec', '~> 1.6' if RUBY_VERSION >= '2.3.0'
  gem 'github_changelog_generator'
  gem 'activesupport', '< 5'
end

group :system_tests do
  gem "beaker",
    :git => 'https://github.com/puppetlabs/beaker',
    :ref => '3d21e843434a2e65152bd352c653511ddea0ce71',
    :require => false
  gem "beaker-rspec",
    :git => 'https://github.com/puppetlabs/beaker-rspec.git',
    :ref => 'a617f7bbc3e6ebb6ce49df32749d4ce93cef737d',
    :require => false
  gem 'serverspec'
  gem 'specinfra'
end

