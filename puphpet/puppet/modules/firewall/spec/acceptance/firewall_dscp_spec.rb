require 'spec_helper_acceptance'

describe 'firewall DSCP' do
  before :all do
    iptables_flush_all_tables
    ip6tables_flush_all_tables
  end

  describe 'dscp ipv4 tests' do
    context 'set_dscp 0x01' do
      pp1 = <<-EOS
          class { '::firewall': }
          firewall {
            '1000 - set_dscp':
              proto     => 'tcp',
              jump      => 'DSCP',
              set_dscp  => '0x01',
              port      => '997',
              chain     => 'OUTPUT',
              table     => 'mangle',
          }
      EOS
      it 'applies' do
        apply_manifest(pp1, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save -t mangle') do |r|
          expect(r.stdout).to match(%r{-A OUTPUT -p tcp -m multiport --ports 997 -m comment --comment "1000 - set_dscp" -j DSCP --set-dscp 0x01})
        end
      end
    end

    context 'set_dscp_class EF' do
      pp2 = <<-EOS
          class { '::firewall': }
          firewall {
            '1001 EF - set_dscp_class':
              proto          => 'tcp',
              jump           => 'DSCP',
              port           => '997',
              set_dscp_class => 'EF',
              chain          => 'OUTPUT',
              table          => 'mangle',
          }
      EOS
      it 'applies' do
        apply_manifest(pp2, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A OUTPUT -p tcp -m multiport --ports 997 -m comment --comment "1001 EF - set_dscp_class" -j DSCP --set-dscp 0x2e})
        end
      end
    end
  end

  if default['platform'] !~ %r{el-5} && default['platform'] !~ %r{sles-10}
    describe 'dscp ipv6 tests' do
      context 'set_dscp 0x01' do
        pp3 = <<-EOS
            class { '::firewall': }
            firewall {
              '1002 - set_dscp':
                proto     => 'tcp',
                jump      => 'DSCP',
                set_dscp  => '0x01',
                port      => '997',
                chain     => 'OUTPUT',
                table     => 'mangle',
                provider  => 'ip6tables',
            }
        EOS
        it 'applies' do
          apply_manifest(pp3, catch_failures: true)
        end

        it 'contains the rule' do
          shell('ip6tables-save -t mangle') do |r|
            expect(r.stdout).to match(%r{-A OUTPUT -p tcp -m multiport --ports 997 -m comment --comment "1002 - set_dscp" -j DSCP --set-dscp 0x01})
          end
        end
      end

      context 'set_dscp_class EF' do
        pp4 = <<-EOS
            class { '::firewall': }
            firewall {
              '1003 EF - set_dscp_class':
                proto          => 'tcp',
                jump           => 'DSCP',
                port           => '997',
                set_dscp_class => 'EF',
                chain          => 'OUTPUT',
                table          => 'mangle',
                provider       => 'ip6tables',
            }
        EOS
        it 'applies' do
          apply_manifest(pp4, catch_failures: true)
        end

        it 'contains the rule' do
          shell('ip6tables-save') do |r|
            expect(r.stdout).to match(%r{-A OUTPUT -p tcp -m multiport --ports 997 -m comment --comment "1003 EF - set_dscp_class" -j DSCP --set-dscp 0x2e})
          end
        end
      end
    end
  end
end
