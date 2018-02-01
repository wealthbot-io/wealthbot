require 'spec_helper'

describe 'firewall', type: :class do
  context 'kernel => Linux' do
    with_debian_facts
    it { is_expected.to contain_class('firewall::linux').with_ensure('running') }
  end

  # rubocop:disable RSpec/MultipleExpectations
  context 'kernel => Windows' do
    let(:facts) { { kernel: 'Windows' } }

    it { expect { is_expected.to contain_class('firewall::linux') }.to raise_error(Puppet::Error) }
  end

  context 'kernel => SunOS' do
    let(:facts) { { kernel: 'SunOS' } }

    it { expect { is_expected.to contain_class('firewall::linux') }.to raise_error(Puppet::Error) }
  end

  context 'kernel => Darwin' do
    let(:facts) { { kernel: 'Darwin' } }

    it { expect { is_expected.to contain_class('firewall::linux') }.to raise_error(Puppet::Error) }
  end

  context 'ensure => stopped' do
    with_debian_facts
    let(:params) { { ensure: 'stopped' } }

    it { is_expected.to contain_class('firewall::linux').with_ensure('stopped') }
  end

  context 'ensure => test' do
    let(:facts) { { kernel: 'Linux' } }
    let(:params) { { ensure: 'test' } }

    it { expect { is_expected.to contain_class('firewall::linux') }.to raise_error(Puppet::Error) }
  end

  context 'ebtables_manage => true' do
    let(:facts) { { kernel: 'Linux' } }
    let(:params) { { ebtables_manage: true } }

    it { expect { is_expected.to contain_package('ebtables') }.to raise_error(Puppet::Error) }
  end
  # rubocop:enable RSpec/MultipleExpectations
end
