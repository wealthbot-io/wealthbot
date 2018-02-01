require 'digest/sha1'
require 'rubygems'
require 'puppetlabs_spec_helper/rake_tasks'
require 'puppet_blacksmith/rake_tasks'
require 'net/http'
require 'uri'
require 'fileutils'
require 'rspec/core/rake_task'
require 'open-uri'
require 'puppet-strings'
require 'puppet-strings/tasks'
require 'yaml'
require 'json'
require_relative 'spec/spec_utilities'

# Workaround for certain rspec/beaker versions
module TempFixForRakeLastComment
  def last_comment
    last_description
  end
end
Rake::Application.send :include, TempFixForRakeLastComment

exclude_paths = [
  'pkg/**/*',
  'vendor/**/*',
  'spec/**/*'
]

require 'puppet-lint/tasks/puppet-lint'
require 'puppet-syntax/tasks/puppet-syntax'

PuppetSyntax.exclude_paths = exclude_paths
PuppetSyntax.future_parser = true if ENV['FUTURE_PARSER'] == 'true'

%w[
  80chars
  class_inherits_from_params_class
  class_parameter_defaults
  single_quote_string_with_variable
].each do |check|
  PuppetLint.configuration.send("disable_#{check}")
end

PuppetLint.configuration.ignore_paths = exclude_paths
PuppetLint.configuration.log_format = \
  '%{path}:%{line}:%{check}:%{KIND}:%{message}'

# Append custom cleanup tasks to :clean
task :clean => %i[
  artifact:clean
  spec_clean
]

desc 'remove outdated module fixtures'
task :spec_prune do
  mods = 'spec/fixtures/modules'
  fixtures = YAML.load_file '.fixtures.yml'
  fixtures['fixtures']['forge_modules'].each do |mod, params|
    next unless params.is_a? Hash \
      and params.key? 'ref' \
      and File.exist? "#{mods}/#{mod}"

    metadata = JSON.parse(File.read("#{mods}/#{mod}/metadata.json"))
    FileUtils.rm_rf "#{mods}/#{mod}" unless metadata['version'] == params['ref']
  end
end
task :spec_prep => [:spec_prune]

RSpec::Core::RakeTask.new(:spec_verbose) do |t|
  t.pattern = 'spec/{classes,defines,unit,functions,templates}/**/*_spec.rb'
  t.rspec_opts = [
    '--format documentation',
    '--require "ci/reporter/rspec"',
    '--format CI::Reporter::RSpecFormatter',
    '--color'
  ]
end
task :spec_verbose => :spec_prep

RSpec::Core::RakeTask.new(:spec_puppet) do |t|
  t.pattern = 'spec/{classes,defines,functions,templates,unit/facter}/**/*_spec.rb'
  t.rspec_opts = ['--color']
end
task :spec_puppet => :spec_prep

RSpec::Core::RakeTask.new(:spec_unit) do |t|
  t.pattern = 'spec/unit/{type,provider}/**/*_spec.rb'
  t.rspec_opts = ['--color']
end
task :spec_unit => :spec_prep

task :beaker => [:spec_prep, 'artifact:prep']

desc 'Run all linting/unit tests.'
task :intake => %i[
  syntax
  lint
  validate
  spec_unit
  spec_puppet
]

# Plumbing for snapshot tests
desc 'Run the snapshot tests'
RSpec::Core::RakeTask.new('beaker:snapshot') do |task|
  task.rspec_opts = ['--color']
  task.pattern = 'spec/acceptance/snapshot.rb'

  if Rake::Task.task_defined? 'artifact:snapshot:not_found'
    puts 'No snapshot artifacts found, skipping snapshot tests.'
    exit(0)
  end
end

beaker_node_sets.each do |node|
  desc "Run the snapshot tests against the #{node} nodeset"
  task "beaker:#{node}:snapshot" => %w[
    spec_prep
    artifact:prep
    artifact:snapshot:deb
    artifact:snapshot:rpm
  ] do
    ENV['BEAKER_set'] = node
    Rake::Task['beaker:snapshot'].reenable
    Rake::Task['beaker:snapshot'].invoke
  end
end

desc 'Run acceptance tests'
RSpec::Core::RakeTask.new('beaker:acceptance') do |c|
  c.pattern = 'spec/acceptance/0*_spec.rb'
end
task 'beaker:acceptance' => [:spec_prep, 'artifact:prep']

desc 'Setup a dummy host only, do not run any tests'
RSpec::Core::RakeTask.new('beaker:noop') do |c|
  ENV['BEAKER_destroy'] = 'no'
  c.pattern = 'spec/acceptance/*basic_spec.rb'
end
task 'beaker:noop' => [:spec_prep]

namespace :artifact do
  desc 'Fetch artifacts for tests'
  task :prep do
    dl_base = 'https://download.elastic.co/elasticsearch/elasticsearch'
    fetch_archives(
      'https://github.com/lmenezes/elasticsearch-kopf/archive/v2.1.1.zip' => \
      'elasticsearch-kopf.zip',
      "#{dl_base}/elasticsearch-2.3.5.deb" => 'elasticsearch-2.3.5.deb',
      "#{dl_base}/elasticsearch-2.3.5.rpm" => 'elasticsearch-2.3.5.rpm'
    )
  end

  namespace :snapshot do
    manifest = JSON.parse(
      open('https://snapshots.elastic.co/manifest.json').read
    )
    ENV['snapshot_version'] = manifest['version']

    downloads = manifest['projects']['elasticsearch']['packages'].select do |pkg, _|
      pkg =~ /(?:deb|rpm)/
    end.map do |package, urls|
      [
        package.split('.').last,
        urls.map do |type, remote|
          # This is temporary and can be removed once the links work.
          uri = URI(remote)

          [
            type,
            "#{uri.scheme}://#{uri.host}/#{uri.path.split('/')[2..-1].join('/')}"
          ]
        end.to_h
      ]
    end.to_h

    # We end up with something like:
    # {
    #   'rpm' => {'url' => 'https://...', 'sha_url' => 'https://...'},
    #   'deb' => {'url' => 'https://...', 'sha_url' => 'https://...'}
    # }
    # Note that checksums are currently broken on the Elastic unified release
    # side; once they start working we can verify them.

    if downloads.empty?
      puts 'No snapshot release available; skipping snapshot download'
      %w[deb rpm].each { |ext| task ext }
      task 'not_found'
    else
      # Download snapshot files
      downloads.each_pair do |extension, urls|
        filename = artifact urls['url']
        checksum = artifact urls['sha_url']
        link = artifact "elasticsearch-snapshot.#{extension}"

        task extension => link
        file link => filename do
          unless File.exist?(link) and File.symlink?(link) \
              and File.readlink(link) == filename
            File.delete link if File.exist? link
            File.symlink File.basename(filename), link
          end
        end

        # file filename => checksum do
        file filename do
          get urls['url'], filename
        end

        task checksum do
          File.delete checksum if File.exist? checksum
          get urls['sha_url'], checksum
        end
      end
    end
  end

  desc 'Purge fetched artifacts'
  task :clean do
    FileUtils.rm_rf(Dir.glob('spec/fixtures/artifacts/*'))
  end
end
