require 'spec_helper_acceptance'

if default['platform'] =~ %r{el-5} || default['platform'] =~ %r{sles-10}
  describe "firewall ip6tables doesn't work on 1.3.5 because --comment is missing" do
    before :all do
      iptables_flush_all_tables
      ip6tables_flush_all_tables
    end

    pp1 = <<-EOS
        class { '::firewall': }
        firewall { '599 - test':
          ensure   => present,
          proto    => 'tcp',
          provider => 'ip6tables',
        }
    EOS
    it "can't use ip6tables" do
      expect(apply_manifest(pp1, expect_failures: true).stderr).to match(%r{ip6tables provider is not supported})
    end
  end
else
  describe 'firewall ishasmorefrags/islastfrag/isfirstfrag properties' do
    before :all do
      iptables_flush_all_tables
      ip6tables_flush_all_tables
    end

    shared_examples 'is idempotent' do |values, line_match|
      pp2 = <<-EOS
            class { '::firewall': }
            firewall { '599 - test':
              ensure   => present,
              proto    => 'tcp',
              provider => 'ip6tables',
              #{values}
            }
      EOS
      it "changes the values to #{values}" do
        apply_manifest(pp2, catch_failures: true)
        apply_manifest(pp2, catch_changes: do_catch_changes)

        shell('ip6tables-save') do |r|
          expect(r.stdout).to match(%r{#{line_match}})
        end
      end
    end
    shared_examples "doesn't change" do |values, line_match|
      pp3 = <<-EOS
            class { '::firewall': }
            firewall { '599 - test':
              ensure   => present,
              proto    => 'tcp',
              provider => 'ip6tables',
              #{values}
            }
      EOS
      it "doesn't change the values to #{values}" do
        apply_manifest(pp3, catch_changes: do_catch_changes)

        shell('ip6tables-save') do |r|
          expect(r.stdout).to match(%r{#{line_match}})
        end
      end
    end

    describe 'adding a rule' do
      context 'when unset' do
        before :all do
          ip6tables_flush_all_tables
        end
        it_behaves_like 'is idempotent', '', %r{-A INPUT -p tcp -m comment --comment "599 - test"}
      end
      context 'when set to true' do
        before :all do
          ip6tables_flush_all_tables
        end
        it_behaves_like 'is idempotent', 'ishasmorefrags => true, islastfrag => true, isfirstfrag => true',
                        %r{-A INPUT -p tcp -m frag --fragid 0 --fragmore -m frag --fragid 0 --fraglast -m frag --fragid 0 --fragfirst -m comment --comment "599 - test"}
      end
      context 'when set to false' do
        before :all do
          ip6tables_flush_all_tables
        end
        it_behaves_like 'is idempotent', 'ishasmorefrags => false, islastfrag => false, isfirstfrag => false', %r{-A INPUT -p tcp -m comment --comment "599 - test"}
      end
    end
    describe 'editing a rule' do
      context 'when unset or false' do
        before :each do
          ip6tables_flush_all_tables
          shell('ip6tables -A INPUT -p tcp -m comment --comment "599 - test"')
        end
        context 'and current value is false' do
          it_behaves_like "doesn't change", 'ishasmorefrags => false, islastfrag => false, isfirstfrag => false', %r{-A INPUT -p tcp -m comment --comment "599 - test"}
        end
        context 'and current value is true' do
          it_behaves_like 'is idempotent', 'ishasmorefrags => true, islastfrag => true, isfirstfrag => true',
                          %r{-A INPUT -p tcp -m frag --fragid 0 --fragmore -m frag --fragid 0 --fraglast -m frag --fragid 0 --fragfirst -m comment --comment "599 - test"}
        end
      end
      context 'when set to true' do
        before :each do
          ip6tables_flush_all_tables
          shell('ip6tables -A INPUT -p tcp -m frag --fragid 0 --fragmore -m frag --fragid 0 --fraglast -m frag --fragid 0 --fragfirst -m comment --comment "599 - test"')
        end
        context 'and current value is false' do
          it_behaves_like 'is idempotent', 'ishasmorefrags => false, islastfrag => false, isfirstfrag => false', %r{-A INPUT -p tcp -m comment --comment "599 - test"}
        end
        context 'and current value is true' do
          it_behaves_like "doesn't change", 'ishasmorefrags => true, islastfrag => true, isfirstfrag => true',
                          %r{-A INPUT -p tcp -m frag --fragid 0 --fragmore -m frag --fragid 0 --fraglast -m frag --fragid 0 --fragfirst -m comment --comment "599 - test"}
        end
      end
    end
  end
end
