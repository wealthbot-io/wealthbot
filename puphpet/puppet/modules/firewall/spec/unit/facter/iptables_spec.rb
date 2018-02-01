require 'spec_helper'

describe 'Facter::Util::Fact' do
  before(:each) do
    Facter.clear
    allow(Facter.fact(:kernel)).to receive(:value).and_return('Linux')
    allow(Facter.fact(:kernelrelease)).to receive(:value).and_return('2.6')
  end

  describe 'iptables_version' do
    it {
      allow(Facter::Util::Resolution).to receive(:exec).with('iptables --version')
                                                       .and_return('iptables v1.4.7')
      expect(Facter.fact(:iptables_version).value).to eql '1.4.7'
    }
  end

  describe 'ip6tables_version' do
    before(:each) do
      allow(Facter::Util::Resolution).to receive(:exec)
        .with('ip6tables --version').and_return('ip6tables v1.4.7')
    end
    it { expect(Facter.fact(:ip6tables_version).value).to eql '1.4.7' }
  end
end
