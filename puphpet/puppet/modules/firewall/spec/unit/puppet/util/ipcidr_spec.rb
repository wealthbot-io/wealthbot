require 'spec_helper'
require 'puppet/util/ipcidr'

describe 'Puppet::Util::IPCidr' do
  describe 'ipv4 address' do
    subject { ipaddr }

    let(:ipaddr) { Puppet::Util::IPCidr.new('96.126.112.51') }

    it { expect(subject.cidr).to eql '96.126.112.51/32' }
    it { expect(subject.prefixlen).to be 32 }
    it { expect(subject.netmask).to eql '255.255.255.255' }
  end

  describe 'single ipv4 address with cidr' do
    subject { ipcidr }

    let(:ipcidr) { Puppet::Util::IPCidr.new('96.126.112.51/32') }

    it { expect(subject.cidr).to eql '96.126.112.51/32' }
    it { expect(subject.prefixlen).to be 32 }
    it { expect(subject.netmask).to eql '255.255.255.255' }
  end

  describe 'ipv4 address range with cidr' do
    subject { ipcidr }

    let(:ipcidr) { Puppet::Util::IPCidr.new('96.126.112.0/24') }

    it { expect(subject.cidr).to eql '96.126.112.0/24' }
    it { expect(subject.prefixlen).to be 24 }
    it { expect(subject.netmask).to eql '255.255.255.0' }
  end

  # https://tickets.puppetlabs.com/browse/MODULES-3215
  describe 'ipv4 address range with invalid cidr' do
    subject { ipcidr }

    let(:ipcidr) { Puppet::Util::IPCidr.new('96.126.112.20/24') }

    specify { subject.cidr.should == '96.126.112.0/24' } # .20 is expected to
    # be silently dropped.
    specify { subject.prefixlen.should == 24 }
    specify { subject.netmask.should == '255.255.255.0' }
  end

  describe 'ipv4 open range with cidr' do
    subject { ipcidr }

    let(:ipcidr) { Puppet::Util::IPCidr.new('0.0.0.0/0') }

    it { expect(subject.cidr).to eql '0.0.0.0/0' }
    it { expect(subject.prefixlen).to be 0 }
    it { expect(subject.netmask).to eql '0.0.0.0' }
  end

  describe 'ipv6 address' do
    subject { ipaddr }

    let(:ipaddr) { Puppet::Util::IPCidr.new('2001:db8:85a3:0:0:8a2e:370:7334') }

    it { expect(subject.cidr).to eql '2001:db8:85a3::8a2e:370:7334/128' }
    it { expect(subject.prefixlen).to be 128 }
    it { expect(subject.netmask).to eql 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff' }
  end

  describe 'single ipv6 addr with cidr' do
    subject { ipaddr }

    let(:ipaddr) { Puppet::Util::IPCidr.new('2001:db8:85a3:0:0:8a2e:370:7334/128') }

    it { expect(subject.cidr).to eql '2001:db8:85a3::8a2e:370:7334/128' }
    it { expect(subject.prefixlen).to be 128 }
    it { expect(subject.netmask).to eql 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff' }
  end

  describe 'ipv6 addr range with cidr' do
    subject { ipaddr }

    let(:ipaddr) { Puppet::Util::IPCidr.new('2001:db8:1234::/48') }

    it { expect(subject.cidr).to eql '2001:db8:1234::/48' }
    it { expect(subject.prefixlen).to be 48 }
    it { expect(subject.netmask).to eql 'ffff:ffff:ffff:0000:0000:0000:0000:0000' }
  end

  describe 'ipv6 open range with cidr' do
    subject { ipaddr }

    let(:ipaddr) { Puppet::Util::IPCidr.new('::/0') }

    it { expect(subject.cidr).to eql '::/0' }
    it { expect(subject.prefixlen).to be 0 }
    it { expect(subject.netmask).to eql '0000:0000:0000:0000:0000:0000:0000:0000' }
  end
end
