require 'spec_helper_acceptance'

describe 'firewall uid' do
  before :all do
    iptables_flush_all_tables
    ip6tables_flush_all_tables
  end

  describe 'uid tests' do
    context 'uid set to root' do
      pp1 = <<-EOS
          class { '::firewall': }
          firewall { '801 - test':
            chain => 'OUTPUT',
            action => accept,
            uid => 'root',
            proto => 'all',
          }
      EOS
      it 'applies' do
        apply_manifest(pp1, catch_failures: true)
        apply_manifest(pp1, catch_changes: do_catch_changes)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A OUTPUT -m owner --uid-owner (0|root) -m comment --comment "801 - test" -j ACCEPT})
        end
      end
    end

    context 'uid set to !root' do
      pp2 = <<-EOS
          class { '::firewall': }
          firewall { '802 - test':
            chain => 'OUTPUT',
            action => accept,
            uid => '!root',
            proto => 'all',
          }
      EOS
      it 'applies' do
        apply_manifest(pp2, catch_failures: true)
        apply_manifest(pp2, catch_changes: do_catch_changes)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A OUTPUT -m owner ! --uid-owner (0|root) -m comment --comment "802 - test" -j ACCEPT})
        end
      end
    end

    context 'uid set to 0' do
      pp3 = <<-EOS
          class { '::firewall': }
          firewall { '803 - test':
            chain => 'OUTPUT',
            action => accept,
            uid => '0',
            proto => 'all',
          }
      EOS
      it 'applies' do
        apply_manifest(pp3, catch_failures: true)
        apply_manifest(pp3, catch_changes: do_catch_changes)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A OUTPUT -m owner --uid-owner (0|root) -m comment --comment "803 - test" -j ACCEPT})
        end
      end
    end

    context 'uid set to !0' do
      pp4 = <<-EOS
          class { '::firewall': }
          firewall { '804 - test':
            chain => 'OUTPUT',
            action => accept,
            uid => '!0',
            proto => 'all',
          }
      EOS
      it 'applies' do
        apply_manifest(pp4, catch_failures: true)
        apply_manifest(pp4, catch_changes: do_catch_changes)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A OUTPUT -m owner ! --uid-owner (0|root) -m comment --comment "804 - test" -j ACCEPT})
        end
      end
    end
  end
end
