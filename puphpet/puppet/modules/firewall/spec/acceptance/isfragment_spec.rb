require 'spec_helper_acceptance'

describe 'firewall isfragment property' do
  before :all do
    iptables_flush_all_tables
    ip6tables_flush_all_tables
  end

  shared_examples 'is idempotent' do |value, line_match|
    pp1 = <<-EOS
          class { '::firewall': }
          firewall { '597 - test':
            ensure => present,
            proto  => 'tcp',
            #{value}
          }
    EOS
    it "changes the value to #{value}" do
      apply_manifest(pp1, catch_failures: true)
      apply_manifest(pp1, catch_changes: do_catch_changes)

      shell('iptables-save') do |r|
        expect(r.stdout).to match(%r{#{line_match}})
      end
    end
  end

  shared_examples "doesn't change" do |value, line_match|
    pp2 = <<-EOS
          class { '::firewall': }
          firewall { '597 - test':
            ensure => present,
            proto  => 'tcp',
            #{value}
          }
    EOS
    it "doesn't change the value to #{value}" do
      apply_manifest(pp2, catch_changes: do_catch_changes)

      shell('iptables-save') do |r|
        expect(r.stdout).to match(%r{#{line_match}})
      end
    end
  end

  describe 'adding a rule' do
    context 'when unset' do
      before :all do
        iptables_flush_all_tables
      end
      it_behaves_like 'is idempotent', '', %r{-A INPUT -p tcp -m comment --comment "597 - test"}
    end
    context 'when set to true' do
      before :all do
        iptables_flush_all_tables
      end
      it_behaves_like 'is idempotent', 'isfragment => true,', %r{-A INPUT -p tcp -f -m comment --comment "597 - test"}
    end
    context 'when set to false' do
      before :all do
        iptables_flush_all_tables
      end
      it_behaves_like 'is idempotent', 'isfragment => false,', %r{-A INPUT -p tcp -m comment --comment "597 - test"}
    end
  end

  describe 'editing a rule and current value is false' do
    context 'when unset or false' do
      before :each do
        iptables_flush_all_tables
        shell('iptables -A INPUT -p tcp -m comment --comment "597 - test"')
      end
      it_behaves_like "doesn't change", 'isfragment => false,', %r{-A INPUT -p tcp -m comment --comment "597 - test"}
    end
    context 'when unset or false and current value is true' do
      before :each do
        iptables_flush_all_tables
        shell('iptables -A INPUT -p tcp -m comment --comment "597 - test"')
      end
      it_behaves_like 'is idempotent', 'isfragment => true,', %r{-A INPUT -p tcp -f -m comment --comment "597 - test"}
    end

    context 'when set to true and current value is false' do
      before :each do
        iptables_flush_all_tables
        shell('iptables -A INPUT -p tcp -f -m comment --comment "597 - test"')
      end
      it_behaves_like 'is idempotent', 'isfragment => false,', %r{-A INPUT -p tcp -m comment --comment "597 - test"}
    end
    context 'when set to trueand current value is true' do
      before :each do
        iptables_flush_all_tables
        shell('iptables -A INPUT -p tcp -f -m comment --comment "597 - test"')
      end
      it_behaves_like "doesn't change", 'isfragment => true,', %r{-A INPUT -p tcp -f -m comment --comment "597 - test"}
    end
  end
end
