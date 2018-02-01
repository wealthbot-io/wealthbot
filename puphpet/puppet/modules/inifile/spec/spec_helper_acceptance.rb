require 'beaker-rspec/spec_helper'
require 'beaker-rspec/helpers/serverspec'
require 'beaker/puppet_install_helper'
require 'beaker/module_install_helper'

run_puppet_install_helper
install_ca_certs unless ENV['PUPPET_INSTALL_TYPE'] =~ %r{pe}i
install_module_on(hosts)
install_module_dependencies_on(hosts)

RSpec.configure do |c|
  c.filter_run focus: true
  c.run_all_when_everything_filtered = true

  # Readable test descriptions
  c.formatter = :documentation
  c.treat_symbols_as_metadata_keys_with_true_values = true
end
