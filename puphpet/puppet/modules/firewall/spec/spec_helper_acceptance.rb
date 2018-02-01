require 'beaker-rspec'
require 'beaker/puppet_install_helper'
require 'beaker/module_install_helper'

def iptables_flush_all_tables
  %w[filter nat mangle raw].each do |t|
    expect(shell("iptables -t #{t} -F").stderr).to eq('')
  end
end

def ip6tables_flush_all_tables
  %w[filter mangle].each do |t|
    expect(shell("ip6tables -t #{t} -F").stderr).to eq('')
  end
end

def do_catch_changes
  if default['platform'] =~ %r{el-5}
    false
  else
    true
  end
end

run_puppet_install_helper
install_module_on(hosts)
install_module_dependencies_on(hosts)

RSpec.configure do |c|
  # Configure all nodes in nodeset
  c.before :suite do
    # Install module and dependencies
    hosts.each do |host|
      on host, puppet('module', 'install', 'puppetlabs-stdlib'), acceptable_exit_codes: [0]
      # the ubuntu-14.04 docker image doesn't carry the iptables command
      apply_manifest_on host, 'package { "iptables": ensure => installed }' if fact('osfamily') == 'Debian'
    end
  end
end
