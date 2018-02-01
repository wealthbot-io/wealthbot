require 'spec_helper_acceptance'

describe 'rabbitmq server:' do
  case fact('os.family')
  when 'RedHat'
    package_name = 'rabbitmq-server'
    service_name = 'rabbitmq-server'
  when 'SUSE'
    package_name = 'rabbitmq-server'
    service_name = 'rabbitmq-server'
  when 'Debian'
    package_name = 'rabbitmq-server'
    service_name = 'rabbitmq-server'
  when 'Archlinux'
    package_name = 'rabbitmq'
    service_name = 'rabbitmq'
  end

  context 'default class inclusion' do
    it 'runs successfully' do
      pp = <<-EOS
      class { 'rabbitmq::server': }
      if $facts['os']['family'] == 'RedHat' {
        class { 'erlang': epel_enable => true}
        Class['erlang'] -> Class['rabbitmq::server']
      }
      EOS

      # Apply twice to ensure no errors the second time.
      apply_manifest(pp, catch_failures: true)
      expect(apply_manifest(pp, catch_changes: true).exit_code).to be_zero
    end

    describe package(package_name) do
      it { is_expected.to be_installed }
    end

    describe service(service_name) do
      it { is_expected.to be_enabled }
      it { is_expected.to be_running }
    end
  end

  context 'disable and stop service' do
    it 'runs successfully' do
      pp = <<-EOS
      class { 'rabbitmq::server':
        service_ensure => 'stopped',
      }
      if $facts['os']['family'] == 'RedHat' {
        class { 'erlang': epel_enable => true}
        Class['erlang'] -> Class['rabbitmq::server']
      }
      EOS

      apply_manifest(pp, catch_failures: true)
    end

    describe service(service_name) do
      it { is_expected.not_to be_enabled }
      it { is_expected.not_to be_running }
    end
  end

  context 'service is unmanaged' do
    it 'runs successfully' do
      pp_pre = <<-EOS
      class { 'rabbitmq::server': }
      if $facts['os']['family'] == 'RedHat' {
        class { 'erlang': epel_enable => true}
        Class['erlang'] -> Class['rabbitmq::server']
      }
      EOS

      pp = <<-EOS
      class { 'rabbitmq::server':
        service_manage => false,
        service_ensure  => 'stopped',
      }
      if $facts['os']['family'] == 'RedHat' {
        class { 'erlang': epel_enable => true}
        Class['erlang'] -> Class['rabbitmq::server']
      }
      EOS

      apply_manifest(pp_pre, catch_failures: true)
      apply_manifest(pp, catch_failures: true)
    end

    describe service(service_name) do
      it { is_expected.to be_enabled }
      it { is_expected.to be_running }
    end
  end
end
