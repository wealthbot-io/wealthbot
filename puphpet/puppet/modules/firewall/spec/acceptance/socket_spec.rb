require 'spec_helper_acceptance'

# RHEL5 does not support -m socket
describe 'firewall socket property', unless: default['platform'] =~ %r{el-5} || fact('operatingsystem') == 'SLES' do
  before :all do
    iptables_flush_all_tables
    ip6tables_flush_all_tables
  end

  shared_examples 'is idempotent' do |value, line_match|
    pp1 = <<-EOS
          class { '::firewall': }
          firewall { '598 - test':
            ensure => present,
            proto  => 'tcp',
            chain  => 'PREROUTING',
            table  => 'raw',
            #{value}
          }
    EOS
    it "changes the value to #{value}" do
      apply_manifest(pp1, catch_failures: true)
      apply_manifest(pp1, catch_changes: true)

      shell('iptables-save -t raw') do |r|
        expect(r.stdout).to match(%r{#{line_match}})
      end
    end
  end

  shared_examples "doesn't change" do |value, line_match|
    pp2 = <<-EOS
          class { '::firewall': }
          firewall { '598 - test':
            ensure => present,
            proto  => 'tcp',
            chain  => 'PREROUTING',
            table  => 'raw',
            #{value}
          }
    EOS
    it "doesn't change the value to #{value}" do
      apply_manifest(pp2, catch_changes: true)

      shell('iptables-save -t raw') do |r|
        expect(r.stdout).to match(%r{#{line_match}})
      end
    end
  end

  describe 'adding a rule' do
    context 'when unset' do
      before :all do
        iptables_flush_all_tables
      end
      it_behaves_like 'is idempotent', '', %r{-A PREROUTING -p tcp -m comment --comment "598 - test"}
    end
    context 'when set to true' do
      before :all do
        iptables_flush_all_tables
      end
      it_behaves_like 'is idempotent', 'socket => true,', %r{-A PREROUTING -p tcp -m socket -m comment --comment "598 - test"}
    end
    context 'when set to false' do
      before :all do
        iptables_flush_all_tables
      end
      it_behaves_like 'is idempotent', 'socket => false,', %r{-A PREROUTING -p tcp -m comment --comment "598 - test"}
    end
  end

  describe 'editing a rule' do
    context 'when unset or false and current value is false' do
      before :each do
        iptables_flush_all_tables
        shell('iptables -t raw -A PREROUTING -p tcp -m comment --comment "598 - test"')
      end
      it_behaves_like "doesn't change", 'socket => false,', %r{-A PREROUTING -p tcp -m comment --comment "598 - test"}
    end
    context 'when unset or false and current value is true' do
      before :each do
        iptables_flush_all_tables
        shell('iptables -t raw -A PREROUTING -p tcp -m comment --comment "598 - test"')
      end
      it_behaves_like 'is idempotent', 'socket => true,', %r{-A PREROUTING -p tcp -m socket -m comment --comment "598 - test"}
    end
    context 'when set to true and current value is false' do
      before :each do
        iptables_flush_all_tables
        shell('iptables -t raw -A PREROUTING -p tcp -m socket -m comment --comment "598 - test"')
      end
      it_behaves_like 'is idempotent', 'socket => false,', %r{-A PREROUTING -p tcp -m comment --comment "598 - test"}
    end
    context 'when set to true and current value is true' do
      before :each do
        iptables_flush_all_tables
        shell('iptables -t raw -A PREROUTING -p tcp -m socket -m comment --comment "598 - test"')
      end
      it_behaves_like "doesn't change", 'socket => true,', %r{-A PREROUTING -p tcp -m socket -m comment --comment "598 - test"}
    end
  end
end
