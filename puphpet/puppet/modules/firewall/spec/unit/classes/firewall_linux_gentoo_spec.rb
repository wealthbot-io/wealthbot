require 'spec_helper'

describe 'firewall::linux::gentoo', type: :class do
  let(:facts) do
    {
      osfamily: 'Gentoo',
      operatingsystem: 'Gentoo',
    }
  end

  it {
    is_expected.to contain_service('iptables').with(
      ensure: 'running',
      enable: 'true',
    )
  }
  it {
    is_expected.to contain_service('ip6tables').with(
      ensure: 'running',
      enable: 'true',
    )
  }
  it {
    is_expected.to contain_package('net-firewall/iptables').with(
      ensure: 'present',
    )
  }

  context 'ensure => stopped' do
    let(:params) { { ensure: 'stopped' } }

    it {
      is_expected.to contain_service('iptables').with(
        ensure: 'stopped',
      )
    }
    it {
      is_expected.to contain_service('ip6tables').with(
        ensure: 'stopped',
      )
    }
  end

  context 'enable => false' do
    let(:params) { { enable: 'false' } }

    it {
      is_expected.to contain_service('iptables').with(
        enable: 'false',
      )
    }
    it {
      is_expected.to contain_service('ip6tables').with(
        enable: 'false',
      )
    }
  end
end
