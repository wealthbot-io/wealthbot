require 'spec_helper_acceptance'

describe 'firewall basics', docker: true do
  before :all do
    iptables_flush_all_tables
    ip6tables_flush_all_tables
  end

  describe 'name' do
    context 'valid' do
      pp1 = <<-EOS
          class { '::firewall': }
          firewall { '001 - test': ensure => present }
      EOS
      it 'applies cleanly' do
        apply_manifest(pp1, catch_failures: true)
      end
    end

    context 'invalid' do
      pp2 = <<-EOS
          class { '::firewall': }
          firewall { 'test': ensure => present }
      EOS
      it 'fails' do
        apply_manifest(pp2, expect_failures: true) do |r|
          expect(r.stderr).to match(%r{Invalid value "test".})
        end
      end
    end
  end

  describe 'ensure' do
    context 'default' do
      pp3 = <<-EOS
          class { '::firewall': }
          firewall { '555 - test':
            proto  => tcp,
            port   => '555',
            action => accept,
          }
      EOS
      it 'applies' do
        apply_manifest(pp3, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A INPUT -p tcp -m multiport --ports 555 -m comment --comment "555 - test" -j ACCEPT})
        end
      end
    end

    context 'present' do
      pp4 = <<-EOS
          class { '::firewall': }
          firewall { '555 - test':
            ensure => present,
            proto  => tcp,
            port   => '555',
            action => accept,
          }
      EOS
      it 'applies' do
        apply_manifest(pp4, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A INPUT -p tcp -m multiport --ports 555 -m comment --comment "555 - test" -j ACCEPT})
        end
      end
    end

    context 'absent' do
      pp5 = <<-EOS
          class { '::firewall': }
          firewall { '555 - test':
            ensure => absent,
            proto  => tcp,
            port   => '555',
            action => accept,
          }
      EOS
      it 'applies' do
        apply_manifest(pp5, catch_failures: true)
      end

      it 'does not contain the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).not_to match(%r{-A INPUT -p tcp -m multiport --ports 555 -m comment --comment "555 - test" -j ACCEPT})
        end
      end
    end
  end

  describe 'source' do
    context '192.168.2.0/24' do
      pp7 = <<-EOS
          class { '::firewall': }
          firewall { '556 - test':
            proto  => tcp,
            port   => '556',
            action => accept,
            source => '192.168.2.0/24',
          }
      EOS
      it 'applies' do
        apply_manifest(pp7, catch_failures: true)
        apply_manifest(pp7, catch_changes: do_catch_changes)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A INPUT -s 192.168.2.0\/(24|255\.255\.255\.0) -p tcp -m multiport --ports 556 -m comment --comment "556 - test" -j ACCEPT})
        end
      end
    end

    context '! 192.168.2.0/24' do
      pp8 = <<-EOS
          class { '::firewall': }
          firewall { '556 - test':
            proto  => tcp,
            port   => '556',
            action => accept,
            source => '! 192.168.2.0/24',
          }
      EOS
      it 'applies' do
        apply_manifest(pp8, catch_failures: true)
        apply_manifest(pp8, catch_changes: do_catch_changes)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A INPUT (! -s|-s !) 192.168.2.0\/(24|255\.255\.255\.0) -p tcp -m multiport --ports 556 -m comment --comment "556 - test" -j ACCEPT})
        end
      end
    end

    # Invalid address
    context '256.168.2.0/24' do
      pp9 = <<-EOS
          class { '::firewall': }
          firewall { '556 - test':
            proto  => tcp,
            port   => '556',
            action => accept,
            source => '256.168.2.0/24',
          }
      EOS
      it 'applies' do
        apply_manifest(pp9, expect_failures: true) do |r|
          expect(r.stderr).to match(%r{host_to_ip failed for 256.168.2.0\/(24|255\.255\.255\.0)})
        end
      end

      it 'does not contain the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).not_to match(%r{-A INPUT -s 256.168.2.0\/(24|255\.255\.255\.0) -p tcp -m multiport --ports 556 -m comment --comment "556 - test" -j ACCEPT})
        end
      end
    end
  end

  describe 'src_range' do
    context '192.168.1.1-192.168.1.10' do
      pp10 = <<-EOS
          class { '::firewall': }
          firewall { '557 - test':
            proto  => tcp,
            port   => '557',
            action => accept,
            src_range => '192.168.1.1-192.168.1.10',
          }
      EOS
      it 'applies' do
        apply_manifest(pp10, catch_failures: true)
        apply_manifest(pp10, catch_changes: do_catch_changes)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A INPUT -p tcp -m iprange --src-range 192.168.1.1-192.168.1.10 -m multiport --ports 557 -m comment --comment "557 - test" -j ACCEPT})
        end
      end
    end

    # Invalid IP
    context '392.168.1.1-192.168.1.10' do
      pp11 = <<-EOS
          class { '::firewall': }
          firewall { '557 - test':
            proto  => tcp,
            port   => '557',
            action => accept,
            src_range => '392.168.1.1-192.168.1.10',
          }
      EOS
      it 'applies' do
        apply_manifest(pp11, expect_failures: true) do |r|
          expect(r.stderr).to match(%r{Invalid IP address "392.168.1.1" in range "392.168.1.1-192.168.1.10"})
        end
      end

      it 'does not contain the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).not_to match(%r{-A INPUT -p tcp -m iprange --src-range 392.168.1.1-192.168.1.10 -m multiport --ports 557 -m comment --comment "557 - test" -j ACCEPT})
        end
      end
    end
  end

  describe 'destination' do
    context '192.168.2.0/24' do
      pp12 = <<-EOS
          class { '::firewall': }
          firewall { '558 - test':
            proto  => tcp,
            port   => '558',
            action => accept,
            destination => '192.168.2.0/24',
          }
      EOS
      it 'applies' do
        apply_manifest(pp12, catch_failures: true)
        apply_manifest(pp12, catch_changes: do_catch_changes)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A INPUT -d 192.168.2.0\/(24|255\.255\.255\.0) -p tcp -m multiport --ports 558 -m comment --comment "558 - test" -j ACCEPT})
        end
      end
    end

    context '! 192.168.2.0/24' do
      pp13 = <<-EOS
          class { '::firewall': }
          firewall { '558 - test':
            proto  => tcp,
            port   => '558',
            action => accept,
            destination => '! 192.168.2.0/24',
          }
      EOS
      it 'applies' do
        apply_manifest(pp13, catch_failures: true)
        apply_manifest(pp13, catch_changes: do_catch_changes)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A INPUT (! -d|-d !) 192.168.2.0\/(24|255\.255\.255\.0) -p tcp -m multiport --ports 558 -m comment --comment "558 - test" -j ACCEPT})
        end
      end
    end

    # Invalid address
    context '256.168.2.0/24' do
      pp14 = <<-EOS
          class { '::firewall': }
          firewall { '558 - test':
            proto  => tcp,
            port   => '558',
            action => accept,
            destination => '256.168.2.0/24',
          }
      EOS
      it 'applies' do
        apply_manifest(pp14, expect_failures: true) do |r|
          expect(r.stderr).to match(%r{host_to_ip failed for 256.168.2.0\/(24|255\.255\.255\.0)})
        end
      end

      it 'does not contain the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).not_to match(%r{-A INPUT -d 256.168.2.0\/(24|255\.255\.255\.0) -p tcp -m multiport --ports 558 -m comment --comment "558 - test" -j ACCEPT})
        end
      end
    end
  end

  describe 'dst_range' do
    context '192.168.1.1-192.168.1.10' do
      pp15 = <<-EOS
          class { '::firewall': }
          firewall { '559 - test':
            proto  => tcp,
            port   => '559',
            action => accept,
            dst_range => '192.168.1.1-192.168.1.10',
          }
      EOS
      it 'applies' do
        apply_manifest(pp15, catch_failures: true)
        apply_manifest(pp15, catch_changes: do_catch_changes)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A INPUT -p tcp -m iprange --dst-range 192.168.1.1-192.168.1.10 -m multiport --ports 559 -m comment --comment "559 - test" -j ACCEPT})
        end
      end
    end

    # Invalid IP
    context '392.168.1.1-192.168.1.10' do
      pp16 = <<-EOS
          class { '::firewall': }
          firewall { '559 - test':
            proto  => tcp,
            port   => '559',
            action => accept,
            dst_range => '392.168.1.1-192.168.1.10',
          }
      EOS
      it 'applies' do
        apply_manifest(pp16, expect_failures: true) do |r|
          expect(r.stderr).to match(%r{Invalid IP address "392.168.1.1" in range "392.168.1.1-192.168.1.10"})
        end
      end

      it 'does not contain the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).not_to match(%r{-A INPUT -p tcp -m iprange --dst-range 392.168.1.1-192.168.1.10 -m multiport --ports 559 -m comment --comment "559 - test" -j ACCEPT})
        end
      end
    end
  end

  describe 'sport' do
    context 'single port' do
      pp17 = <<-EOS
          class { '::firewall': }
          firewall { '560 - test':
            proto  => tcp,
            sport  => '560',
            action => accept,
          }
      EOS
      it 'applies' do
        apply_manifest(pp17, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A INPUT -p tcp -m multiport --sports 560 -m comment --comment "560 - test" -j ACCEPT})
        end
      end
    end

    context 'multiple ports' do
      pp18 = <<-EOS
          class { '::firewall': }
          firewall { '560 - test':
            proto  => tcp,
            sport  => '560-561',
            action => accept,
          }
      EOS
      it 'applies' do
        apply_manifest(pp18, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A INPUT -p tcp -m multiport --sports 560:561 -m comment --comment "560 - test" -j ACCEPT})
        end
      end
    end

    context 'invalid ports' do
      pp19 = <<-EOS
          class { '::firewall': }
          firewall { '560 - test':
            proto  => tcp,
            sport  => '9999560-561',
            action => accept,
          }
      EOS
      it 'applies' do
        apply_manifest(pp19, expect_failures: true) do |r|
          expect(r.stderr).to match(%r{invalid port\/service `9999560' specified})
        end
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).not_to match(%r{-A INPUT -p tcp -m multiport --sports 9999560-561 -m comment --comment "560 - test" -j ACCEPT})
        end
      end
    end
  end

  describe 'dport' do
    context 'single port' do
      pp20 = <<-EOS
          class { '::firewall': }
          firewall { '561 - test':
            proto  => tcp,
            dport  => '561',
            action => accept,
          }
      EOS
      it 'applies' do
        apply_manifest(pp20, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A INPUT -p tcp -m multiport --dports 561 -m comment --comment "561 - test" -j ACCEPT})
        end
      end
    end

    context 'multiple ports' do
      pp21 = <<-EOS
          class { '::firewall': }
          firewall { '561 - test':
            proto  => tcp,
            dport  => '561-562',
            action => accept,
          }
      EOS
      it 'applies' do
        apply_manifest(pp21, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A INPUT -p tcp -m multiport --dports 561:562 -m comment --comment "561 - test" -j ACCEPT})
        end
      end
    end

    context 'invalid ports' do
      pp22 = <<-EOS
          class { '::firewall': }
          firewall { '561 - test':
            proto  => tcp,
            dport  => '9999561-562',
            action => accept,
          }
      EOS
      it 'applies' do
        apply_manifest(pp22, expect_failures: true) do |r|
          expect(r.stderr).to match(%r{invalid port\/service `9999561' specified})
        end
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).not_to match(%r{-A INPUT -p tcp -m multiport --dports 9999561-562 -m comment --comment "560 - test" -j ACCEPT})
        end
      end
    end
  end

  describe 'port' do
    context 'single port' do
      pp23 = <<-EOS
          class { '::firewall': }
          firewall { '562 - test':
            proto  => tcp,
            port  => '562',
            action => accept,
          }
      EOS
      it 'applies' do
        apply_manifest(pp23, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A INPUT -p tcp -m multiport --ports 562 -m comment --comment "562 - test" -j ACCEPT})
        end
      end
    end

    context 'multiple ports' do
      pp24 = <<-EOS
          class { '::firewall': }
          firewall { '562 - test':
            proto  => tcp,
            port  => '562-563',
            action => accept,
          }
      EOS
      it 'applies' do
        apply_manifest(pp24, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A INPUT -p tcp -m multiport --ports 562:563 -m comment --comment "562 - test" -j ACCEPT})
        end
      end
    end

    context 'invalid ports' do
      pp25 = <<-EOS
          class { '::firewall': }
          firewall { '562 - test':
            proto  => tcp,
            port  => '9999562-563',
            action => accept,
          }
      EOS
      it 'applies' do
        apply_manifest(pp25, expect_failures: true) do |r|
          expect(r.stderr).to match(%r{invalid port\/service `9999562' specified})
        end
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).not_to match(%r{-A INPUT -p tcp -m multiport --ports 9999562-563 -m comment --comment "562 - test" -j ACCEPT})
        end
      end
    end
  end

  %w[dst_type src_type].each do |type|
    describe type.to_s do
      context 'MULTICAST' do
        pp26 = <<-EOS
            class { '::firewall': }
            firewall { '563 - test':
              proto  => tcp,
              action => accept,
              #{type} => 'MULTICAST',
            }
        EOS
        it 'applies' do
          apply_manifest(pp26, catch_failures: true)
        end

        it 'contains the rule' do
          shell('iptables-save') do |r|
            expect(r.stdout).to match(%r{-A INPUT -p tcp -m addrtype\s.*\sMULTICAST -m comment --comment "563 - test" -j ACCEPT})
          end
        end
      end

      context '! MULTICAST' do
        pp27 = <<-EOS
            class { '::firewall': }
            firewall { '563 - test inversion':
              proto  => tcp,
              action => accept,
              #{type} => '! MULTICAST',
            }
        EOS
        it 'applies' do
          apply_manifest(pp27, catch_failures: true)
          apply_manifest(pp27, catch_changes: do_catch_changes)
        end

        it 'contains the rule' do
          shell('iptables-save') do |r|
            expect(r.stdout).to match(%r{-A INPUT -p tcp -m addrtype( !\s.*\sMULTICAST|\s.*\s! MULTICAST) -m comment --comment "563 - test inversion" -j ACCEPT})
          end
        end
      end

      context 'BROKEN' do
        pp28 = <<-EOS
            class { '::firewall': }
            firewall { '563 - test':
              proto  => tcp,
              action => accept,
              #{type} => 'BROKEN',
            }
        EOS
        it 'fails' do
          apply_manifest(pp28, expect_failures: true) do |r|
            expect(r.stderr).to match(%r{Invalid value "BROKEN".})
          end
        end

        it 'does not contain the rule' do
          shell('iptables-save') do |r|
            expect(r.stdout).not_to match(%r{-A INPUT -p tcp -m addrtype\s.*\sBROKEN -m comment --comment "563 - test" -j ACCEPT})
          end
        end
      end
    end
  end

  describe 'tcp_flags' do
    context 'FIN,SYN ACK' do
      pp29 = <<-EOS
          class { '::firewall': }
          firewall { '564 - test':
            proto  => tcp,
            action => accept,
            tcp_flags => 'FIN,SYN ACK',
          }
      EOS
      it 'applies' do
        apply_manifest(pp29, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A INPUT -p tcp -m tcp --tcp-flags FIN,SYN ACK -m comment --comment "564 - test" -j ACCEPT})
        end
      end
    end
  end

  describe 'chain' do
    context 'INPUT' do
      pp30 = <<-EOS
          class { '::firewall': }
          firewall { '565 - test':
            proto  => tcp,
            action => accept,
            chain  => 'FORWARD',
          }
      EOS
      it 'applies' do
        apply_manifest(pp30, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A FORWARD -p tcp -m comment --comment "565 - test" -j ACCEPT})
        end
      end
    end
  end

  describe 'table' do
    context 'mangle' do
      pp31 = <<-EOS
          class { '::firewall': }
          firewall { '566 - test':
            proto  => tcp,
            action => accept,
            table  => 'mangle',
          }
      EOS
      it 'applies' do
        apply_manifest(pp31, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save -t mangle') do |r|
          expect(r.stdout).to match(%r{-A INPUT -p tcp -m comment --comment "566 - test" -j ACCEPT})
        end
      end
    end
    context 'nat' do
      pp32 = <<-EOS
          class { '::firewall': }
          firewall { '566 - test2':
            proto  => tcp,
            action => accept,
            table  => 'nat',
            chain  => 'OUTPUT',
          }
      EOS
      it 'applies' do
        apply_manifest(pp32, catch_failures: true)
      end

      it 'does not contain the rule' do
        shell('iptables-save -t nat') do |r|
          expect(r.stdout).to match(%r{-A OUTPUT -p tcp -m comment --comment "566 - test2" -j ACCEPT})
        end
      end
    end
  end

  describe 'jump' do
    after :all do
      iptables_flush_all_tables
    end

    context 'MARK' do
      pp33 = <<-EOS
          class { '::firewall': }
          firewallchain { 'TEST:filter:IPv4':
            ensure => present,
          }
          firewall { '567 - test':
            proto  => tcp,
            chain  => 'INPUT',
            jump  => 'TEST',
          }
      EOS
      it 'applies' do
        apply_manifest(pp33, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A INPUT -p tcp -m comment --comment "567 - test" -j TEST})
        end
      end
    end

    context 'jump and apply' do
      pp34 = <<-EOS
          class { '::firewall': }
          firewallchain { 'TEST:filter:IPv4':
            ensure => present,
          }
          firewall { '568 - test':
            proto  => tcp,
            chain  => 'INPUT',
            action => 'accept',
            jump  => 'TEST',
          }
      EOS
      it 'applies' do
        apply_manifest(pp34, expect_failures: true) do |r|
          expect(r.stderr).to match(%r{Only one of the parameters 'action' and 'jump' can be set})
        end
      end

      it 'does not contain the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).not_to match(%r{-A INPUT -p tcp -m comment --comment "568 - test" -j TEST})
        end
      end
    end
  end

  describe 'tosource' do
    context '192.168.1.1' do
      pp35 = <<-EOS
          class { '::firewall': }
          firewall { '568 - test':
            proto  => tcp,
            table  => 'nat',
            chain  => 'POSTROUTING',
            jump  => 'SNAT',
            tosource => '192.168.1.1',
          }
      EOS
      it 'applies' do
        apply_manifest(pp35, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save -t nat') do |r|
          expect(r.stdout).to match(%r{A POSTROUTING -p tcp -m comment --comment "568 - test" -j SNAT --to-source 192.168.1.1})
        end
      end
    end
  end

  describe 'todest' do
    context '192.168.1.1' do
      pp36 = <<-EOS
          class { '::firewall': }
          firewall { '569 - test':
            proto  => tcp,
            table  => 'nat',
            chain  => 'PREROUTING',
            jump   => 'DNAT',
            source => '200.200.200.200',
            todest => '192.168.1.1',
          }
      EOS
      it 'applies' do
        apply_manifest(pp36, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save -t nat') do |r|
          expect(r.stdout).to match(%r{-A PREROUTING -s 200.200.200.200(\/32)? -p tcp -m comment --comment "569 - test" -j DNAT --to-destination 192.168.1.1})
        end
      end
    end
  end

  describe 'toports' do
    context '192.168.1.1' do
      pp37 = <<-EOS
          class { '::firewall': }
          firewall { '570 - test':
            proto  => icmp,
            table  => 'nat',
            chain  => 'PREROUTING',
            jump  => 'REDIRECT',
            toports => '2222',
          }
      EOS
      it 'applies' do
        apply_manifest(pp37, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save -t nat') do |r|
          expect(r.stdout).to match(%r{-A PREROUTING -p icmp -m comment --comment "570 - test" -j REDIRECT --to-ports 2222})
        end
      end
    end
  end

  if default['platform'] !~ %r{el-5} && default['platform'] !~ %r{ubuntu-10\.04} && default['platform'] !~ %r{debian-6} && default['platform'] !~ %r{sles}
    describe 'checksum_fill' do
      context 'virbr' do
        pp38 = <<-EOS
            class { '::firewall': }
            firewall { '576 - test':
              proto  => udp,
              table  => 'mangle',
              outiface => 'virbr0',
              chain  => 'POSTROUTING',
              dport => '68',
              jump  => 'CHECKSUM',
              checksum_fill => true,
              provider => iptables,
            }
        EOS
        it 'applies' do
          apply_manifest(pp38, catch_failures: true)
        end

        it 'contains the rule' do
          shell('iptables-save -t mangle') do |r|
            expect(r.stdout).to match(%r{-A POSTROUTING -o virbr0 -p udp -m multiport --dports 68 -m comment --comment "576 - test" -j CHECKSUM --checksum-fill})
          end
        end
      end
    end

    describe 'checksum_fill6' do
      context 'virbr' do
        pp39 = <<-EOS
            class { '::firewall': }
            firewall { '576 - test':
              proto  => udp,
              table  => 'mangle',
              outiface => 'virbr0',
              chain  => 'POSTROUTING',
              dport => '68',
              jump  => 'CHECKSUM',
              checksum_fill => true,
              provider => ip6tables,
            }
        EOS
        it 'applies' do
          apply_manifest(pp39, catch_failures: true)
        end

        it 'contains the rule' do
          shell('ip6tables-save -t mangle') do |r|
            expect(r.stdout).to match(%r{-A POSTROUTING -o virbr0 -p udp -m multiport --dports 68 -m comment --comment "576 - test" -j CHECKSUM --checksum-fill})
          end
        end
      end
    end
  end

  # RHEL5 does not support --random
  if default['platform'] !~ %r{el-5} && default['platform'] !~ %r{sles-10}
    describe 'random' do
      context '192.168.1.1' do
        pp40 = <<-EOS
            class { '::firewall': }
            firewall { '570 - test 2':
              proto  => all,
              table  => 'nat',
              chain  => 'POSTROUTING',
              jump   => 'MASQUERADE',
              source => '172.30.0.0/16',
              random => true
            }
        EOS
        it 'applies' do
          apply_manifest(pp40, catch_failures: true)
          apply_manifest(pp40, catch_changes: do_catch_changes)
        end

        it 'contains the rule' do
          shell('iptables-save -t nat') do |r|
            expect(r.stdout).to match(%r{-A POSTROUTING -s 172\.30\.0\.0\/16 -m comment --comment "570 - test 2" -j MASQUERADE --random})
          end
        end
      end
    end
  end

  describe 'icmp' do
    context 'any' do
      pp41 = <<-EOS
          class { '::firewall': }
          firewall { '571 - test':
            proto  => icmp,
            icmp   => 'any',
          }
      EOS
      it 'fails' do
        apply_manifest(pp41, expect_failures: true) do |r|
          expect(r.stderr).to match(%r{This behaviour should be achieved by omitting or undefining the ICMP parameter})
        end
      end

      it 'does not contain the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).not_to match(%r{-A INPUT -p icmp -m comment --comment "570 - test" -m icmp --icmp-type 11})
        end
      end
    end
  end

  # iptables version 1.3.5 is not suppored by the ip6tables provider
  # iptables version 1.4.7 fails for multiple hl entries
  if default['platform'] !~ %r{(el-5|el-6|sles-10|sles-11)}
    describe 'hop_limit' do
      context '5' do
        pp42 = <<-EOS
            class { '::firewall': }
            firewall { '571 - test':
              ensure => present,
              proto => tcp,
              port   => '571',
              action => accept,
              hop_limit => '5',
              provider => 'ip6tables',
            }
        EOS
        it 'applies' do
          apply_manifest(pp42, catch_failures: true)
        end

        it 'contains the rule' do
          shell('ip6tables-save') do |r|
            expect(r.stdout).to match(%r{-A INPUT -p tcp -m multiport --ports 571 -m hl --hl-eq 5 -m comment --comment "571 - test" -j ACCEPT})
          end
        end
      end

      context 'invalid' do
        pp43 = <<-EOS
            class { '::firewall': }
            firewall { '571 - test':
              ensure => present,
              proto => tcp,
              port   => '571',
              action => accept,
              hop_limit => 'invalid',
              provider => 'ip6tables',
            }
        EOS
        it 'applies' do
          apply_manifest(pp43, expect_failures: true) do |r|
            expect(r.stderr).to match(%r{Invalid value "invalid".})
          end
        end

        it 'does not contain the rule' do
          shell('ip6tables-save') do |r|
            expect(r.stdout).not_to match(%r{-A INPUT -p tcp -m multiport --ports 571 -m comment --comment "571 - test" -m hl --hl-eq invalid -j ACCEPT})
          end
        end
      end
    end

    describe 'ishasmorefrags' do
      context 'true' do
        pp44 = <<-EOS
            class { '::firewall': }
            firewall { '587 - test':
              ensure => present,
              proto => tcp,
              port   => '587',
              action => accept,
              ishasmorefrags => true,
              provider => 'ip6tables',
            }
        EOS
        it 'applies' do
          apply_manifest(pp44, catch_failures: true)
        end

        it 'contains the rule' do
          shell('ip6tables-save') do |r|
            expect(r.stdout).to match(%r{A INPUT -p tcp -m frag --fragid 0 --fragmore -m multiport --ports 587 -m comment --comment "587 - test" -j ACCEPT})
          end
        end
      end

      context 'false' do
        pp45 = <<-EOS
            class { '::firewall': }
            firewall { '588 - test':
              ensure => present,
              proto => tcp,
              port   => '588',
              action => accept,
              ishasmorefrags => false,
              provider => 'ip6tables',
            }
        EOS
        it 'applies' do
          apply_manifest(pp45, catch_failures: true)
        end

        it 'contains the rule' do
          shell('ip6tables-save') do |r|
            expect(r.stdout).to match(%r{-A INPUT -p tcp -m multiport --ports 588 -m comment --comment "588 - test" -j ACCEPT})
          end
        end
      end
    end

    describe 'islastfrag' do
      context 'true' do
        pp46 = <<-EOS
            class { '::firewall': }
            firewall { '589 - test':
              ensure => present,
              proto => tcp,
              port   => '589',
              action => accept,
              islastfrag => true,
              provider => 'ip6tables',
            }
        EOS
        it 'applies' do
          apply_manifest(pp46, catch_failures: true)
        end

        it 'contains the rule' do
          shell('ip6tables-save') do |r|
            expect(r.stdout).to match(%r{-A INPUT -p tcp -m frag --fragid 0 --fraglast -m multiport --ports 589 -m comment --comment "589 - test" -j ACCEPT})
          end
        end
      end

      context 'false' do
        pp47 = <<-EOS
            class { '::firewall': }
            firewall { '590 - test':
              ensure => present,
              proto => tcp,
              port   => '590',
              action => accept,
              islastfrag => false,
              provider => 'ip6tables',
            }
        EOS
        it 'applies' do
          apply_manifest(pp47, catch_failures: true)
        end

        it 'contains the rule' do
          shell('ip6tables-save') do |r|
            expect(r.stdout).to match(%r{-A INPUT -p tcp -m multiport --ports 590 -m comment --comment "590 - test" -j ACCEPT})
          end
        end
      end
    end

    describe 'isfirstfrag' do
      context 'true' do
        pp48 = <<-EOS
            class { '::firewall': }
            firewall { '591 - test':
              ensure => present,
              proto => tcp,
              port   => '591',
              action => accept,
              isfirstfrag => true,
              provider => 'ip6tables',
            }
        EOS
        it 'applies' do
          apply_manifest(pp48, catch_failures: true)
        end

        it 'contains the rule' do
          shell('ip6tables-save') do |r|
            expect(r.stdout).to match(%r{-A INPUT -p tcp -m frag --fragid 0 --fragfirst -m multiport --ports 591 -m comment --comment "591 - test" -j ACCEPT})
          end
        end
      end

      context 'false' do
        pp49 = <<-EOS
            class { '::firewall': }
            firewall { '592 - test':
              ensure => present,
              proto => tcp,
              port   => '592',
              action => accept,
              isfirstfrag => false,
              provider => 'ip6tables',
            }
        EOS
        it 'applies' do
          apply_manifest(pp49, catch_failures: true)
        end

        it 'contains the rule' do
          shell('ip6tables-save') do |r|
            expect(r.stdout).to match(%r{-A INPUT -p tcp -m multiport --ports 592 -m comment --comment "592 - test" -j ACCEPT})
          end
        end
      end
    end

    describe 'tcp_flags' do
      context 'FIN,SYN ACK' do
        pp50 = <<-EOS
          class { '::firewall': }
          firewall { '593 - test':
            proto  => tcp,
            action => accept,
            tcp_flags => 'FIN,SYN ACK',
            provider => 'ip6tables',
          }
        EOS
        it 'applies' do
          apply_manifest(pp50, catch_failures: true)
        end

        it 'contains the rule' do
          shell('ip6tables-save') do |r|
            expect(r.stdout).to match(%r{-A INPUT -p tcp -m tcp --tcp-flags FIN,SYN ACK -m comment --comment "593 - test" -j ACCEPT})
          end
        end
      end
    end

    describe 'src_range' do
      context '2001:db8::1-2001:db8::ff' do
        pp51 = <<-EOS
          class { '::firewall': }
          firewall { '601 - test':
            proto     => tcp,
            port      => '601',
            action    => accept,
            src_range => '2001:db8::1-2001:db8::ff',
            provider  => 'ip6tables',
          }
        EOS
        it 'applies' do
          apply_manifest(pp51, catch_failures: true)
          apply_manifest(pp51, catch_changes: do_catch_changes)
        end

        it 'contains the rule' do
          shell('ip6tables-save') do |r|
            expect(r.stdout).to match(%r{-A INPUT -p tcp -m iprange --src-range 2001:db8::1-2001:db8::ff -m multiport --ports 601 -m comment --comment "601 - test" -j ACCEPT})
          end
        end
      end

      # Invalid IP
      context '2001::db8::1-2001:db8::ff' do
        pp52 = <<-EOS
          class { '::firewall': }
          firewall { '601 - test':
            proto     => tcp,
            port      => '601',
            action    => accept,
            provider  => 'ip6tables',
            src_range => '2001::db8::1-2001:db8::ff',
          }
        EOS
        it 'applies' do
          apply_manifest(pp52, expect_failures: true) do |r|
            expect(r.stderr).to match(%r{Invalid IP address "2001::db8::1" in range "2001::db8::1-2001:db8::ff"})
          end
        end

        it 'does not contain the rule' do
          shell('ip6tables-save') do |r|
            expect(r.stdout).not_to match(%r{-A INPUT -p tcp -m iprange --src-range 2001::db8::1-2001:db8::ff -m multiport --ports 601 -m comment --comment "601 - test" -j ACCEPT})
          end
        end
      end
    end

    describe 'dst_range' do
      context '2001:db8::1-2001:db8::ff' do
        pp53 = <<-EOS
          class { '::firewall': }
          firewall { '602 - test':
            proto     => tcp,
            port      => '602',
            action    => accept,
            dst_range => '2001:db8::1-2001:db8::ff',
            provider  => 'ip6tables',
          }
        EOS
        it 'applies' do
          apply_manifest(pp53, catch_failures: true)
          apply_manifest(pp53, catch_changes: do_catch_changes)
        end

        it 'contains the rule' do
          shell('ip6tables-save') do |r|
            expect(r.stdout).to match(%r{-A INPUT -p tcp -m iprange --dst-range 2001:db8::1-2001:db8::ff -m multiport --ports 602 -m comment --comment "602 - test" -j ACCEPT})
          end
        end
      end

      # Invalid IP
      context '2001::db8::1-2001:db8::ff' do
        pp54 = <<-EOS
          class { '::firewall': }
          firewall { '602 - test':
            proto     => tcp,
            port      => '602',
            action    => accept,
            provider  => 'ip6tables',
            dst_range => '2001::db8::1-2001:db8::ff',
          }
        EOS
        it 'applies' do
          apply_manifest(pp54, expect_failures: true) do |r|
            expect(r.stderr).to match(%r{Invalid IP address "2001::db8::1" in range "2001::db8::1-2001:db8::ff"})
          end
        end

        it 'does not contain the rule' do
          shell('ip6tables-save') do |r|
            expect(r.stdout).not_to match(%r{-A INPUT -p tcp -m iprange --dst-range 2001::db8::1-2001:db8::ff -m multiport --ports 602 -m comment --comment "602 - test" -j ACCEPT})
          end
        end
      end
    end

    describe 'mac_source' do
      context '0A:1B:3C:4D:5E:6F' do
        pp55 = <<-EOS
          class { '::firewall': }
          firewall { '604 - test':
            ensure      => present,
            source      => '2001:db8::1/128',
            mac_source  => '0A:1B:3C:4D:5E:6F',
            chain       => 'INPUT',
            provider    => 'ip6tables',
          }
        EOS
        it 'applies' do
          apply_manifest(pp55, catch_failures: true)
        end

        it 'contains the rule' do
          shell('ip6tables-save') do |r|
            expect(r.stdout).to match(%r{-A INPUT -s 2001:db8::1\/(128|ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff) -p tcp -m mac --mac-source 0A:1B:3C:4D:5E:6F -m comment --comment "604 - test"})
          end
        end
      end
    end

    # ip6tables has limited `-m socket` support
    if default['platform'] !~ %r{el-5} && default['platform'] !~ %r{ubuntu-10\.04} && default['platform'] !~ %r{debian-6} && default['platform'] !~ %r{sles}
      describe 'socket' do
        context 'true' do
          pp56 = <<-EOS
              class { '::firewall': }
              firewall { '605 - test':
                ensure   => present,
                proto    => tcp,
                port     => '605',
                action   => accept,
                chain    => 'INPUT',
                socket   => true,
                provider => 'ip6tables',
              }
          EOS
          it 'applies' do
            apply_manifest(pp56, catch_failures: true)
          end

          it 'contains the rule' do
            shell('ip6tables-save') do |r|
              expect(r.stdout).to match(%r{-A INPUT -p tcp -m multiport --ports 605 -m socket -m comment --comment "605 - test" -j ACCEPT})
            end
          end
        end

        context 'false' do
          pp57 = <<-EOS
              class { '::firewall': }
              firewall { '606 - test':
                ensure   => present,
                proto    => tcp,
                port     => '606',
                action   => accept,
                chain    => 'INPUT',
                socket   => false,
                provider => 'ip6tables',
              }
          EOS
          it 'applies' do
            apply_manifest(pp57, catch_failures: true)
          end

          it 'contains the rule' do
            shell('ip6tables-save') do |r|
              expect(r.stdout).to match(%r{-A INPUT -p tcp -m multiport --ports 606 -m comment --comment "606 - test" -j ACCEPT})
            end
          end
        end
      end
    end

    describe 'ipsec_policy' do
      context 'ipsec' do
        pp58 = <<-EOS
            class { '::firewall': }
            firewall { '607 - test':
              ensure       => 'present',
              action       => 'reject',
              chain        => 'OUTPUT',
              destination  => '2001:db8::1/128',
              ipsec_dir    => 'out',
              ipsec_policy => 'ipsec',
              proto        => 'all',
              reject       => 'icmp6-adm-prohibited',
              table        => 'filter',
              provider     => 'ip6tables',
            }
        EOS
        it 'applies' do
          apply_manifest(pp58, catch_failures: true)
        end

        it 'contains the rule' do
          shell('ip6tables-save') do |r|
            expect(r.stdout).to match(%r{-A OUTPUT -d 2001:db8::1\/(128|ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff) -m policy --dir out --pol ipsec -m comment --comment "607 - test" -j REJECT --reject-with icmp6-adm-prohibited}) # rubocop:disable Metrics/LineLength : Cannot reduce line to required length
          end
        end
      end

      context 'none' do
        pp59 = <<-EOS
          class { '::firewall': }
          firewall { '608 - test':
            ensure       => 'present',
            action       => 'reject',
            chain        => 'OUTPUT',
            destination  => '2001:db8::1/128',
            ipsec_dir    => 'out',
            ipsec_policy => 'none',
            proto        => 'all',
            reject       => 'icmp6-adm-prohibited',
            table        => 'filter',
            provider     => 'ip6tables',
          }
        EOS
        it 'applies' do
          apply_manifest(pp59, catch_failures: true)
        end

        it 'contains the rule' do
          shell('ip6tables-save') do |r|
            expect(r.stdout).to match(%r{-A OUTPUT -d 2001:db8::1\/(128|ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff) -m policy --dir out --pol none -m comment --comment "608 - test" -j REJECT --reject-with icmp6-adm-prohibited}) # rubocop:disable Metrics/LineLength : Cannot reduce line to required length
          end
        end
      end
    end

    describe 'ipsec_dir' do
      context 'out' do
        pp60 = <<-EOS
          class { '::firewall': }
          firewall { '609 - test':
            ensure       => 'present',
            action       => 'reject',
            chain        => 'OUTPUT',
            destination  => '2001:db8::1/128',
            ipsec_dir    => 'out',
            ipsec_policy => 'ipsec',
            proto        => 'all',
            reject       => 'icmp6-adm-prohibited',
            table        => 'filter',
            provider     => 'ip6tables',
          }
        EOS
        it 'applies' do
          apply_manifest(pp60, catch_failures: true)
        end

        it 'contains the rule' do
          shell('ip6tables-save') do |r|
            expect(r.stdout).to match(%r{-A OUTPUT -d 2001:db8::1\/(128|ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff) -m policy --dir out --pol ipsec -m comment --comment "609 - test" -j REJECT --reject-with icmp6-adm-prohibited}) # rubocop:disable Metrics/LineLength : Cannot reduce line to required length
          end
        end
      end

      context 'in' do
        pp61 = <<-EOS
          class { '::firewall': }
          firewall { '610 - test':
            ensure       => 'present',
            action       => 'reject',
            chain        => 'INPUT',
            destination  => '2001:db8::1/128',
            ipsec_dir    => 'in',
            ipsec_policy => 'none',
            proto        => 'all',
            reject       => 'icmp6-adm-prohibited',
            table        => 'filter',
            provider     => 'ip6tables',
          }
        EOS
        it 'applies' do
          apply_manifest(pp61, catch_failures: true)
        end

        it 'contains the rule' do
          shell('ip6tables-save') do |r|
            expect(r.stdout).to match(%r{-A INPUT -d 2001:db8::1\/(128|ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff) -m policy --dir in --pol none -m comment --comment "610 - test" -j REJECT --reject-with icmp6-adm-prohibited}) # rubocop:disable Metrics/LineLength : Cannot reduce line to required length
          end
        end
      end
    end

    describe 'set_mark' do
      context '0x3e8/0xffffffff' do
        pp62 = <<-EOS
            class { '::firewall': }
            firewall { '611 - test':
              ensure => present,
              chain => 'OUTPUT',
              proto => tcp,
              port   => '611',
              jump => 'MARK',
              table => 'mangle',
              set_mark => '0x3e8/0xffffffff',
              provider => 'ip6tables',
            }
        EOS
        it 'applies' do
          apply_manifest(pp62, catch_failures: true)
        end

        it 'contains the rule' do
          shell('ip6tables-save -t mangle') do |r|
            expect(r.stdout).to match(%r{-A OUTPUT -p tcp -m multiport --ports 611 -m comment --comment "611 - test" -j MARK --set-xmark 0x3e8\/0xffffffff})
          end
        end
      end
    end

    # ip6tables only supports ipset, addrtype, and mask on a limited set of platforms
    if default['platform'] =~ %r{el-7} || default['platform'] =~ %r{debian-7} || default['platform'] =~ %r{ubuntu-14\.04}
      # ipset is really difficult to test, just testing on one platform
      if default['platform'] =~ %r{ubuntu-14\.04}
        describe 'ipset' do
          pp63 = <<-EOS
            exec { 'hackery pt 1':
              command => 'service iptables-persistent flush',
              path    => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
            }
            package { 'ipset':
              ensure  => present,
              require => Exec['hackery pt 1'],
            }
            exec { 'hackery pt 2':
              command => 'service iptables-persistent start',
              path    => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
              require => Package['ipset'],
            }
            class { '::firewall': }
            exec { 'create ipset blacklist':
              command => 'ipset create blacklist hash:ip,port family inet6 maxelem 1024 hashsize 65535 timeout 120',
              path    => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
              require => Package['ipset'],
            }
            -> exec { 'create ipset honeypot':
              command => 'ipset create honeypot hash:ip family inet6 maxelem 1024 hashsize 65535 timeout 120',
              path    => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
            }
            -> exec { 'add blacklist':
              command => 'ipset add blacklist 2001:db8::1,80',
              path    => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
            }
            -> exec { 'add honeypot':
              command => 'ipset add honeypot 2001:db8::5',
              path    => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
            }
            firewall { '612 - test':
              ensure   => present,
              chain    => 'INPUT',
              proto    => tcp,
              action   => drop,
              ipset    => ['blacklist src,dst', '! honeypot dst'],
              provider => 'ip6tables',
              require  => Exec['add honeypot'],
            }
          EOS
          it 'applies' do
            apply_manifest(pp63, catch_failures: true)
          end

          it 'contains the rule' do
            shell('ip6tables-save') do |r|
              expect(r.stdout).to match(%r{-A INPUT -p tcp -m set --match-set blacklist src,dst -m set ! --match-set honeypot dst -m comment --comment "612 - test" -j DROP})
            end
          end
        end
      end

      # mask isn't supported on deb7
      if default['platform'] !~ %r{debian-7}
        describe 'mask' do
          pp64 = <<-EOS
            class { '::firewall': }
            firewall { '613 - test':
              recent => 'update',
              rseconds => 60,
              rsource => true,
              rname => 'test',
              action => 'drop',
              chain => 'FORWARD',
              mask => 'ffff::',
              provider => 'ip6tables',
            }
          EOS
          it 'applies' do
            apply_manifest(pp64, catch_failures: true)
          end

          it 'contains the rule' do
            shell('ip6tables-save') do |r|
              expect(r.stdout).to match(%r{-A FORWARD -p tcp -m recent --update --seconds 60 --name test --mask ffff:: --rsource -m comment --comment "613 - test" -j DROP})
            end
          end
        end
      end

      %w[dst_type src_type].each do |type|
        describe type.to_s do
          context 'MULTICAST' do
            pp65 = <<-EOS
              class { '::firewall': }
              firewall { '603 - test':
                proto    => tcp,
                action   => accept,
                #{type}  => 'MULTICAST',
                provider => 'ip6tables',
              }
            EOS
            it 'applies' do
              apply_manifest(pp65, catch_failures: true)
              apply_manifest(pp65, catch_changes: do_catch_changes)
            end

            it 'contains the rule' do
              shell('ip6tables-save') do |r|
                expect(r.stdout).to match(%r{-A INPUT -p tcp -m addrtype\s.*\sMULTICAST -m comment --comment "603 - test" -j ACCEPT})
              end
            end
          end

          context '! MULTICAST' do
            pp66 = <<-EOS
              class { '::firewall': }
              firewall { '603 - test inversion':
                proto    => tcp,
                action   => accept,
                #{type}  => '! MULTICAST',
                provider => 'ip6tables',
              }
            EOS
            it 'applies' do
              apply_manifest(pp66, catch_failures: true)
              apply_manifest(pp66, catch_changes: do_catch_changes)
            end

            it 'contains the rule' do
              shell('ip6tables-save') do |r|
                expect(r.stdout).to match(%r{-A INPUT -p tcp -m addrtype( !\s.*\sMULTICAST|\s.*\s! MULTICAST) -m comment --comment "603 - test inversion" -j ACCEPT})
              end
            end
          end

          context 'BROKEN' do
            pp67 = <<-EOS
              class { '::firewall': }
              firewall { '603 - test':
                proto    => tcp,
                action   => accept,
                #{type}  => 'BROKEN',
                provider => 'ip6tables',
              }
            EOS
            it 'fails' do
              apply_manifest(pp67, expect_failures: true) do |r|
                expect(r.stderr).to match(%r{Invalid value "BROKEN".})
              end
            end

            it 'does not contain the rule' do
              shell('ip6tables-save') do |r|
                expect(r.stdout).not_to match(%r{-A INPUT -p tcp -m addrtype\s.*\sBROKEN -m comment --comment "603 - test" -j ACCEPT})
              end
            end
          end
        end
      end
    end

  end

  describe 'limit' do
    context '500/sec' do
      pp68 = <<-EOS
          class { '::firewall': }
          firewall { '572 - test':
            ensure => present,
            proto => tcp,
            port   => '572',
            action => accept,
            limit => '500/sec',
          }
      EOS
      it 'applies' do
        apply_manifest(pp68, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A INPUT -p tcp -m multiport --ports 572 -m limit --limit 500\/sec -m comment --comment "572 - test" -j ACCEPT})
        end
      end
    end
  end

  describe 'burst' do
    context '500' do
      pp69 = <<-EOS
          class { '::firewall': }
          firewall { '573 - test':
            ensure => present,
            proto => tcp,
            port   => '573',
            action => accept,
            limit => '500/sec',
            burst => '1500',
          }
      EOS
      it 'applies' do
        apply_manifest(pp69, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A INPUT -p tcp -m multiport --ports 573 -m limit --limit 500\/sec --limit-burst 1500 -m comment --comment "573 - test" -j ACCEPT})
        end
      end
    end

    context 'invalid' do
      pp70 = <<-EOS
          class { '::firewall': }
          firewall { '571 - test':
            ensure => present,
            proto => tcp,
            port   => '571',
            action => accept,
            limit => '500/sec',
            burst => '1500/sec',
          }
      EOS
      it 'applies' do
        apply_manifest(pp70, expect_failures: true) do |r|
          expect(r.stderr).to match(%r{Invalid value "1500\/sec".})
        end
      end

      it 'does not contain the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).not_to match(%r{-A INPUT -p tcp -m multiport --ports 573 -m comment --comment "573 - test" -m limit --limit 500\/sec --limit-burst 1500\/sec -j ACCEPT})
        end
      end
    end
  end

  describe 'uid' do
    context 'nobody' do
      pp71 = <<-EOS
          class { '::firewall': }
          firewall { '574 - test':
            ensure => present,
            proto => tcp,
            chain => 'OUTPUT',
            port   => '574',
            action => accept,
            uid => 'nobody',
          }
      EOS
      it 'applies' do
        apply_manifest(pp71, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A OUTPUT -p tcp -m owner --uid-owner (nobody|\d+) -m multiport --ports 574 -m comment --comment "574 - test" -j ACCEPT})
        end
      end
    end
  end

  describe 'gid' do
    context 'root' do
      pp72 = <<-EOS
          class { '::firewall': }
          firewall { '575 - test':
            ensure => present,
            proto => tcp,
            chain => 'OUTPUT',
            port   => '575',
            action => accept,
            gid => 'root',
          }
      EOS
      it 'applies' do
        apply_manifest(pp72, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A OUTPUT -p tcp -m owner --gid-owner (root|\d+) -m multiport --ports 575 -m comment --comment "575 - test" -j ACCEPT})
        end
      end
    end
  end

  # iptables version 1.3.5 does not support masks on MARK rules
  if default['platform'] !~ %r{el-5} && default['platform'] !~ %r{sles-10}
    describe 'set_mark' do
      context '0x3e8/0xffffffff' do
        pp73 = <<-EOS
            class { '::firewall': }
            firewall { '580 - test':
              ensure => present,
              chain => 'OUTPUT',
              proto => tcp,
              port   => '580',
              jump => 'MARK',
              table => 'mangle',
              set_mark => '0x3e8/0xffffffff',
            }
        EOS
        it 'applies' do
          apply_manifest(pp73, catch_failures: true)
        end

        it 'contains the rule' do
          shell('iptables-save -t mangle') do |r|
            expect(r.stdout).to match(%r{-A OUTPUT -p tcp -m multiport --ports 580 -m comment --comment "580 - test" -j MARK --set-xmark 0x3e8\/0xffffffff})
          end
        end
      end
    end
  end

  describe 'pkttype' do
    context 'multicast' do
      pp74 = <<-EOS
          class { '::firewall': }
          firewall { '581 - test':
            ensure => present,
            proto => tcp,
            port   => '581',
            action => accept,
            pkttype => 'multicast',
          }
      EOS
      it 'applies' do
        apply_manifest(pp74, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A INPUT -p tcp -m multiport --ports 581 -m pkttype --pkt-type multicast -m comment --comment "581 - test" -j ACCEPT})
        end
      end
    end

    context 'test' do
      pp75 = <<-EOS
          class { '::firewall': }
          firewall { '582 - test':
            ensure => present,
            proto => tcp,
            port   => '582',
            action => accept,
            pkttype => 'test',
          }
      EOS
      it 'applies' do
        apply_manifest(pp75, expect_failures: true) do |r|
          expect(r.stderr).to match(%r{Invalid value "test".})
        end
      end

      it 'does not contain the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).not_to match(%r{-A INPUT -p tcp -m multiport --ports 582 -m pkttype --pkt-type multicast -m comment --comment "582 - test" -j ACCEPT})
        end
      end
    end
  end

  describe 'isfragment' do
    context 'true' do
      pp76 = <<-EOS
          class { '::firewall': }
          firewall { '583 - test':
            ensure => present,
            proto => tcp,
            port   => '583',
            action => accept,
            isfragment => true,
          }
      EOS
      it 'applies' do
        apply_manifest(pp76, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A INPUT -p tcp -f -m multiport --ports 583 -m comment --comment "583 - test" -j ACCEPT})
        end
      end
    end

    context 'false' do
      pp77 = <<-EOS
          class { '::firewall': }
          firewall { '584 - test':
            ensure => present,
            proto => tcp,
            port   => '584',
            action => accept,
            isfragment => false,
          }
      EOS
      it 'applies' do
        apply_manifest(pp77, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A INPUT -p tcp -m multiport --ports 584 -m comment --comment "584 - test" -j ACCEPT})
        end
      end
    end
  end

  # RHEL5/SLES does not support -m socket
  describe 'socket', unless: (default['platform'] =~ %r{el-5} || fact('operatingsystem') == 'SLES') do
    context 'true' do
      pp78 = <<-EOS
          class { '::firewall': }
          firewall { '585 - test':
            ensure => present,
            proto => tcp,
            port   => '585',
            action => accept,
            chain  => 'PREROUTING',
            table  => 'nat',
            socket => true,
          }
      EOS
      it 'applies' do
        apply_manifest(pp78, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save -t nat') do |r|
          expect(r.stdout).to match(%r{-A PREROUTING -p tcp -m multiport --ports 585 -m socket -m comment --comment "585 - test" -j ACCEPT})
        end
      end
    end

    context 'false' do
      pp79 = <<-EOS
          class { '::firewall': }
          firewall { '586 - test':
            ensure => present,
            proto => tcp,
            port   => '586',
            action => accept,
            chain  => 'PREROUTING',
            table  => 'nat',
            socket => false,
          }
      EOS
      it 'applies' do
        apply_manifest(pp79, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save -t nat') do |r|
          expect(r.stdout).to match(%r{-A PREROUTING -p tcp -m multiport --ports 586 -m comment --comment "586 - test" -j ACCEPT})
        end
      end
    end
  end

  describe 'ipsec_policy' do
    context 'ipsec' do
      pp80 = <<-EOS
          class { '::firewall': }
          firewall { '593 - test':
            ensure       => 'present',
            action       => 'reject',
            chain        => 'OUTPUT',
            destination  => '20.0.0.0/8',
            ipsec_dir    => 'out',
            ipsec_policy => 'ipsec',
            proto        => 'all',
            reject       => 'icmp-net-unreachable',
            table        => 'filter',
          }
      EOS
      it 'applies' do
        apply_manifest(pp80, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A OUTPUT -d 20.0.0.0\/(8|255\.0\.0\.0) -m policy --dir out --pol ipsec -m comment --comment "593 - test" -j REJECT --reject-with icmp-net-unreachable})
        end
      end
    end

    context 'none' do
      pp81 = <<-EOS
          class { '::firewall': }
          firewall { '594 - test':
            ensure       => 'present',
            action       => 'reject',
            chain        => 'OUTPUT',
            destination  => '20.0.0.0/8',
            ipsec_dir    => 'out',
            ipsec_policy => 'none',
            proto        => 'all',
            reject       => 'icmp-net-unreachable',
            table        => 'filter',
          }
      EOS
      it 'applies' do
        apply_manifest(pp81, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A OUTPUT -d 20.0.0.0\/(8|255\.0\.0\.0) -m policy --dir out --pol none -m comment --comment "594 - test" -j REJECT --reject-with icmp-net-unreachable})
        end
      end
    end
  end

  describe 'ipsec_dir' do
    context 'out' do
      pp82 = <<-EOS
          class { '::firewall': }
          firewall { '595 - test':
            ensure       => 'present',
            action       => 'reject',
            chain        => 'OUTPUT',
            destination  => '20.0.0.0/8',
            ipsec_dir    => 'out',
            ipsec_policy => 'ipsec',
            proto        => 'all',
            reject       => 'icmp-net-unreachable',
            table        => 'filter',
          }
      EOS
      it 'applies' do
        apply_manifest(pp82, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A OUTPUT -d 20.0.0.0\/(8|255\.0\.0\.0) -m policy --dir out --pol ipsec -m comment --comment "595 - test" -j REJECT --reject-with icmp-net-unreachable})
        end
      end
    end

    context 'in' do
      pp83 = <<-EOS
          class { '::firewall': }
          firewall { '596 - test':
            ensure       => 'present',
            action       => 'reject',
            chain        => 'INPUT',
            destination  => '20.0.0.0/8',
            ipsec_dir    => 'in',
            ipsec_policy => 'none',
            proto        => 'all',
            reject       => 'icmp-net-unreachable',
            table        => 'filter',
          }
      EOS
      it 'applies' do
        apply_manifest(pp83, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A INPUT -d 20.0.0.0\/(8|255\.0\.0\.0) -m policy --dir in --pol none -m comment --comment "596 - test" -j REJECT --reject-with icmp-net-unreachable})
        end
      end
    end
  end

  describe 'recent' do
    context 'set' do
      pp84 = <<-EOS
          class { '::firewall': }
          firewall { '597 - test':
            ensure       => 'present',
            chain        => 'INPUT',
            destination  => '30.0.0.0/8',
            proto        => 'all',
            table        => 'filter',
            recent       => 'set',
            rdest        => true,
            rname        => 'list1',
          }
      EOS
      it 'applies' do
        apply_manifest(pp84, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          # Mask added as of Ubuntu 14.04.
          expect(r.stdout).to match(%r{-A INPUT -d 30.0.0.0\/(8|255\.0\.0\.0) -m recent --set --name list1 (--mask 255.255.255.255 )?--rdest -m comment --comment "597 - test"})
        end
      end
    end

    context 'rcheck' do
      pp85 = <<-EOS
          class { '::firewall': }
          firewall { '598 - test':
            ensure       => 'present',
            chain        => 'INPUT',
            destination  => '30.0.0.0/8',
            proto        => 'all',
            table        => 'filter',
            recent       => 'rcheck',
            rsource      => true,
            rname        => 'list1',
            rseconds     => 60,
            rhitcount    => 5,
            rttl         => true,
          }
      EOS
      it 'applies' do
        apply_manifest(pp85, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A INPUT -d 30.0.0.0\/(8|255\.0\.0\.0) -m recent --rcheck --seconds 60 --hitcount 5 --rttl --name list1 (--mask 255.255.255.255 )?--rsource -m comment --comment "598 - test"}) # rubocop:disable Metrics/LineLength : Cannot reduce line to required length
        end
      end
    end

    context 'update' do
      pp86 = <<-EOS
          class { '::firewall': }
          firewall { '599 - test':
            ensure       => 'present',
            chain        => 'INPUT',
            destination  => '30.0.0.0/8',
            proto        => 'all',
            table        => 'filter',
            recent       => 'update',
          }
      EOS
      it 'applies' do
        apply_manifest(pp86, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A INPUT -d 30.0.0.0\/(8|255\.0\.0\.0) -m recent --update --name DEFAULT (--mask 255.255.255.255 )?--rsource -m comment --comment "599 - test"})
        end
      end
    end

    context 'remove' do
      pp87 = <<-EOS
          class { '::firewall': }
          firewall { '600 - test':
            ensure       => 'present',
            chain        => 'INPUT',
            destination  => '30.0.0.0/8',
            proto        => 'all',
            table        => 'filter',
            recent       => 'remove',
          }
      EOS
      it 'applies' do
        apply_manifest(pp87, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A INPUT -d 30.0.0.0\/(8|255\.0\.0\.0) -m recent --remove --name DEFAULT (--mask 255.255.255.255 )?--rsource -m comment --comment "600 - test"})
        end
      end
    end
  end

  describe 'mac_source' do
    context '0A:1B:3C:4D:5E:6F' do
      pp88 = <<-EOS
          class { '::firewall': }
          firewall { '610 - test':
            ensure      => present,
            source      => '10.1.5.28/32',
            mac_source  => '0A:1B:3C:4D:5E:6F',
            chain       => 'INPUT',
          }
      EOS
      it 'applies' do
        apply_manifest(pp88, catch_failures: true)
      end
      # rubocop:disable RSpec/ExampleLength : Cannot reduce lines to required size
      it 'contains the rule' do # rubocop:disable RSpec/MultipleExpectations : Second expectation required
        shell('iptables-save') do |r|
          if (fact('osfamily') == 'RedHat' && fact('operatingsystemmajrelease') == '5') || (default['platform'] =~ %r{sles-10})
            expect(r.stdout).to match(%r{-A INPUT -s 10.1.5.28 -p tcp -m mac --mac-source 0A:1B:3C:4D:5E:6F -m comment --comment "610 - test"})
          else
            expect(r.stdout).to match(%r{-A INPUT -s 10.1.5.28\/(32|255\.255\.255\.255) -p tcp -m mac --mac-source 0A:1B:3C:4D:5E:6F -m comment --comment "610 - test"})
          end
        end
      end
      # rubocop:enable RSpec/ExampleLength : Cannot reduce lines to required size
    end
  end

  describe 'reset' do
    it 'deletes all rules' do
      shell('ip6tables --flush')
      shell('iptables --flush; iptables -t nat --flush; iptables -t mangle --flush')
    end
  end

  describe 'to' do
    context 'Destination netmap 192.168.1.1' do
      pp89 = <<-EOS
          class { '::firewall': }
          firewall { '569 - test':
            proto  => tcp,
            table  => 'nat',
            chain  => 'PREROUTING',
            jump   => 'NETMAP',
            source => '200.200.200.200',
            to => '192.168.1.1',
          }
      EOS
      it 'applies' do
        apply_manifest(pp89, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save -t nat') do |r|
          expect(r.stdout).to match(%r{-A PREROUTING -s 200.200.200.200(\/32)? -p tcp -m comment --comment "569 - test" -j NETMAP --to 192.168.1.1})
        end
      end
    end

    describe 'reset' do
      it 'deletes all rules' do
        shell('ip6tables --flush')
        shell('iptables --flush; iptables -t nat --flush; iptables -t mangle --flush')
      end
    end

    context 'Source netmap 192.168.1.1' do
      pp90 = <<-EOS
          class { '::firewall': }
          firewall { '569 - test':
            proto  => tcp,
            table  => 'nat',
            chain  => 'POSTROUTING',
            jump   => 'NETMAP',
            destination => '200.200.200.200',
            to => '192.168.1.1',
          }
      EOS
      it 'applies' do
        apply_manifest(pp90, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save -t nat') do |r|
          expect(r.stdout).to match(%r{-A POSTROUTING -d 200.200.200.200(\/32)? -p tcp -m comment --comment "569 - test" -j NETMAP --to 192.168.1.1})
        end
      end
    end
  end

  context 'log_prefix containing -A' do
    pp91 = <<-EOS
      class { '::firewall': }
      firewall { '700 - test':
        jump       => 'LOG',
        log_prefix => 'FW-A-INPUT: ',
      }
    EOS
    it 'adds the rule' do
      apply_manifest(pp91, catch_failures: true)
    end

    it 'contains the rule' do
      shell('iptables-save') do |r|
        expect(r.stdout).to match(%r{-A INPUT -p tcp -m comment --comment "700 - test" -j LOG --log-prefix "FW-A-INPUT: "})
      end
    end

    pp92 = <<-EOS
      class { '::firewall': }
      firewall { '700 - test':
        ensure     => absent,
        jump       => 'LOG',
        log_prefix => 'FW-A-INPUT: ',
      }
    EOS
    it 'removes the rule' do
      apply_manifest(pp92, catch_failures: true)
    end

    it 'does not contain the rule' do
      shell('iptables-save') do |r|
        expect(r.stdout).not_to match(%r{-A INPUT -p tcp -m comment --comment "700 - test" -j LOG --log-prefix "FW-A-INPUT: "})
      end
    end
  end

  context 'log_uid is true' do
    pp93 = <<-EOS
      class { '::firewall': }
      firewall { '700 - test log_uid':
        chain   => 'OUTPUT',
        jump    => 'LOG',
        log_uid => true,
      }
    EOS
    it 'adds the rule' do
      apply_manifest(pp93, catch_failures: true)
    end

    it 'contains the rule' do
      shell('iptables-save') do |r|
        expect(r.stdout).to match(%r{-A OUTPUT -p tcp -m comment --comment "700 - test log_uid" -j LOG --log-uid})
      end
    end

    pp94 = <<-EOS
      class  { '::firewall': }
      firewall { '700 - test log_uid':
        chain   => 'OUTPUT',
        jump    => 'LOG',
        log_uid => false,
        ensure  => absent,
      }
    EOS
    it 'removes the rule' do
      apply_manifest(pp94, catch_failures: true)
    end

    it 'does not contain the rule' do
      shell('iptables-save') do |r|
        expect(r.stdout).not_to match(%r{-A OUTPUT -p tcp -m comment --comment "700 - test log_uid" -j --log-uid})
      end
    end
  end

  context 'comment containing "-A "' do
    pp95 = <<-EOS
      class { '::firewall': }
      firewall { '700 - blah-A Test Rule':
        jump       => 'LOG',
        log_prefix => 'FW-A-INPUT: ',
      }
    EOS
    it 'adds the rule' do
      apply_manifest(pp95, catch_failures: true)
    end

    it 'contains the rule' do
      shell('iptables-save') do |r|
        expect(r.stdout).to match(%r{-A INPUT -p tcp -m comment --comment "700 - blah-A Test Rule" -j LOG --log-prefix "FW-A-INPUT: "})
      end
    end

    pp96 = <<-EOS
      class { '::firewall': }
      firewall { '700 - blah-A Test Rule':
        ensure     => absent,
        jump       => 'LOG',
        log_prefix => 'FW-A-INPUT: ',
      }
    EOS
    it 'removes the rule' do
      apply_manifest(pp96, catch_failures: true)
    end

    it 'does not contain the rule' do
      shell('iptables-save') do |r|
        expect(r.stdout).not_to match(%r{-A INPUT -p tcp -m comment --comment "700 - blah-A Test Rule" -j LOG --log-prefix "FW-A-INPUT: "})
      end
    end
  end
end
