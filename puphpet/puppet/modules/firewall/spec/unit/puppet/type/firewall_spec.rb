#!/usr/bin/env rspec

require 'spec_helper'

firewall = Puppet::Type.type(:firewall)

describe firewall do # rubocop:disable RSpec/MultipleDescribes
  let(:firewall_class) { firewall }
  let(:provider) { instance_double('provider') }
  let(:resource) { firewall_class.new(name: '000 test foo') }

  before :each do
    allow(provider).to receive(:name).and_return(:iptables)
    allow(Puppet::Type::Firewall).to receive(:defaultprovider).and_return provider

    # Stub iptables version
    allow(Facter.fact(:iptables_version)).to receive(:value).and_return('1.4.2')
    allow(Facter.fact(:ip6tables_version)).to receive(:value).and_return('1.4.2')

    # Stub confine facts
    allow(Facter.fact(:kernel)).to receive(:value).and_return('Linux')
    allow(Facter.fact(:operatingsystem)).to receive(:value).and_return('Debian')
  end

  it 'has :name be its namevar' do
    expect(firewall_class.key_attributes).to eql [:name]
  end

  describe ':name' do
    it 'accepts a name' do
      resource[:name] = '000-test-foo'
      expect(resource[:name]).to eql '000-test-foo'
    end

    it 'does not accept a name with non-ASCII chars' do
      expect(-> { resource[:name] = '%*#^(#$' }).to raise_error(Puppet::Error)
    end
  end

  describe ':action' do
    it 'has no default' do
      res = firewall_class.new(name: '000 test')
      expect(res.parameters[:action]).to be nil
    end

    [:accept, :drop, :reject].each do |action|
      it "should accept value #{action}" do
        resource[:action] = action
        expect(resource[:action]).to eql action
      end
    end

    it 'fails when value is not recognized' do
      expect(-> { resource[:action] = 'not valid' }).to raise_error(Puppet::Error)
    end
  end

  describe ':chain' do
    [:INPUT, :FORWARD, :OUTPUT, :PREROUTING, :POSTROUTING].each do |chain|
      it "should accept chain value #{chain}" do
        resource[:chain] = chain
        expect(resource[:chain]).to eql chain
      end
    end

    it 'fails when the chain value is not recognized' do
      expect(-> { resource[:chain] = 'not valid' }).to raise_error(Puppet::Error)
    end
  end

  describe ':table' do
    [:nat, :mangle, :filter, :raw].each do |table|
      it "should accept table value #{table}" do
        resource[:table] = table
        expect(resource[:table]).to eql table
      end
    end

    it 'fails when table value is not recognized' do
      expect(-> { resource[:table] = 'not valid' }).to raise_error(Puppet::Error)
    end
  end

  describe ':proto' do
    [:ip, :tcp, :udp, :icmp, :esp, :ah, :vrrp, :igmp, :ipencap, :ipv4, :ipv6, :ospf, :gre, :pim, :all].each do |proto|
      it "should accept proto value #{proto}" do
        resource[:proto] = proto
        expect(resource[:proto]).to eql proto
      end
    end

    it 'fails when proto value is not recognized' do
      expect(-> { resource[:proto] = 'foo' }).to raise_error(Puppet::Error)
    end
  end

  describe ':jump' do
    it 'has no default' do
      res = firewall_class.new(name: '000 test')
      expect(res.parameters[:jump]).to be nil
    end

    %w[QUEUE RETURN DNAT SNAT LOG NFLOG MASQUERADE REDIRECT MARK].each do |jump|
      it "should accept jump value #{jump}" do
        resource[:jump] = jump
        expect(resource[:jump]).to eql jump
      end
    end

    %w[ACCEPT DROP REJECT].each do |jump|
      it "should now fail when value #{jump}" do
        expect(-> { resource[:jump] = jump }).to raise_error(Puppet::Error)
      end
    end

    it 'fails when jump value is not recognized' do
      expect(-> { resource[:jump] = '%^&*' }).to raise_error(Puppet::Error)
    end
  end

  [:source, :destination].each do |addr|
    describe addr do
      it "should accept a #{addr} as a string" do
        resource[addr] = '127.0.0.1'
        expect(resource[addr]).to eql '127.0.0.1/32'
      end
      ['0.0.0.0/0', '::/0'].each do |prefix|
        it "should be nil for zero prefix length address #{prefix}" do
          resource[addr] = prefix
          expect(resource[addr]).to be nil
        end
      end
      it "should accept a negated #{addr} as a string" do
        resource[addr] = '! 127.0.0.1'
        expect(resource[addr]).to eql '! 127.0.0.1/32'
      end
    end
  end

  [:dport, :sport].each do |port|
    describe port do
      it "should accept a #{port} as string" do
        resource[port] = '22'
        expect(resource[port]).to eql ['22']
      end

      it "should accept a #{port} as an array" do
        resource[port] = %w[22 23]
        expect(resource[port]).to eql %w[22 23]
      end

      it "should accept a #{port} as a number" do
        resource[port] = 22
        expect(resource[port]).to eql ['22']
      end

      it "should accept a #{port} as a hyphen separated range" do
        resource[port] = ['22-1000']
        expect(resource[port]).to eql ['22-1000']
      end

      it "should accept a #{port} as a combination of arrays of single and " \
        'hyphen separated ranges' do

        resource[port] = ['22-1000', '33', '3000-4000']
        expect(resource[port]).to eql ['22-1000', '33', '3000-4000']
      end

      it "should convert a port name for #{port} to its number" do
        resource[port] = 'ssh'
        expect(resource[port]).to eql ['22']
      end

      it "should not accept something invalid for #{port}" do
        expect { resource[port] = 'something odd' }.to raise_error(Puppet::Error, %r{^Parameter .+ failed.+Munging failed for value ".+" in class .+: no such service})
      end

      it "should not accept something invalid in an array for #{port}" do
        expect { resource[port] = ['something odd', 'something even odder'] }.to raise_error(Puppet::Error, %r{^Parameter .+ failed.+Munging failed for value ".+" in class .+: no such service})
      end
    end
  end

  describe 'port deprecated' do
    it 'raises a warning' do
      allow(Puppet).to receive(:warning).with %r{port to firewall is deprecated}
      resource[:port] = '22'
    end
  end

  [:dst_type, :src_type].each do |addrtype|
    describe addrtype do
      it 'has no default' do
        res = firewall_class.new(name: '000 test')
        expect(res.parameters[addrtype]).to be nil
      end
    end

    [:UNSPEC, :UNICAST, :LOCAL, :BROADCAST, :ANYCAST, :MULTICAST, :BLACKHOLE,
     :UNREACHABLE, :PROHIBIT, :THROW, :NAT, :XRESOLVE].each do |type|
      it "should accept #{addrtype} value #{type}" do
        resource[addrtype] = type
        expect(resource[addrtype]).to eql type
      end
    end

    it "should fail when #{addrtype} value is not recognized" do
      expect(-> { resource[addrtype] = 'foo' }).to raise_error(Puppet::Error)
    end
  end

  [:iniface, :outiface].each do |iface|
    describe iface do
      it "should accept #{iface} value as a string" do
        resource[iface] = 'eth1'
        expect(resource[iface]).to eql 'eth1'
      end
      it "should accept a negated #{iface} value as a string" do
        resource[iface] = '! eth1'
        expect(resource[iface]).to eql '! eth1'
      end
      it "should accept an interface alias for the #{iface} value as a string" do
        resource[iface] = 'eth1:2'
        expect(resource[iface]).to eql 'eth1:2'
      end
    end
  end

  [:tosource, :todest, :to].each do |addr|
    describe addr do
      it "should accept #{addr} value as a string" do
        resource[addr] = '127.0.0.1'
      end
    end
  end

  describe ':log_level' do
    values = {
      'panic' => '0',
      'alert' => '1',
      'crit'  => '2',
      'err'   => '3',
      'warn'  => '4',
      'warning' => '4',
      'not' => '5',
      'notice' => '5',
      'info' => '6',
      'debug' => '7',
    }

    values.each do |k, v|
      it {
        resource[:log_level] = k
        expect(resource[:log_level]).to eql v
      }

      it {
        resource[:log_level] = 3
        expect(resource[:log_level]).to be 3
      }

      it { expect(-> { resource[:log_level] = 'foo' }).to raise_error(Puppet::Error) }
    end
  end

  describe 'NFLOG' do
    describe ':nflog_group' do
      [0, 1, 5, 10].each do |v|
        it {
          resource[:nflog_group] = v
          expect(resource[:nflog_group]).to eq v
        }
      end

      [-3, 999_999].each do |v|
        it {
          expect(-> { resource[:nflog_group] = v }).to raise_error(Puppet::Error, %r{2\^16\-1})
        }
      end
    end

    describe ':nflog_prefix' do
      let(:valid_prefix) { 'This is a valid prefix' }
      let(:invalid_prefix) { 'This is not a valid prefix. !t is longer than 64 char@cters for sure. How do I know? I c0unted.' }

      it {
        resource[:nflog_prefix] = valid_prefix
        expect(resource[:nflog_prefix]).to eq valid_prefix
      }

      it {
        expect(-> { resource[:nflog_prefix] = invalid_prefix }).to raise_error(Puppet::Error, %r{64 characters})
      }
    end
  end

  describe ':icmp' do
    icmp_codes = {
      iptables: {
        '0' => 'echo-reply',
        '3' => 'destination-unreachable',
        '4' => 'source-quench',
        '6' => 'redirect',
        '8' => 'echo-request',
        '9' => 'router-advertisement',
        '10' => 'router-solicitation',
        '11' => 'time-exceeded',
        '12' => 'parameter-problem',
        '13' => 'timestamp-request',
        '14' => 'timestamp-reply',
        '17' => 'address-mask-request',
        '18' => 'address-mask-reply',
      },
      ip6tables: {
        '1' => 'destination-unreachable',
        '2' => 'too-big',
        '3' => 'time-exceeded',
        '4' => 'parameter-problem',
        '128' => 'echo-request',
        '129' => 'echo-reply',
        '133' => 'router-solicitation',
        '134' => 'router-advertisement',
        '137' => 'redirect',
      },
    }
    icmp_codes.each do |provider, values|
      describe provider do
        values.each do |k, v|
          resource_type = [:provider, :icmp]
          resource_value = [provider, v]
          resource_expected = [provider, k]
          it 'converts icmp string to number' do
            resource_type.each_with_index do |type, index|
              resource[type] = resource_value[index]
              expect(resource[type]).to eql resource_expected[index]
            end
          end
        end
      end
    end

    it 'accepts values as integers' do
      resource[:icmp] = 9
      expect(resource[:icmp]).to be 9
    end

    it 'fails if icmp type is "any"' do
      expect(-> { resource[:icmp] = 'any' }).to raise_error(Puppet::Error)
    end
    it 'fails if icmp type is an array' do
      expect(-> { resource[:icmp] = "['0', '8']" }).to raise_error(Puppet::Error)
    end

    it 'fails if icmp type cannot be mapped to a numeric' do
      expect(-> { resource[:icmp] = 'foo' }).to raise_error(Puppet::Error)
    end
  end

  describe ':state' do
    it 'accepts value as a string - INVALID' do
      resource[:state] = :INVALID
      expect(resource[:state]).to eql [:INVALID]
    end

    it 'accepts value as a string - UNTRACKED' do
      resource[:state] = :UNTRACKED
      expect(resource[:state]).to eql [:UNTRACKED]
    end

    it 'accepts value as an array - INVALID, NEW' do
      resource[:state] = [:INVALID, :NEW]
      expect(resource[:state]).to eql [:INVALID, :NEW]
    end

    it 'sorts values alphabetically - NEW, UNTRACKED, ESTABLISHED' do
      resource[:state] = [:NEW, :UNTRACKED, :ESTABLISHED]
      expect(resource[:state]).to eql [:ESTABLISHED, :NEW, :UNTRACKED]
    end
  end

  describe ':ctstate' do
    it 'accepts value as a string - INVALID' do
      resource[:ctstate] = :INVALID
      expect(resource[:ctstate]).to eql [:INVALID]
    end

    it 'accepts value as a string - UNTRACKED' do
      resource[:state] = :UNTRACKED
      expect(resource[:state]).to eql [:UNTRACKED]
    end

    it 'accepts value as an array - INVALID, NEW' do
      resource[:ctstate] = [:INVALID, :NEW]
      expect(resource[:ctstate]).to eql [:INVALID, :NEW]
    end

    it 'sorts values alphabetically - NEW, ESTABLISHED' do
      resource[:ctstate] = [:NEW, :ESTABLISHED]
      expect(resource[:ctstate]).to eql [:ESTABLISHED, :NEW]
    end
  end

  describe ':burst' do
    it 'accepts numeric values' do
      resource[:burst] = 12
      expect(resource[:burst]).to be 12
    end

    it 'fails if value is not numeric' do
      expect(-> { resource[:burst] = 'foo' }).to raise_error(Puppet::Error)
    end
  end

  describe ':recent' do
    %w[set update rcheck remove].each do |recent|
      it "should accept recent value #{recent}" do
        resource[:recent] = recent
        expect(resource[:recent]).to eql "--#{recent}"
      end
    end
  end

  describe ':action and :jump' do
    it 'allows only 1 to be set at a time' do
      expect {
        firewall_class.new(name: '001-test', action: 'accept', jump: 'custom_chain')
      }.to raise_error(RuntimeError, %r{Only one of the parameters 'action' and 'jump' can be set$})
    end
  end
  describe ':gid and :uid' do
    it 'allows me to set uid' do
      resource[:uid] = 'root'
      expect(resource[:uid]).to eql 'root'
    end
    it 'allows me to set uid as an array, and silently hide my error' do
      resource[:uid] = %w[root bobby]
      expect(resource[:uid]).to eql 'root'
    end
    it 'allows me to set gid' do
      resource[:gid] = 'root'
      expect(resource[:gid]).to eql 'root'
    end
    it 'allows me to set gid as an array, and silently hide my error' do
      resource[:gid] = %w[root bobby]
      expect(resource[:gid]).to eql 'root'
    end
  end

  describe ':set_mark' do
    ['1.3.2', '1.4.2'].each do |iptables_version|
      describe "with iptables #{iptables_version}" do
        before(:each) do
          Facter.clear
          allow(Facter.fact(:iptables_version)).to receive(:value).and_return iptables_version
          allow(Facter.fact(:ip6tables_version)).to receive(:value).and_return iptables_version
        end

        if iptables_version == '1.3.2'
          it 'allows me to set set-mark without mask' do
            resource[:set_mark] = '0x3e8'
            expect(resource[:set_mark]).to eql '0x3e8'
          end
          it 'converts int to hex without mask' do
            resource[:set_mark] = '1000'
            expect(resource[:set_mark]).to eql '0x3e8'
          end
          it 'fails if mask is present' do
            expect(-> { resource[:set_mark] = '0x3e8/0xffffffff' }).to raise_error(
              Puppet::Error, %r{iptables version #{iptables_version} does not support masks on MARK rules$}
            )
          end
        end

        if iptables_version == '1.4.2'
          it 'allows me to set set-mark with mask' do
            resource[:set_mark] = '0x3e8/0xffffffff'
            expect(resource[:set_mark]).to eql '0x3e8/0xffffffff'
          end
          it 'converts int to hex and add a 32 bit mask' do
            resource[:set_mark] = '1000'
            expect(resource[:set_mark]).to eql '0x3e8/0xffffffff'
          end
          it 'adds a 32 bit mask' do
            resource[:set_mark] = '0x32'
            expect(resource[:set_mark]).to eql '0x32/0xffffffff'
          end
          it 'uses the mask provided' do
            resource[:set_mark] = '0x32/0x4'
            expect(resource[:set_mark]).to eql '0x32/0x4'
          end
          it 'uses the mask provided and convert int to hex' do
            resource[:set_mark] = '1000/0x4'
            expect(resource[:set_mark]).to eql '0x3e8/0x4'
          end
          it 'fails if mask value is more than 32 bits' do
            expect(-> { resource[:set_mark] = '1/4294967296' }).to raise_error(
              Puppet::Error, %r{MARK mask must be integer or hex between 0 and 0xffffffff$}
            )
          end
          it 'fails if mask is malformed' do
            expect(-> { resource[:set_mark] = '1000/0xq4' }).to raise_error(
              Puppet::Error, %r{MARK mask must be integer or hex between 0 and 0xffffffff$}
            )
          end
        end

        ['/', '1000/', 'pwnie'].each do |bad_mark|
          it "should fail with malformed mark '#{bad_mark}'" do
            expect(-> { resource[:set_mark] = bad_mark }).to raise_error(Puppet::Error)
          end
        end
        it 'fails if mark value is more than 32 bits' do
          expect(-> { resource[:set_mark] = '4294967296' }).to raise_error(
            Puppet::Error, %r{MARK value must be integer or hex between 0 and 0xffffffff$}
          )
        end
      end
    end
  end

  [:chain, :jump].each do |param|
    describe param do
      # rubocop:disable RSpec/ExampleLength
      # rubocop:disable RSpec/MultipleExpectations
      it 'autorequires fwchain when table and provider are undefined' do
        resource[param] = 'FOO'
        expect(resource[:table]).to be :filter
        expect(resource[:provider]).to be :iptables

        chain = Puppet::Type.type(:firewallchain).new(name: 'FOO:filter:IPv4')
        catalog = Puppet::Resource::Catalog.new
        catalog.add_resource resource
        catalog.add_resource chain
        rel = resource.autorequire[0]
        expect(rel.source.ref).to eql chain.ref
        expect(rel.target.ref).to eql resource.ref
      end

      it 'autorequires fwchain when table is undefined and provider is ip6tables' do
        resource[param] = 'FOO'
        expect(resource[:table]).to be :filter
        resource[:provider] = :ip6tables

        chain = Puppet::Type.type(:firewallchain).new(name: 'FOO:filter:IPv6')
        catalog = Puppet::Resource::Catalog.new
        catalog.add_resource resource
        catalog.add_resource chain
        rel = resource.autorequire[0]
        expect(rel.source.ref).to eql chain.ref
        expect(rel.target.ref).to eql resource.ref
      end

      it 'autorequires fwchain when table is raw and provider is undefined' do
        resource[param] = 'FOO'
        resource[:table] = :raw
        expect(resource[:provider]).to be :iptables

        chain = Puppet::Type.type(:firewallchain).new(name: 'FOO:raw:IPv4')
        catalog = Puppet::Resource::Catalog.new
        catalog.add_resource resource
        catalog.add_resource chain
        rel = resource.autorequire[0]
        expect(rel.source.ref).to eql chain.ref
        expect(rel.target.ref).to eql resource.ref
      end

      it 'autorequires fwchain when table is raw and provider is ip6tables' do
        resource[param] = 'FOO'
        resource[:table] = :raw
        resource[:provider] = :ip6tables

        chain = Puppet::Type.type(:firewallchain).new(name: 'FOO:raw:IPv6')
        catalog = Puppet::Resource::Catalog.new
        catalog.add_resource resource
        catalog.add_resource chain
        rel = resource.autorequire[0]
        expect(rel.source.ref).to eql chain.ref
        expect(rel.target.ref).to eql resource.ref
      end

      # test where autorequire is still needed (table != filter)
      %w[INPUT OUTPUT FORWARD].each do |test_chain|
        it "should autorequire fwchain #{test_chain} when table is mangle and provider is undefined" do
          resource[param] = test_chain
          resource[:table] = :mangle
          expect(resource[:provider]).to be :iptables

          chain = Puppet::Type.type(:firewallchain).new(name: "#{test_chain}:mangle:IPv4")
          catalog = Puppet::Resource::Catalog.new
          catalog.add_resource resource
          catalog.add_resource chain
          rel = resource.autorequire[0]
          expect(rel.source.ref).to eql chain.ref
          expect(rel.target.ref).to eql resource.ref
        end

        it "should autorequire fwchain #{test_chain} when table is mangle and provider is ip6tables" do
          resource[param] = test_chain
          resource[:table] = :mangle
          resource[:provider] = :ip6tables

          chain = Puppet::Type.type(:firewallchain).new(name: "#{test_chain}:mangle:IPv6")
          catalog = Puppet::Resource::Catalog.new
          catalog.add_resource resource
          catalog.add_resource chain
          rel = resource.autorequire[0]
          expect(rel.source.ref).to eql chain.ref
          expect(rel.target.ref).to eql resource.ref
        end
      end

      # test of case where autorequire should not happen
      %w[INPUT OUTPUT FORWARD].each do |test_chain|
        it "should not autorequire fwchain #{test_chain} when table and provider are undefined" do
          resource[param] = test_chain
          expect(resource[:table]).to be :filter
          expect(resource[:provider]).to be :iptables

          chain = Puppet::Type.type(:firewallchain).new(name: "#{test_chain}:filter:IPv4")
          catalog = Puppet::Resource::Catalog.new
          catalog.add_resource resource
          catalog.add_resource chain
          rel = resource.autorequire[0]
          expect(rel).to be nil
        end

        it "should not autorequire fwchain #{test_chain} when table is undefined and provider is ip6tables" do
          resource[param] = test_chain
          expect(resource[:table]).to be :filter
          resource[:provider] = :ip6tables

          chain = Puppet::Type.type(:firewallchain).new(name: "#{test_chain}:filter:IPv6")
          catalog = Puppet::Resource::Catalog.new
          catalog.add_resource resource
          catalog.add_resource chain
          rel = resource.autorequire[0]
          expect(rel).to be nil
        end
      end
    end
  end

  describe ':chain and :jump' do
    it 'autorequires independent fwchains' do
      resource[:chain] = 'FOO'
      resource[:jump] = 'BAR'
      expect(resource[:table]).to be :filter
      expect(resource[:provider]).to be :iptables

      chain_foo = Puppet::Type.type(:firewallchain).new(name: 'FOO:filter:IPv4')
      chain_bar = Puppet::Type.type(:firewallchain).new(name: 'BAR:filter:IPv4')
      catalog = Puppet::Resource::Catalog.new
      catalog.add_resource resource
      catalog.add_resource chain_foo
      catalog.add_resource chain_bar
      rel = resource.autorequire
      expect(rel[0].source.ref).to eql chain_foo.ref
      expect(rel[0].target.ref).to eql resource.ref
      expect(rel[1].source.ref).to eql chain_bar.ref
      expect(rel[1].target.ref).to eql resource.ref
    end
  end
  # rubocop:enable RSpec/ExampleLength
  # rubocop:enable RSpec/MultipleExpectations

  describe ':pkttype' do
    [:multicast, :broadcast, :unicast].each do |pkttype|
      it "should accept pkttype value #{pkttype}" do
        resource[:pkttype] = pkttype
        expect(resource[:pkttype]).to eql pkttype
      end
    end

    it 'fails when the pkttype value is not recognized' do
      expect(-> { resource[:pkttype] = 'not valid' }).to raise_error(Puppet::Error)
    end
  end

  describe 'autorequire packages' do
    # rubocop:disable RSpec/ExampleLength
    # rubocop:disable RSpec/MultipleExpectations
    [:iptables, :ip6tables].each do |provider|
      it "provider #{provider} should autorequire package iptables" do
        resource[:provider] = provider
        expect(resource[:provider]).to eql provider
        package = Puppet::Type.type(:package).new(name: 'iptables')
        catalog = Puppet::Resource::Catalog.new
        catalog.add_resource resource
        catalog.add_resource package
        rel = resource.autorequire[0]
        expect(rel.source.ref).to eql package.ref
        expect(rel.target.ref).to eql resource.ref
      end

      it "provider #{provider} should autorequire packages iptables, iptables-persistent, and iptables-services" do
        resource[:provider] = provider
        expect(resource[:provider]).to eql provider
        packages = [
          Puppet::Type.type(:package).new(name: 'iptables'),
          Puppet::Type.type(:package).new(name: 'iptables-persistent'),
          Puppet::Type.type(:package).new(name: 'iptables-services'),
        ]
        catalog = Puppet::Resource::Catalog.new
        catalog.add_resource resource
        packages.each do |package|
          catalog.add_resource package
        end
        packages.zip(resource.autorequire) do |package, rel|
          expect(rel.source.ref).to eql package.ref
          expect(rel.target.ref).to eql resource.ref
        end
      end
    end
    # rubocop:enable RSpec/ExampleLength
    # rubocop:enable RSpec/MultipleExpectations
  end
  it 'is suitable' do
    expect(resource).to be_suitable
  end
end

describe 'firewall on unsupported platforms' do
  it 'is not suitable' do # rubocop:disable RSpec/ExampleLength
    # Stub iptables version
    allow(Facter.fact(:iptables_version)).to receive(:value).and_return(nil)
    allow(Facter.fact(:ip6tables_version)).to receive(:value).and_return(nil)

    # Stub confine facts
    allow(Facter.fact(:kernel)).to receive(:value).and_return('Darwin')
    allow(Facter.fact(:operatingsystem)).to receive(:value).and_return('Darwin')
    resource = firewall.new(name: '000 test foo', ensure: :present)

    # If our provider list is nil, then the Puppet::Transaction#evaluate will
    # say 'Error: Could not find a suitable provider for firewall' but there
    # isn't a unit testable way to get this.
    expect(resource).not_to be_suitable
  end
end
