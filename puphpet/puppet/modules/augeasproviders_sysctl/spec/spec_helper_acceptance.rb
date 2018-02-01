require 'beaker-rspec'
require 'tmpdir'
require 'yaml'

# Force FIPS off for these tests since it is not relevant.
ENV['BEAKER_fips'] = 'no'

require 'simp/beaker_helpers'
include Simp::BeakerHelpers

unless ENV['BEAKER_provision'] == 'no'
  hosts.each do |host|
    # Install Puppet
    if host.is_pe?
      install_pe
    else
      install_puppet
    end
  end
end

RSpec.configure do |c|
  c.include Helpers

  # ensure that environment OS is ready on each host
  fix_errata_on hosts

  # Readable test descriptions
  c.formatter = :documentation

  c.before :suite do
    copy_fixture_modules_to( hosts )
  end
end
