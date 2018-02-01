require 'spec_helper_acceptance'

describe 'rabbitmq policy on a vhost:' do
  context 'create policy resource' do
    it 'runs successfully' do
      pp = <<-EOS
      if $facts['os']['family'] == 'RedHat' {
        class { 'erlang': epel_enable => true }
        Class['erlang'] -> Class['rabbitmq']
      }
      class { 'rabbitmq':
        service_manage    => true,
        port              => 5672,
        delete_guest_user => true,
        admin_enable      => true,
      } ->

      rabbitmq_vhost { 'myhost':
        ensure => present,
      } ->

      rabbitmq_policy { 'ha-all@myhost':
        pattern    => '.*',
        priority   => 0,
        applyto    => 'all',
        definition => {
          'ha-mode'      => 'all',
          'ha-sync-mode' => 'automatic',
        },
      }

      rabbitmq_policy { 'eu-federation@myhost':
        pattern    => '^eu\\.',
        priority   => 0,
        applyto    => 'all',
        definition => {
          'federation-upstream-set' => 'all',
        },
      }
      EOS

      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)

      # Apply twice to ensure no changes the second time.
      apply_manifest(pp, catch_failures: true)
      expect(apply_manifest(pp, catch_changes: true).exit_code).to be_zero
    end

    # rubocop:disable RSpec/MultipleExpectations
    it 'has the policy' do
      shell('rabbitmqctl list_policies -p myhost') do |r|
        expect(r.stdout).to match(%r{myhost.*ha-all.*ha-sync-mode})
        expect(r.stdout).to match(%r{myhost.*eu-federation})
        expect(r.exit_code).to be_zero
      end
    end
    # rubocop:enable RSpec/MultipleExpectations
  end
end
