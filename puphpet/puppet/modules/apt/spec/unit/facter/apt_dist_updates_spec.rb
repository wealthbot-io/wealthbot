require 'spec_helper'

describe 'apt_updates fact' do
  subject { Facter.fact(:apt_dist_updates).value }

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
      apt_output = "Inst extremetuxracer [2015f-0+deb8u1] (2015g-0+deb8u1 Debian:stable-updates [all])\n" \
                   "Conf extremetuxracer (2015g-0+deb8u1 Debian:stable-updates [all])\n" \
                   "Inst planet.rb [13-1.1] (22-2~bpo8+1 Debian Backports:jessie-backports [all])\n" \
                   "Conf planet.rb (22-2~bpo8+1 Debian Backports:jessie-backports [all])\n"
      Facter::Util::Resolution.expects(:exec).with('/usr/bin/apt-get -s -o Debug::NoLocking=true dist-upgrade 2>&1').returns apt_output
    end
    it { is_expected.to eq(2) }
  end
end
