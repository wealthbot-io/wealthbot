require 'spec_helper'

describe 'apt_update_last_success fact' do
  subject { Facter.fact(:apt_update_last_success).value }

  before(:each) { Facter.clear }
  after(:each) { Facter.clear }

  describe 'on Debian based distro which has not yet created the update-success-stamp file' do
    it 'has a value of -1' do
      Facter.fact(:osfamily).stubs(:value).returns 'Debian'
      File.expects(:exist?).with('/var/lib/apt/periodic/update-success-stamp').returns false
      is_expected.to eq(-1)
    end
  end

  describe 'on Debian based distro which has created the update-success-stamp' do
    it 'has the value of the mtime of the file' do
      Facter.fact(:osfamily).stubs(:value).returns 'Debian'
      File.stubs(:exist?).returns true
      File.stubs(:mtime).returns 1_407_660_561
      is_expected.to eq(1_407_660_561)
    end
  end
end
