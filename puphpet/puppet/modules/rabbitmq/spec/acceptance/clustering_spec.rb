require 'spec_helper_acceptance'

describe 'rabbitmq clustering' do
  context 'rabbitmq::wipe_db_on_cookie_change => false' do
    it 'runs successfully' do
      pp = <<-EOS
      class { 'rabbitmq':
        config_cluster           => true,
        cluster_nodes            => ['rabbit1', 'rabbit2'],
        cluster_node_type        => 'ram',
        erlang_cookie            => 'TESTCOOKIE',
        wipe_db_on_cookie_change => false,
      }
      if $facts['os']['family'] == 'RedHat' {
        class { 'erlang': epel_enable => true}
        Class['erlang'] -> Class['rabbitmq']
      }
      EOS

      apply_manifest(pp, expect_failures: true)
    end

    describe file('/var/lib/rabbitmq/.erlang.cookie') do
      it { is_expected.not_to contain 'TESTCOOKIE' }
    end
  end
  context 'rabbitmq::wipe_db_on_cookie_change => true' do
    it 'runs successfully' do
      pp = <<-EOS
      class { 'rabbitmq':
        config_cluster           => true,
        cluster_nodes            => ['rabbit1', 'rabbit2'],
        cluster_node_type        => 'ram',
        erlang_cookie            => 'TESTCOOKIE',
        wipe_db_on_cookie_change => true,
      }
      if $facts['os']['family'] == 'RedHat' {
        class { 'erlang': epel_enable => true}
        Class['erlang'] -> Class['rabbitmq']
      }
      EOS

      apply_manifest(pp, catch_failures: true)
    end

    describe file('/etc/rabbitmq/rabbitmq.config') do
      it { is_expected.to be_file }
      it { is_expected.to contain 'cluster_nodes' }
      it { is_expected.to contain 'rabbit@rabbit1' }
      it { is_expected.to contain 'rabbit@rabbit2' }
      it { is_expected.to contain 'ram' }
    end

    describe file('/var/lib/rabbitmq/.erlang.cookie') do
      it { is_expected.to be_file }
      it { is_expected.to contain 'TESTCOOKIE' }
    end
  end
end
