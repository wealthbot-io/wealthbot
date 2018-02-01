require 'beaker-rspec/spec_helper'
require 'beaker-rspec/helpers/serverspec'
require 'beaker/puppet_install_helper'
require 'beaker/module_install_helper'

def install_bolt_on(hosts)
  on(hosts, "/opt/puppetlabs/puppet/bin/gem install --source http://rubygems.delivery.puppetlabs.net bolt -v '> 0.0.1'", acceptable_exit_codes: [0, 1]).stdout
end

def pe_install?
  ENV['PUPPET_INSTALL_TYPE'] =~ %r{pe}i
end

def puppet_version
  (on default, puppet('--version')).output.chomp
end

run_puppet_install_helper
install_bolt_on(hosts) unless pe_install?
install_module_on(hosts)
install_module_dependencies_on(hosts)

DEFAULT_PASSWORD = if default[:hypervisor] == 'vagrant'
                     'vagrant'
                   elsif default[:hypervisor] == 'vcloud'
                     'Qu@lity!'
                   end

def run_puppet_access_login(user:, password: '~!@#$%^*-/ aZ', lifetime: '5y')
  on(master, puppet('access', 'login', '--username', user, '--lifetime', lifetime), stdin: password)
end

def run_task(task_name:, params: nil, password: DEFAULT_PASSWORD)
  if pe_install?
    run_puppet_task(task_name: task_name, params: params)
  else
    run_bolt_task(task_name: task_name, params: params, password: password)
  end
end

def run_bolt_task(task_name:, params: nil, password: DEFAULT_PASSWORD)
  on(master, "/opt/puppetlabs/puppet/bin/bolt task run #{task_name} --modules /etc/puppetlabs/code/modules/service --nodes localhost --password #{password} #{params}", acceptable_exit_codes: [0, 1]).stdout # rubocop:disable Metrics/LineLength
end

def run_puppet_task(task_name:, params: nil)
  on(master, puppet('task', 'run', task_name, '--nodes', fact_on(master, 'fqdn'), params.to_s), acceptable_exit_codes: [0, 1]).stdout
end

def expect_multiple_regexes(result:, regexes:)
  regexes.each do |regex|
    expect(result).to match(regex)
  end
end

RSpec.configure do |c|
  c.filter_run :focus => true
  c.run_all_when_everything_filtered = true
  # apache on Ubuntu 10.04 and 12.04 doesn't like IPv6 VirtualHosts, so we skip ipv6 tests on those systems
  if fact('operatingsystem') == 'Ubuntu' and (fact('operatingsystemrelease') == '10.04' or fact('operatingsystemrelease') == '12.04')
    c.filter_run_excluding :ipv6 => true
  end

  # Readable test descriptions
  c.formatter = :documentation

  # detect the situation where PUP-5016 is triggered and skip the idempotency tests in that case
  # also note how fact('puppetversion') is not available because of PUP-4359
  if fact('osfamily') == 'Debian' && fact('operatingsystemmajrelease') == '8' && shell('puppet --version').stdout =~ /^4\.2/
    c.filter_run_excluding :skip_pup_5016 => true
  end

  # Configure all nodes in nodeset
  c.before :suite do
    run_puppet_access_login(user: 'admin') if pe_install?
    # net-tools required for netstat utility being used by be_listening
    if fact('osfamily') == 'RedHat' && fact('operatingsystemmajrelease') == '7'
      pp = <<-EOS
        package { 'net-tools': ensure => installed }
      EOS

      apply_manifest_on(agents, pp, :catch_failures => false)
    end

    if fact('osfamily') == 'Debian'
      # Make sure snake-oil certs are installed.
      shell 'apt-get install -y ssl-cert'
    end

    # Install module and dependencies
    hosts.each do |host|
      # Required for mod_passenger tests.
      if fact('osfamily') == 'RedHat'
        on host, puppet('module','install','stahnma/epel')
        on host, puppet('module','install','puppetlabs/inifile')
        #we need epel installed, so we can get plugins, wsgi, mime ...
        pp = <<-EOS
          class { 'epel': }
        EOS

        apply_manifest_on(host, pp, :catch_failures => true)
      end

      # Required for manifest to make mod_pagespeed repository available
      if fact('osfamily') == 'Debian'
        on host, puppet('module','install','puppetlabs-apt')
      end

      # Make sure selinux is disabled so the tests work.
      on host, puppet('apply', '-e',
                        %{"exec { 'setenforce 0': path   => '/bin:/sbin:/usr/bin:/usr/sbin', onlyif => 'which setenforce && getenforce | grep Enforcing', }"})
    end
  end
end

shared_examples "a idempotent resource" do
  it 'should apply with no errors' do
    apply_manifest(pp, :catch_failures => true)
  end

  it 'should apply a second time without changes', :skip_pup_5016 do
    apply_manifest(pp, :catch_changes => true)
  end
end
