# To check the correct dependancies are set up for NTP.

require 'spec_helper'
describe 'ntp' do
  let(:facts) { { is_virtual: 'false' } }
  let :pre_condition do
    'file { "foo.rb":
      ensure => present,
      path => "/etc/tmp",
      notify => Service["ntp"] }'
  end

  on_supported_os.reject { |_, f| f[:os]['family'] == 'Solaris' }.each do |os, f|
    context "on #{os}" do
      let(:facts) do
        f.merge(super())
      end

      it { is_expected.to compile.with_all_deps }
      describe 'Testing the dependancies between the classes' do
        it { is_expected.to contain_class('ntp::install') }
        it { is_expected.to contain_class('ntp::config') }
        it { is_expected.to contain_class('ntp::service') }
        it { is_expected.to contain_class('ntp::install').that_comes_before('Class[ntp::config]') }
        it { is_expected.to contain_class('ntp::service').that_subscribes_to('Class[ntp::config]') }
        it { is_expected.to contain_file('foo.rb').that_notifies('Service[ntp]') }
      end
    end
  end
end
