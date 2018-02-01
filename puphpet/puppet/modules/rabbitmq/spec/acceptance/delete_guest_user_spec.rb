require 'spec_helper_acceptance'

describe 'rabbitmq with delete_guest_user' do
  context 'delete_guest_user' do
    it 'runs successfully' do
      pp = <<-EOS
      class { 'rabbitmq':
        port              => 5672,
        delete_guest_user => true,
      }
      if $facts['os']['family'] == 'RedHat' {
        class { 'erlang': epel_enable => true}
        Class['erlang'] -> Class['rabbitmq']
      }
      EOS

      apply_manifest(pp, catch_failures: true)
      shell('rabbitmqctl list_users > /tmp/rabbitmqctl_users')
    end

    describe file('/tmp/rabbitmqctl_users') do
      it { is_expected.to be_file }
      it { is_expected.not_to contain 'guest' }
    end
  end
end
