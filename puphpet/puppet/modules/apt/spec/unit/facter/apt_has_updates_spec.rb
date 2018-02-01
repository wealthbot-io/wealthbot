require 'spec_helper'

describe 'apt_has_updates fact' do
  subject { Facter.fact(:apt_has_updates).value }

  after(:each) { Facter.clear }

  describe 'on non-Debian distro' do
    before(:each) do
      Facter.fact(:osfamily).expects(:value).at_least(1).returns 'RedHat'
    end
    it { is_expected.to be_nil }
  end

  describe 'on Debian based distro missing apt-get' do
    before(:each) do
      Facter.fact(:osfamily).expects(:value).at_least(1).returns 'Debian'
      File.stubs(:executable?) # Stub all other calls
      File.expects(:executable?).with('/usr/bin/apt-get').returns false
    end
    it { is_expected.to be_nil }
  end

  describe 'on Debian based distro' do
    before(:each) do
      Facter.fact(:osfamily).expects(:value).at_least(1).returns 'Debian'
      File.stubs(:executable?) # Stub all other calls
      Facter::Util::Resolution.stubs(:exec) # Catch all other calls
      File.expects(:executable?).with('/usr/bin/apt-get').returns true
      apt_output = "Inst tzdata [2015f-0+deb8u1] (2015g-0+deb8u1 Debian:stable-updates [all])\n" \
                   "Conf tzdata (2015g-0+deb8u1 Debian:stable-updates [all])\n" \
                   "Inst unhide.rb [13-1.1] (22-2~bpo8+1 Debian Backports:jessie-backports [all])\n" \
                   "Conf unhide.rb (22-2~bpo8+1 Debian Backports:jessie-backports [all])\n"
      Facter::Util::Resolution.expects(:exec).with('/usr/bin/apt-get -s -o Debug::NoLocking=true upgrade 2>&1').returns apt_output
    end
    it { is_expected.to be true }
  end
end
