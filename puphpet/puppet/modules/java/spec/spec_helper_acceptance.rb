require 'beaker-rspec'
require 'beaker/puppet_install_helper'
require 'beaker/module_install_helper'

run_puppet_install_helper
install_module_on(hosts)
install_module_dependencies_on(hosts)

UNSUPPORTED_PLATFORMS = %w[Darwin windows].freeze

unless ENV['RS_PROVISION'] == 'no' || ENV['BEAKER_provision'] == 'no'
  hosts.each do |host|
    install_puppet_module_via_pmt_on(host, module_name: 'puppetlabs-apt')
  end
end

RSpec.configure do |c|
  # Readable test descriptions
  c.formatter = :documentation
end
