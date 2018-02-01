source ENV['GEM_SOURCE'] || "https://rubygems.org"

def location_for(place, fake_version = nil)
  if place =~ /^(git[:@][^#]*)#(.*)/
    [fake_version, { :git => $1, :branch => $2, :require => false }].compact
  elsif place =~ /^file:\/\/(.*)/
    ['>= 0', { :path => File.expand_path($1), :require => false }]
  else
    [place, { :require => false }]
  end
end

group :test do
  gem 'puppetlabs_spec_helper', '~> 1.2.2',                         :require => false
  gem 'rspec-puppet',                                               :require => false, :git => 'https://github.com/rodjek/rspec-puppet.git'
  gem 'rspec-puppet-facts',                                         :require => false
  gem 'rspec-puppet-utils',                                         :require => false
  gem 'puppet-lint-absolute_classname-check',                       :require => false
  gem 'puppet-lint-leading_zero-check',                             :require => false
  gem 'puppet-lint-trailing_comma-check',                           :require => false
  gem 'puppet-lint-version_comparison-check',                       :require => false
  gem 'puppet-lint-classes_and_types_beginning_with_digits-check',  :require => false
  gem 'puppet-lint-unquoted_string-check',                          :require => false
  gem 'puppet-lint-variable_contains_upcase',                       :require => false
  gem 'metadata-json-lint',                                         :require => false
  gem 'puppet-strings', '1.1.0',                                    :require => false
  gem 'puppet_facts',                                               :require => false
  gem 'rubocop-rspec', '~> 1.6',                                    :require => false if RUBY_VERSION >= '2.3.0'
  gem 'json_pure', '<= 2.0.1',                                      :require => false if RUBY_VERSION < '2.0.0'
  gem 'safe_yaml', '~> 1.0.4',                                      :require => false
  gem 'puppet-syntax',                                              :require => false, git: 'https://github.com/gds-operations/puppet-syntax.git'
  gem 'pry',                                                        :require => false
  gem 'rb-readline',                                                :require => false
  gem 'redis', '3.3.3',                                             :require => false
  gem 'mock_redis',                                                 :require => false
  gem 'rack', '1.6.8',                                              :require => false
  gem 'simp-rake-helpers', '3.6.0',                                 :require => false
end

group :development do
  gem 'puppet-blacksmith'
  gem 'github_changelog_generator', '1.13.2'
end

group :system_tests do
  gem "beaker"
  gem "beaker-rspec"
  gem 'beaker-puppet_install_helper',  :require => false
  gem 'beaker-module_install_helper'
  gem 'vagrant-wrapper'
  gem 'simp-beaker-helpers', :git => 'https://github.com/petems/rubygem-simp-beaker-helpers'
end

ENV['PUPPET_GEM_VERSION'].nil? ? puppetversion = '~> 4.0' : puppetversion = ENV['PUPPET_GEM_VERSION'].to_s
gem 'puppet', puppetversion, :require => false, :groups => [:test]

# vim: syntax=ruby
