require 'spec_helper_acceptance'

describe 'connlimit property' do
  before :all do
    iptables_flush_all_tables
    ip6tables_flush_all_tables
  end

  if default['platform'] !~ %r{sles-10}
    describe 'connlimit_above' do
      context '10' do
        pp1 = <<-EOS
            class { '::firewall': }
            firewall { '500 - test':
              proto           => tcp,
              dport           => '2222',
              connlimit_above => '10',
              action          => reject,
            }
        EOS
        it 'applies' do
          apply_manifest(pp1, catch_failures: true)
          apply_manifest(pp1, catch_changes: do_catch_changes)
        end

        it 'contains the rule' do
          shell('iptables-save') do |r|
            # connlimit-saddr is added in Ubuntu 14.04.
            expect(r.stdout).to match(%r{-A INPUT -p tcp -m multiport --dports 2222 -m connlimit --connlimit-above 10 --connlimit-mask 32 (--connlimit-saddr )?-m comment --comment "500 - test" -j REJECT --reject-with icmp-port-unreachable}) # rubocop:disable Metrics/LineLength : Cannot reduce length to required size
          end
        end
      end
    end

    describe 'connlimit_mask' do
      context '24' do
        pp2 = <<-EOS
            class { '::firewall': }
            firewall { '501 - test':
              proto           => tcp,
              dport           => '2222',
              connlimit_above => '10',
              connlimit_mask  => '24',
              action          => reject,
            }
        EOS
        it 'applies' do
          apply_manifest(pp2, catch_failures: true)
          apply_manifest(pp2, catch_changes: do_catch_changes)
        end

        it 'contains the rule' do
          shell('iptables-save') do |r|
            # connlimit-saddr is added in Ubuntu 14.04.
            expect(r.stdout).to match(%r{-A INPUT -p tcp -m multiport --dports 2222 -m connlimit --connlimit-above 10 --connlimit-mask 24 (--connlimit-saddr )?-m comment --comment "501 - test" -j REJECT --reject-with icmp-port-unreachable}) # rubocop:disable Metrics/LineLength : Cannot reduce length to required size
          end
        end
      end
    end
  end
end
