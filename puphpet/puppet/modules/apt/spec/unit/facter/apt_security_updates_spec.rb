require 'spec_helper'

describe 'apt_security_updates fact' do
  subject { Facter.fact(:apt_security_updates).value }

  after(:each) { Facter.clear }

  describe 'when apt has no updates' do
    before(:each) do
      Facter.fact(:apt_has_updates).stubs(:value).returns false
    end
    it { is_expected.to be nil }
  end

  describe 'when apt has security updates' do
    before(:each) do
      Facter.fact(:osfamily).stubs(:value).returns 'Debian'
      File.stubs(:executable?) # Stub all other calls
      Facter::Util::Resolution.stubs(:exec) # Catch all other calls
      File.expects(:executable?).with('/usr/bin/apt-get').returns true
      Facter::Util::Resolution.expects(:exec).with('/usr/bin/apt-get -s -o Debug::NoLocking=true upgrade 2>&1').returns apt_get_upgrade_output
    end

    describe 'on Debian' do
      let(:apt_get_upgrade_output) do
        "Inst tzdata [2015f-0+deb8u1] (2015g-0+deb8u1 Debian:stable-updates [all])\n" \
          "Conf tzdata (2015g-0+deb8u1 Debian:stable-updates [all])\n" \
          "Inst unhide.rb [13-1.1] (22-2~bpo8+1 Debian Backports:jessie-backports [all])\n" \
          "Conf unhide.rb (22-2~bpo8+1 Debian Backports:jessie-backports [all])\n" \
          "Inst curl [7.52.1-5] (7.52.1-5+deb9u2 Debian-Security:9/stable [amd64]) []\n" \
          "Conf curl (7.52.1-5+deb9u2 Debian-Security:9/stable [amd64])\n" \
      end

      it { is_expected.to eq(1) }
    end

    describe 'on Ubuntu' do
      let(:apt_get_upgrade_output) do
        "Inst tzdata [2016f-0ubuntu0.16.04] (2016j-0ubuntu0.16.04 Ubuntu:16.04/xenial-security, Ubuntu:16.04/xenial-updates [all])\n" \
          "Conf tzdata (2016j-0ubuntu0.16.04 Ubuntu:16.04/xenial-security, Ubuntu:16.04/xenial-updates [all])\n" \
          "Inst curl [7.47.0-1ubuntu2] (7.47.0-1ubuntu2.2 Ubuntu:16.04/xenial-security [amd64]) []\n" \
          "Conf curl (7.47.0-1ubuntu2.2 Ubuntu:16.04/xenial-security [amd64])\n" \
          "Inst procps [2:3.3.10-4ubuntu2] (2:3.3.10-4ubuntu2.3 Ubuntu:16.04/xenial-updates [amd64])\n" \
          "Conf procps (2:3.3.10-4ubuntu2.3 Ubuntu:16.04/xenial-updates [amd64])\n"
      end

      it { is_expected.to eq(2) }
    end
  end
end
