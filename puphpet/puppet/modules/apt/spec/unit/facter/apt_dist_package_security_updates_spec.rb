require 'spec_helper'

describe 'apt_package_security_dist_updates fact' do
  subject { Facter.fact(:apt_package_security_dist_updates).value }

  after(:each) { Facter.clear }

  describe 'when apt has no updates' do
    before(:each) do
      Facter.fact(:apt_has_dist_updates).stubs(:value).returns false
    end
    it { is_expected.to be nil }
  end

  describe 'when apt has updates' do
    before(:each) do
      Facter.fact(:osfamily).stubs(:value).returns 'Debian'
      File.stubs(:executable?) # Stub all other calls
      Facter::Util::Resolution.stubs(:exec) # Catch all other calls
      File.expects(:executable?).with('/usr/bin/apt-get').returns true
      Facter::Util::Resolution.expects(:exec).with('/usr/bin/apt-get -s -o Debug::NoLocking=true upgrade 2>&1').returns 'test'
      File.expects(:executable?).with('/usr/bin/apt-get').returns true
      Facter::Util::Resolution.expects(:exec).with('/usr/bin/apt-get -s -o Debug::NoLocking=true dist-upgrade 2>&1').returns apt_get_upgrade_output
    end

    describe 'on Debian' do
      let(:apt_get_upgrade_output) do
        "Inst extremetuxracer [2015f-0+deb8u1] (2015g-0+deb8u1 Debian:stable-updates [all])\n" \
          "Conf extremetuxracer (2015g-0+deb8u1 Debian:stable-updates [all])\n" \
          "Inst planet.rb [13-1.1] (22-2~bpo8+1 Debian Backports:jessie-backports [all])\n" \
          "Conf planet.rb (22-2~bpo8+1 Debian Backports:jessie-backports [all])\n" \
          "Inst vim [7.52.1-5] (7.52.1-5+deb9u2 Debian-Security:9/stable [amd64]) []\n" \
          "Conf vim (7.52.1-5+deb9u2 Debian-Security:9/stable [amd64])\n" \
      end

      if Facter.version < '2.0.0'
        it { is_expected.to eq('vim') }
      else
        it { is_expected.to eq(['vim']) }
      end
    end

    describe 'on Ubuntu' do
      let(:apt_get_upgrade_output) do
        "Inst extremetuxracer [2016f-0ubuntu0.16.04] (2016j-0ubuntu0.16.04 Ubuntu:16.04/xenial-security, Ubuntu:16.04/xenial-updates [all])\n" \
          "Conf extremetuxracer (2016j-0ubuntu0.16.04 Ubuntu:16.04/xenial-security, Ubuntu:16.04/xenial-updates [all])\n" \
          "Inst vim [7.47.0-1ubuntu2] (7.47.0-1ubuntu2.2 Ubuntu:16.04/xenial-security [amd64]) []\n" \
          "Conf vim (7.47.0-1ubuntu2.2 Ubuntu:16.04/xenial-security [amd64])\n" \
          "Inst onioncircuits [2:3.3.10-4ubuntu2] (2:3.3.10-4ubuntu2.3 Ubuntu:16.04/xenial-updates [amd64])\n" \
          "Conf onioncircuits (2:3.3.10-4ubuntu2.3 Ubuntu:16.04/xenial-updates [amd64])\n"
      end

      if Facter.version < '2.0.0'
        it { is_expected.to eq('extremetuxracer,vim') }
      else
        it { is_expected.to eq(%w[extremetuxracer vim]) }
      end
    end
  end
end
