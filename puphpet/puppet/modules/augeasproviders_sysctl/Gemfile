source 'https://rubygems.org'

if ENV.key?('PUPPET')
  puppetversion = "~> #{ENV['PUPPET']}"
else
  puppetversion = ['>= 2.7']
end
gem 'puppet', puppetversion

if ENV.key?('RUBY_AUGEAS')
  if ENV['RUBY_AUGEAS'] == '0.3.0'
    # pre-0.4.1 versions aren't available on rubygems
    rbaugversion = {:git => 'git://github.com/domcleal/ruby-augeas.git', :branch => '0.3.0-gem'}
  else
    rbaugversion = "~> #{ENV['RUBY_AUGEAS']}"
  end
else
  rbaugversion = ['~> 0.3']
end
gem 'ruby-augeas', rbaugversion

group :development, :unit_tests do
  gem 'rake', ' < 11.0',                                   :require => false if RUBY_VERSION =~ /^1\.8/
  gem 'rspec', '< 3.2',                                    :require => false if RUBY_VERSION =~ /^1\.8/
  gem 'json', '< 2.0',                                     :require => false if RUBY_VERSION =~ /^1\.[89]/
  gem 'json_pure', '< 2.0',                                :require => false if RUBY_VERSION =~ /^1\.[89]/
  gem 'rspec-puppet',                                      :require => false
  gem 'puppetlabs_spec_helper',                            :require => false
  gem 'metadata-json-lint',                                :require => false
  gem 'puppet-lint',                                       :require => false
  gem 'puppet-lint-unquoted_string-check',                 :require => false
  gem 'puppet-lint-empty_string-check',                    :require => false
  gem 'puppet-lint-spaceship_operator_without_tag-check',  :require => false
  gem 'puppet-lint-variable_contains_upcase',              :require => false
  gem 'puppet-lint-absolute_classname-check',              :require => false
  gem 'puppet-lint-undef_in_function-check',               :require => false
  gem 'puppet-lint-leading_zero-check',                    :require => false
  gem 'puppet-lint-trailing_comma-check',                  :require => false
  gem 'puppet-lint-file_ensure-check',                     :require => false
  gem 'puppet-lint-version_comparison-check',              :require => false
  gem 'rspec-puppet-facts',                                :require => false
  gem 'beaker-rspec',                                      :require => false
  gem 'simp-beaker-helpers',                               :require => false

  gem 'coveralls',                                         :require => false unless RUBY_VERSION =~ /^1\.8/
  gem 'simplecov', '~> 0.7.0',                             :require => false
  gem 'yard',                                              :require => false
  gem 'redcarpet', '~> 2.0',                               :require => false

  # mime-types-data requires Ruby version >= 2.0
  gem 'mime-types', '2.6.2' if RUBY_VERSION =~ /^1\.9/
end
