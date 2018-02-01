require 'spec_helper'

describe 'firewall::linux::debian', type: :class do
  context 'Debian 7' do
    let(:facts) do
      {
        osfamily: 'Debian',
        operatingsystem: 'Debian',
        operatingsystemrelease: '7.0',
      }
    end

    it {
      is_expected.to contain_package('iptables-persistent').with(
        ensure: 'present',
      )
    }
    it {
      is_expected.to contain_service('iptables-persistent').with(
        ensure: nil,
        enable: 'true',
        require: 'Package[iptables-persistent]',
      )
    }
  end

  context 'deb7 enable => false' do
    let(:facts) do
      {
        osfamily: 'Debian',
        operatingsystem: 'Debian',
        operatingsystemrelease: '7.0',
      }
    end
    let(:params) { { enable: 'false' } }

    it {
      is_expected.to contain_service('iptables-persistent').with(
        enable: 'false',
      )
    }
  end

  context 'Debian 8' do
    let(:facts) do
      {
        osfamily: 'Debian',
        operatingsystem: 'Debian',
        operatingsystemrelease: 'jessie/sid',
      }
    end

    it {
      is_expected.to contain_package('iptables-persistent').with(
        ensure: 'present',
      )
    }
    it {
      is_expected.to contain_service('netfilter-persistent').with(
        ensure: nil,
        enable: 'true',
        require: 'Package[iptables-persistent]',
      )
    }
  end

  context 'deb8 enable => false' do
    let(:facts) do
      {
        osfamily: 'Debian',
        operatingsystem: 'Debian',
        operatingsystemrelease: 'jessie/sid',
      }
    end
    let(:params) { { enable: 'false' } }

    it {
      is_expected.to contain_service('netfilter-persistent').with(
        enable: 'false',
      )
    }
  end

  context 'Debian 8, alt operatingsystem' do
    let(:facts) do
      {
        osfamily: 'Debian',
        operatingsystem: 'Debian',
        operatingsystemrelease: '8.0',
      }
    end

    it {
      is_expected.to contain_package('iptables-persistent').with(
        ensure: 'present',
      )
    }
    it {
      is_expected.to contain_service('netfilter-persistent').with(
        ensure: nil,
        enable: 'true',
        require: 'Package[iptables-persistent]',
      )
    }
  end

  context 'deb8, alt operatingsystem, enable => false' do
    let(:facts) do
      {
        osfamily: 'Debian',
        operatingsystem: 'Debian',
        operatingsystemrelease: '8.0',
      }
    end
    let(:params) { { enable: 'false' } }

    it {
      is_expected.to contain_service('netfilter-persistent').with(
        enable: 'false',
      )
    }
  end
end
