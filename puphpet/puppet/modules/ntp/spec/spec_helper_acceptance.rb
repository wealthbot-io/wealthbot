require 'beaker-rspec'
require 'beaker/puppet_install_helper'
require 'beaker/module_install_helper'

UNSUPPORTED_PLATFORMS = %w[windows Darwin].freeze

run_puppet_install_helper
install_ca_certs unless ENV['PUPPET_INSTALL_TYPE'] =~ %r{pe}i
install_module_on(hosts)
install_module_dependencies_on(hosts)

unless ENV['RS_PROVISION'] == 'no' || ENV['BEAKER_provision'] == 'no'

  hosts.each do |host|
    # Need to disable update of ntp servers from DHCP, as subsequent restart of ntp causes test failures
    if fact_on(host, 'osfamily') == 'Debian'
      on host, 'dpkg-divert --divert /etc/dhcp-ntp.bak --local --rename --add /etc/dhcp/dhclient-exit-hooks.d/ntp'
      on host, 'dpkg-divert --divert /etc/dhcp3-ntp.bak --local --rename --add /etc/dhcp3/dhclient-exit-hooks.d/ntp'
    elsif fact_on(host, 'osfamily') == 'RedHat'
      on host, 'echo "PEERNTP=no" >> /etc/sysconfig/network'
    end
  end
end

RSpec.configure do |c|
  # Project root
  proj_root = File.expand_path(File.join(File.dirname(__FILE__), '..'))

  # Readable test descriptions
  c.formatter = :documentation

  # Configure all nodes in nodeset
  c.before :suite do
    hosts.each do |host|
      copy_module_to(host, source: proj_root, module_name: 'ntp')
    end
  end
end
