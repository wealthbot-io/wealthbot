require 'spec_helper'

describe 'Puppet::Util::Firewall' do
  let(:resource) do
    type = Puppet::Type.type(:firewall)
    provider = instance_double('provider')
    allow(provider).to receive(:name).and_return(:iptables)
    allow(Puppet::Type::Firewall).to receive(:defaultprovider).and_return(provider)
    type.new(name: '000 test foo')
  end

  before(:each) { resource }

  describe '#host_to_ip' do
    subject { resource }

    it { # rubocop:disable RSpec/MultipleExpectations
      allow(Resolv).to receive(:each_address).at_least(:once).with('puppetlabs.com').and_yield('96.126.112.51').and_yield('2001:DB8:4650::13:8A')
      expect(subject.host_to_ip('puppetlabs.com', :IPv4)).to eql '96.126.112.51/32'
      expect(subject.host_to_ip('puppetlabs.com', :IPv6)).to eql '2001:db8:4650::13:8a/128'
    }
    it { expect(subject.host_to_ip('96.126.112.51')).to eql '96.126.112.51/32' }
    it { expect(subject.host_to_ip('96.126.112.51/32')).to eql '96.126.112.51/32' }
    it { expect(subject.host_to_ip('2001:db8:85a3:0:0:8a2e:370:7334')).to eql '2001:db8:85a3::8a2e:370:7334/128' }
    it { expect(subject.host_to_ip('2001:db8:1234::/48')).to eql '2001:db8:1234::/48' }
    it { expect(subject.host_to_ip('0.0.0.0/0')).to be nil }
    it { expect(subject.host_to_ip('::/0')).to be nil }
  end

  describe '#host_to_mask' do
    subject { resource }

    it { # rubocop:disable RSpec/MultipleExpectations
      allow(Resolv).to receive(:each_address).at_least(:once).with('puppetlabs.com').and_yield('96.126.112.51').and_yield('2001:DB8:4650::13:8A')
      expect(subject.host_to_mask('puppetlabs.com', :IPv4)).to eql '96.126.112.51/32'
      expect(subject.host_to_mask('!puppetlabs.com', :IPv4)).to eql '! 96.126.112.51/32'
      expect(subject.host_to_mask('puppetlabs.com', :IPv6)).to eql '2001:db8:4650::13:8a/128'
      expect(subject.host_to_mask('!puppetlabs.com', :IPv6)).to eql '! 2001:db8:4650::13:8a/128'
    }
    it { expect(subject.host_to_mask('96.126.112.51', :IPv4)).to eql '96.126.112.51/32' }
    it { expect(subject.host_to_mask('!96.126.112.51', :IPv4)).to eql '! 96.126.112.51/32' }
    it { expect(subject.host_to_mask('96.126.112.51/32', :IPv4)).to eql '96.126.112.51/32' }
    it { expect(subject.host_to_mask('! 96.126.112.51/32', :IPv4)).to eql '! 96.126.112.51/32' }
    it { expect(subject.host_to_mask('2001:db8:85a3:0:0:8a2e:370:7334', :IPv6)).to eql '2001:db8:85a3::8a2e:370:7334/128' }
    it { expect(subject.host_to_mask('!2001:db8:85a3:0:0:8a2e:370:7334', :IPv6)).to eql '! 2001:db8:85a3::8a2e:370:7334/128' }
    it { expect(subject.host_to_mask('2001:db8:1234::/48', :IPv6)).to eql '2001:db8:1234::/48' }
    it { expect(subject.host_to_mask('! 2001:db8:1234::/48', :IPv6)).to eql '! 2001:db8:1234::/48' }
    it { expect(subject.host_to_mask('0.0.0.0/0', :IPv4)).to be nil }
    it { expect(subject.host_to_mask('!0.0.0.0/0', :IPv4)).to be nil }
    it { expect(subject.host_to_mask('::/0', :IPv6)).to be nil }
    it { expect(subject.host_to_mask('! ::/0', :IPv6)).to be nil }
  end

  describe '#icmp_name_to_number' do
    describe 'proto unsupported' do
      subject { resource }

      %w[inet5 inet8 foo].each do |proto|
        it "should reject invalid proto #{proto}" do
          expect { subject.icmp_name_to_number('echo-reply', proto) }
            .to raise_error(ArgumentError, "unsupported protocol family '#{proto}'")
        end
      end
    end

    describe 'proto IPv4' do
      proto = 'inet'
      subject { resource }

      it { expect(subject.icmp_name_to_number('echo-reply', proto)).to eql '0' }
      it { expect(subject.icmp_name_to_number('destination-unreachable', proto)).to eql '3' }
      it { expect(subject.icmp_name_to_number('source-quench', proto)).to eql '4' }
      it { expect(subject.icmp_name_to_number('redirect', proto)).to eql '6' }
      it { expect(subject.icmp_name_to_number('echo-request', proto)).to eql '8' }
      it { expect(subject.icmp_name_to_number('router-advertisement', proto)).to eql '9' }
      it { expect(subject.icmp_name_to_number('router-solicitation', proto)).to eql '10' }
      it { expect(subject.icmp_name_to_number('time-exceeded', proto)).to eql '11' }
      it { expect(subject.icmp_name_to_number('parameter-problem', proto)).to eql '12' }
      it { expect(subject.icmp_name_to_number('timestamp-request', proto)).to eql '13' }
      it { expect(subject.icmp_name_to_number('timestamp-reply', proto)).to eql '14' }
      it { expect(subject.icmp_name_to_number('address-mask-request', proto)).to eql '17' }
      it { expect(subject.icmp_name_to_number('address-mask-reply', proto)).to eql '18' }
    end

    describe 'proto IPv6' do
      proto = 'inet6'
      subject { resource }

      it { expect(subject.icmp_name_to_number('destination-unreachable', proto)).to eql '1' }
      it { expect(subject.icmp_name_to_number('time-exceeded', proto)).to eql '3' }
      it { expect(subject.icmp_name_to_number('parameter-problem', proto)).to eql '4' }
      it { expect(subject.icmp_name_to_number('echo-request', proto)).to eql '128' }
      it { expect(subject.icmp_name_to_number('echo-reply', proto)).to eql '129' }
      it { expect(subject.icmp_name_to_number('router-solicitation', proto)).to eql '133' }
      it { expect(subject.icmp_name_to_number('router-advertisement', proto)).to eql '134' }
      it { expect(subject.icmp_name_to_number('neighbour-solicitation', proto)).to eql '135' }
      it { expect(subject.icmp_name_to_number('neighbour-advertisement', proto)).to eql '136' }
      it { expect(subject.icmp_name_to_number('redirect', proto)).to eql '137' }
    end
  end

  describe '#string_to_port' do
    subject { resource }

    it { expect(subject.string_to_port('80', 'tcp')).to eql '80' }
    it { expect(subject.string_to_port(80, 'tcp')).to eql '80' }
    it { expect(subject.string_to_port('http', 'tcp')).to eql '80' }
    it { expect(subject.string_to_port('domain', 'udp')).to eql '53' }
  end

  describe '#to_hex32' do
    subject { resource }

    it { expect(subject.to_hex32('0')).to eql '0x0' }
    it { expect(subject.to_hex32('0x32')).to eql '0x32' }
    it { expect(subject.to_hex32('42')).to eql '0x2a' }
    it { expect(subject.to_hex32('4294967295')).to eql '0xffffffff' }
    it { expect(subject.to_hex32('4294967296')).to be nil }
    it { expect(subject.to_hex32('-1')).to be nil }
    it { expect(subject.to_hex32('bananas')).to be nil }
  end

  describe '#persist_iptables' do
    before(:each) { Facter.clear }
    subject { resource }

    # rubocop:disable RSpec/SubjectStub
    describe 'when proto is IPv4' do
      let(:proto) { 'IPv4' }

      it 'is expected to exec /sbin/service if running RHEL 6 or earlier' do
        allow(Facter.fact(:osfamily)).to receive(:value).and_return('RedHat')
        allow(Facter.fact(:operatingsystem)).to receive(:value).and_return('RedHat')
        allow(Facter.fact(:operatingsystemrelease)).to receive(:value).and_return('6')

        allow(subject).to receive(:execute).with(%w[/sbin/service iptables save])
        subject.persist_iptables(proto)
      end

      it 'is expected to exec for systemd if running RHEL 7 or greater' do
        allow(Facter.fact(:osfamily)).to receive(:value).and_return('RedHat')
        allow(Facter.fact(:operatingsystem)).to receive(:value).and_return('RedHat')
        allow(Facter.fact(:operatingsystemrelease)).to receive(:value).and_return('7')

        allow(subject).to receive(:execute).with(%w[/usr/libexec/iptables/iptables.init save])
        subject.persist_iptables(proto)
      end

      it 'is expected to exec for systemd if running Fedora 15 or greater' do
        allow(Facter.fact(:osfamily)).to receive(:value).and_return('RedHat')
        allow(Facter.fact(:operatingsystem)).to receive(:value).and_return('Fedora')
        allow(Facter.fact(:operatingsystemrelease)).to receive(:value).and_return('15')

        allow(subject).to receive(:execute).with(%w[/usr/libexec/iptables/iptables.init save])
        subject.persist_iptables(proto)
      end

      it 'is expected to exec for CentOS 6 identified from operatingsystem and operatingsystemrelease' do
        allow(Facter.fact(:osfamily)).to receive(:value).and_return(nil)
        allow(Facter.fact(:operatingsystem)).to receive(:value).and_return('CentOS')
        allow(Facter.fact(:operatingsystemrelease)).to receive(:value).and_return('6.5')
        allow(subject).to receive(:execute).with(%w[/sbin/service iptables save])
        subject.persist_iptables(proto)
      end

      it 'is expected to exec for CentOS 7 identified from operatingsystem and operatingsystemrelease' do
        allow(Facter.fact(:osfamily)).to receive(:value).and_return(nil)
        allow(Facter.fact(:operatingsystem)).to receive(:value).and_return('CentOS')
        allow(Facter.fact(:operatingsystemrelease)).to receive(:value).and_return('7.0.1406')
        allow(subject).to receive(:execute).with(%w[/usr/libexec/iptables/iptables.init save])
        subject.persist_iptables(proto)
      end

      it 'is expected to exec for Archlinux identified from osfamily' do
        allow(Facter.fact(:osfamily)).to receive(:value).and_return('Archlinux')
        allow(subject).to receive(:execute).with(['/bin/sh', '-c', '/usr/sbin/iptables-save > /etc/iptables/iptables.rules'])
        subject.persist_iptables(proto)
      end

      it 'is expected to raise a warning when exec fails' do # rubocop:disable RSpec/ExampleLength
        allow(Facter.fact(:osfamily)).to receive(:value).and_return('RedHat')
        allow(Facter.fact(:operatingsystem)).to receive(:value).and_return('RedHat')
        allow(Facter.fact(:operatingsystemrelease)).to receive(:value).and_return('6')

        allow(subject).to receive(:execute).with(%w[/sbin/service iptables save])
                                           .and_raise(Puppet::ExecutionFailure, 'some error')
        allow(subject).to receive(:warning).with('Unable to persist firewall rules: some error')
        subject.persist_iptables(proto)
      end
    end

    describe 'when proto is IPv6' do
      let(:proto) { 'IPv6' }

      it 'is expected to exec for newer Ubuntu' do
        allow(Facter.fact(:osfamily)).to receive(:value).and_return(nil)
        allow(Facter.fact(:operatingsystem)).to receive(:value).and_return('Ubuntu')
        allow(Facter.fact(:iptables_persistent_version)).to receive(:value).and_return('0.5.3ubuntu2')
        allow(subject).to receive(:execute).with(%w[/usr/sbin/service iptables-persistent save])
        subject.persist_iptables(proto)
      end

      it 'is expected to not exec for older Ubuntu which does not support IPv6' do
        allow(Facter.fact(:osfamily)).to receive(:value).and_return(nil)
        allow(Facter.fact(:operatingsystem)).to receive(:value).and_return('Ubuntu')
        allow(Facter.fact(:iptables_persistent_version)).to receive(:value).and_return('0.0.20090701')
        allow(subject).to receive(:execute).never
        subject.persist_iptables(proto)
      end

      it 'is expected to not exec for Suse which is not supported' do
        allow(Facter.fact(:osfamily)).to receive(:value).and_return('Suse')
        allow(subject).to receive(:execute).never
        subject.persist_iptables(proto)
      end
    end
    # rubocop:enable RSpec/SubjectStub
  end
end
