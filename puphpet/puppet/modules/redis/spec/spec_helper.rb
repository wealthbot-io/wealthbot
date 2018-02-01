require 'rubygems'
require 'puppetlabs_spec_helper/module_spec_helper'
require 'rspec-puppet-facts'
include RspecPuppetFacts

require 'puppet/indirector/catalog/compiler'

# Magic to add a catalog.exported_resources accessor
class Puppet::Resource::Catalog::Compiler
  alias_method :filter_exclude_exported_resources, :filter
  def filter(catalog)
    filter_exclude_exported_resources(catalog).tap do |filtered|
      # Every time we filter a catalog, add a .exported_resources to it.
      filtered.define_singleton_method(:exported_resources) do
        # The block passed to filter returns `false` if it wants to keep a resource. Go figure.
        catalog.filter { |r| !r.exported? }
      end
    end
  end
end

module Support
  module ExportedResources
    # Get exported resources as a catalog. Compatible with all catalog matchers, e.g.
    # `expect(exported_resources).to contain_myexportedresource('name').with_param('value')`
    def exported_resources
      # Catalog matchers expect something that can receive .call
      proc { subject.call.exported_resources }
    end
  end
end

def get_spec_fixtures_dir
  spec_dir = File.expand_path(File.dirname(__FILE__) + '/fixtures')

  raise "The directory #{spec_dir} does not exist" unless Dir.exists? spec_dir

  spec_dir
end

def read_fixture_file filename
  filename = get_spec_fixtures_dir + "/#{filename}"

  raise "The fixture file #{filename} doesn't exist" unless File.exists? filename

  File.read(filename)
end

def manifest_vars

  vars = {}

  case facts[:osfamily].to_s
  when 'RedHat'
    vars[:package_name] = 'redis'
    vars[:service_name] = 'redis'
    vars[:config_file_orig] = '/etc/redis.conf.puppet'
    vars[:ppa_repo] = nil
  when 'FreeBSD',
    vars[:package_name] = 'redis'
    vars[:service_name] = 'redis'
    vars[:config_file_orig] = '/usr/local/etc/redis.conf.puppet'
    vars[:ppa_repo] = nil
  when 'Debian'
    vars[:package_name] = 'redis-server'
    vars[:service_name] = 'redis-server'
    vars[:config_file_orig] = '/etc/redis/redis.conf.puppet'
    vars[:ppa_repo] = 'ppa:chris-lea/redis-server'
  when 'Archlinux'
    vars[:package_name] = 'redis'
    vars[:service_name] = 'redis'
    vars[:config_file] = '/etc/redis/redis.conf'
    vars[:config_file_orig] = '/etc/redis/redis.conf.puppet'
    vars[:ppa_repo] = nil
  end

  vars
end

def centos_facts
  {
    :operatingsystem => 'CentOS',
    :osfamily        => 'RedHat',
    :puppetversion   => '4.5.2',
  }
end

def debian_facts
  {
    :operatingsystem           => 'Debian',
    :osfamily                  => 'Debian',
    :operatingsystemmajrelease => '8',
    :puppetversion             => '4.5.2',
    :lsbdistcodename           => 'jessie',
  }
end

def freebsd_facts
  {
    :operatingsystem => 'FreeBSD',
    :osfamily        => 'FreeBSD',
    :puppetversion   => '4.5.2',
  }
end

def centos_6_facts
  {
    :operatingsystem => 'CentOS',
    :osfamily        => 'RedHat',
    :operatingsystemmajrelease => '6',
    :puppetversion   => '4.5.2',
  }
end

def centos_7_facts
  {
    :operatingsystem => 'CentOS',
    :osfamily        => 'RedHat',
    :operatingsystemmajrelease => '7',
    :puppetversion   => '4.5.2',
  }
end

def debian_wheezy_facts
  {
    :operatingsystem           => 'Debian',
    :osfamily                  => 'Debian',
    :operatingsystemmajrelease => '8',
    :puppetversion             => '4.5.2',
    :lsbdistcodename           => 'wheezy',
  }
end

def ubuntu_1404_facts
  {
    :operatingsystem           => 'Ubuntu',
    :osfamily                  => 'Debian',
    :operatingsystemmajrelease => '14.04',
    :puppetversion             => '4.5.2',
    :lsbdistcodename           => 'trusty',
  }
end

def ubuntu_1604_facts
  {
    :operatingsystem           => 'Ubuntu',
    :osfamily                  => 'Debian',
    :operatingsystemmajrelease => '16.04',
    :puppetversion             => '4.5.2',
    :lsbdistcodename           => 'xenial',
  }
end

def archlinux_facts
  {
    :operatingsystem => 'Archlinux',
    :osfamily        => 'Archlinux',
    :puppetversion   => '4.5.2',
  }
end

# Include code coverage report for all our specs
at_exit { RSpec::Puppet::Coverage.report! }
