require 'spec_helper_acceptance'

describe 'timezone class' do
  describe 'running puppet code' do
    it 'works with no errors' do
      pp = <<-PUPPET
        class { '::timezone': }
      PUPPET

      # Run it twice and test for idempotency
      apply_manifest(pp, :catch_failures => true)
      apply_manifest(pp, :catch_changes => true)
    end
  end
end
