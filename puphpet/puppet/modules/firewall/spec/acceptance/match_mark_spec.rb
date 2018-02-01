require 'spec_helper_acceptance'

describe 'firewall match marks' do
  before :all do
    iptables_flush_all_tables
    ip6tables_flush_all_tables
  end

  if default['platform'] !~ %r{el-5} && default['platform'] !~ %r{sles-10}
    describe 'match_mark' do
      context '0x1' do
        pp1 = <<-EOS
            class { '::firewall': }
            firewall { '503 match_mark - test':
              proto      => 'all',
              match_mark => '0x1',
              action     => reject,
            }
        EOS
        it 'applies' do
          apply_manifest(pp1, catch_failures: true)
        end

        it 'contains the rule' do
          shell('iptables-save') do |r|
            expect(r.stdout).to match(%r{-A INPUT -m mark --mark 0x1 -m comment --comment "503 match_mark - test" -j REJECT --reject-with icmp-port-unreachable})
          end
        end
      end
    end

    describe 'match_mark_ip6' do
      context '0x1' do
        pp2 = <<-EOS
            class { '::firewall': }
            firewall { '503 match_mark ip6tables - test':
              proto      => 'all',
              match_mark => '0x1',
              action     => reject,
              provider => 'ip6tables',
            }
        EOS
        it 'applies' do
          apply_manifest(pp2, catch_failures: true)
        end

        it 'contains the rule' do
          shell('ip6tables-save') do |r|
            expect(r.stdout).to match(%r{-A INPUT -m mark --mark 0x1 -m comment --comment "503 match_mark ip6tables - test" -j REJECT --reject-with icmp6-port-unreachable})
          end
        end
      end
    end
  end
end
