require 'rubygems'
require 'puppetlabs_spec_helper/rake_tasks'
require 'puppet-syntax/tasks/puppet-syntax'
require 'puppet-lint/tasks/puppet-lint'
require 'yard'

exclude_paths = [
  "pkg/**/*",
  "vendor/**/*",
  "spec/**/*",
]

task :default => [:spec, :yard, :syntax, :lint]

desc "Run acceptance tests"
RSpec::Core::RakeTask.new(:acceptance) do |t|
  t.pattern = 'spec/acceptance'
end

YARD::Rake::YardocTask.new do |t|
  t.options = []
end

# Disable puppet-lint checks
PuppetLint.configuration.send("disable_80chars")
PuppetLint.configuration.send("disable_class_inherits_from_params_class")

# Ignore files outside this module
PuppetLint.configuration.ignore_paths = exclude_paths
PuppetSyntax.exclude_paths = exclude_paths
