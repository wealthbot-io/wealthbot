require 'spec_helper_acceptance'

describe 'rules spec' do
  describe 'complex ruleset 1' do
    before :all do
      iptables_flush_all_tables
      ip6tables_flush_all_tables
    end

    after :all do
      shell('iptables -t filter -P INPUT ACCEPT')
      shell('iptables -t filter -P FORWARD ACCEPT')
      shell('iptables -t filter -P OUTPUT ACCEPT')
      shell('iptables -t filter --flush')
    end

    pp1 = <<-EOS
        firewall { '090 forward allow local':
          chain       => 'FORWARD',
          proto       => 'all',
          source      => '10.0.0.0/8',
          destination => '10.0.0.0/8',
          action      => 'accept',
        }
        firewall { '100 forward standard allow tcp':
          chain       => 'FORWARD',
          source      => '10.0.0.0/8',
          destination => '!10.0.0.0/8',
          proto       => 'tcp',
          state       => 'NEW',
          port        => [80,443,21,20,22,53,123,43,873,25,465],
          action      => 'accept',
        }
        firewall { '100 forward standard allow udp':
          chain       => 'FORWARD',
          source      => '10.0.0.0/8',
          destination => '!10.0.0.0/8',
          proto       => 'udp',
          port        => [53,123],
          action      => 'accept',
        }
        firewall { '100 forward standard allow icmp':
          chain       => 'FORWARD',
          source      => '10.0.0.0/8',
          destination => '!10.0.0.0/8',
          proto       => 'icmp',
          action      => 'accept',
        }

        firewall { '090 ignore ipsec':
          table        => 'nat',
          chain        => 'POSTROUTING',
          outiface     => 'eth0',
          ipsec_policy => 'ipsec',
          ipsec_dir    => 'out',
          action       => 'accept',
        }
        firewall { '093 ignore 10.0.0.0/8':
          table       => 'nat',
          chain       => 'POSTROUTING',
          outiface    => 'eth0',
          destination => '10.0.0.0/8',
          action      => 'accept',
        }
        firewall { '093 ignore 172.16.0.0/12':
          table       => 'nat',
          chain       => 'POSTROUTING',
          outiface    => 'eth0',
          destination => '172.16.0.0/12',
          action      => 'accept',
        }
        firewall { '093 ignore 192.168.0.0/16':
          table       => 'nat',
          chain       => 'POSTROUTING',
          outiface    => 'eth0',
          destination => '192.168.0.0/16',
          action      => 'accept',
        }
        firewall { '100 masq outbound':
          table    => 'nat',
          chain    => 'POSTROUTING',
          outiface => 'eth0',
          jump     => 'MASQUERADE',
        }
        firewall { '101 redirect port 1':
          table   => 'nat',
          chain   => 'PREROUTING',
          iniface => 'eth0',
          proto   => 'tcp',
          dport   => '1',
          toports => '22',
          jump    => 'REDIRECT',
        }
    EOS
    it 'applies cleanly' do
      # Run it twice and test for idempotency
      apply_manifest(pp1, catch_failures: true)
      expect(apply_manifest(pp1, catch_failures: true).exit_code).to be_zero
    end
    regex_values = [
      %r{INPUT ACCEPT}, %r{FORWARD ACCEPT}, %r{OUTPUT ACCEPT},
      %r{-A FORWARD -s 10.0.0.0\/(8|255\.0\.0\.0) -d 10.0.0.0\/(8|255\.0\.0\.0) -m comment --comment \"090 forward allow local\" -j ACCEPT},
      %r{-A FORWARD -s 10.0.0.0\/(8|255\.0\.0\.0) (! -d|-d !) 10.0.0.0\/(8|255\.0\.0\.0) -p icmp -m comment --comment \"100 forward standard allow icmp\" -j ACCEPT},
      %r{-A FORWARD -s 10.0.0.0\/(8|255\.0\.0\.0) (! -d|-d !) 10.0.0.0\/(8|255\.0\.0\.0) -p tcp -m multiport --ports 80,443,21,20,22,53,123,43,873,25,465 -m state --state NEW -m comment --comment \"100 forward standard allow tcp\" -j ACCEPT}, # rubocop:disable Metrics/LineLength
      %r{-A FORWARD -s 10.0.0.0\/(8|255\.0\.0\.0) (! -d|-d !) 10.0.0.0\/(8|255\.0\.0\.0) -p udp -m multiport --ports 53,123 -m comment --comment \"100 forward standard allow udp\" -j ACCEPT}
    ]
    it 'contains appropriate rules' do
      shell('iptables-save') do |r|
        regex_values.each do |line|
          expect(r.stdout).to match(line)
        end
      end
    end
  end

  describe 'complex ruleset 2' do
    after :all do
      shell('iptables -t filter -P INPUT ACCEPT')
      shell('iptables -t filter -P FORWARD ACCEPT')
      shell('iptables -t filter -P OUTPUT ACCEPT')
      shell('iptables -t filter --flush')
    end

    pp2 = <<-EOS
        class { '::firewall': }

        Firewall {
          proto => 'all',
        }
        Firewallchain {
          purge  => 'true',
          ignore => [
            '--comment "[^"]*(?i:ignore)[^"]*"',
          ],
        }

        firewall { '001 ssh needed for beaker testing':
          proto   => 'tcp',
          dport   => '22',
          action  => 'accept',
          before => Firewallchain['INPUT:filter:IPv4'],
        }

        firewall { '010 INPUT allow established and related':
          proto  => 'all',
          state  => ['ESTABLISHED', 'RELATED'],
          action => 'accept',
          before => Firewallchain['INPUT:filter:IPv4'],
        }

        firewall { "011 reject local traffic not on loopback interface":
          iniface     => '! lo',
          proto       => 'all',
          destination => '127.0.0.1/8',
          action      => 'reject',
        }
        firewall { '012 accept loopback':
          iniface => 'lo',
          action  => 'accept',
          before => Firewallchain['INPUT:filter:IPv4'],
        }
        firewall { '020 ssh':
          proto  => 'tcp',
          dport  => '22',
          state  => 'NEW',
          action => 'accept',
          before => Firewallchain['INPUT:filter:IPv4'],
        }

        firewall { '025 smtp':
          outiface => '! eth0:2',
          chain    => 'OUTPUT',
          proto    => 'tcp',
          dport    => '25',
          state    => 'NEW',
          action   => 'accept',
        }
        firewall { '013 icmp echo-request':
          proto  => 'icmp',
          icmp   => 'echo-request',
          action => 'accept',
          source => '10.0.0.0/8',
        }
        firewall { '013 icmp destination-unreachable':
          proto  => 'icmp',
          icmp   => 'destination-unreachable',
          action => 'accept',
        }
        firewall { '013 icmp time-exceeded':
          proto  => 'icmp',
          icmp   => 'time-exceeded',
          action => 'accept',
        }
        firewall { '443 ssl on aliased interface':
          proto   => 'tcp',
          dport   => '443',
          state   => 'NEW',
          action  => 'accept',
          iniface => 'eth0:3',
        }

        firewallchain { 'LOCAL_INPUT_PRE:filter:IPv4': }
        firewall { '001 LOCAL_INPUT_PRE':
          jump    => 'LOCAL_INPUT_PRE',
          require => Firewallchain['LOCAL_INPUT_PRE:filter:IPv4'],
        }
        firewallchain { 'LOCAL_INPUT:filter:IPv4': }
        firewall { '900 LOCAL_INPUT':
          jump    => 'LOCAL_INPUT',
          require => Firewallchain['LOCAL_INPUT:filter:IPv4'],
        }
        firewallchain { 'INPUT:filter:IPv4':
          policy => 'drop',
          ignore => [
            '-j fail2ban-ssh',
            '--comment "[^"]*(?i:ignore)[^"]*"',
          ],
        }


        firewall { '010 allow established and related':
          chain  => 'FORWARD',
          proto  => 'all',
          state  => ['ESTABLISHED','RELATED'],
          action => 'accept',
          before => Firewallchain['FORWARD:filter:IPv4'],
        }
        firewallchain { 'FORWARD:filter:IPv4':
          policy => 'drop',
        }

        firewallchain { 'OUTPUT:filter:IPv4': }


        # purge unknown rules from mangle table
        firewallchain { ['PREROUTING:mangle:IPv4', 'INPUT:mangle:IPv4', 'FORWARD:mangle:IPv4', 'OUTPUT:mangle:IPv4', 'POSTROUTING:mangle:IPv4']: }

        # and the nat table
        firewallchain { ['PREROUTING:nat:IPv4', 'INPUT:nat:IPv4', 'OUTPUT:nat:IPv4', 'POSTROUTING:nat:IPv4']: }
    EOS
    it 'applies cleanly' do
      # Run it twice and test for idempotency
      apply_manifest(pp2, catch_failures: true)
      apply_manifest(pp2, catch_changes: do_catch_changes)
    end

    regex_values = [
      %r{INPUT DROP},
      %r{FORWARD DROP},
      %r{OUTPUT ACCEPT},
      %r{LOCAL_INPUT},
      %r{LOCAL_INPUT_PRE},
      %r{-A INPUT -m comment --comment \"001 LOCAL_INPUT_PRE\" -j LOCAL_INPUT_PRE},
      %r{-A INPUT -p tcp -m multiport --dports 22 -m comment --comment \"001 ssh needed for beaker testing\" -j ACCEPT},
      %r{-A INPUT -m state --state RELATED,ESTABLISHED -m comment --comment \"010 INPUT allow established and related\" -j ACCEPT},
      %r{-A INPUT -d 127.0.0.0\/(8|255\.0\.0\.0) (! -i|-i !) lo -m comment --comment \"011 reject local traffic not on loopback interface\" -j REJECT --reject-with icmp-port-unreachable},
      %r{-A INPUT -i lo -m comment --comment \"012 accept loopback\" -j ACCEPT},
      %r{-A INPUT -p icmp -m icmp --icmp-type 3 -m comment --comment \"013 icmp destination-unreachable\" -j ACCEPT},
      %r{-A INPUT -s 10.0.0.0\/(8|255\.0\.0\.0) -p icmp -m icmp --icmp-type 8 -m comment --comment \"013 icmp echo-request\" -j ACCEPT},
      %r{-A INPUT -p icmp -m icmp --icmp-type 11 -m comment --comment \"013 icmp time-exceeded\" -j ACCEPT},
      %r{-A INPUT -p tcp -m multiport --dports 22 -m state --state NEW -m comment --comment \"020 ssh\" -j ACCEPT},
      %r{-A INPUT -i eth0:3 -p tcp -m multiport --dports 443 -m state --state NEW -m comment --comment \"443 ssl on aliased interface\" -j ACCEPT},
      %r{-A INPUT -m comment --comment \"900 LOCAL_INPUT\" -j LOCAL_INPUT},
      %r{-A FORWARD -m state --state RELATED,ESTABLISHED -m comment --comment \"010 allow established and related\" -j ACCEPT},
      %r{-A OUTPUT (! -o|-o !) eth0:2 -p tcp -m multiport --dports 25 -m state --state NEW -m comment --comment \"025 smtp\" -j ACCEPT},
    ]
    it 'contains appropriate rules' do
      shell('iptables-save') do |r|
        regex_values.each do |line|
          expect(r.stdout).to match(line)
        end
      end
    end
  end
end
