require 'spec_helper_acceptance'

describe 'puppet resource firewallchain command' do
  before :all do
    iptables_flush_all_tables
    ip6tables_flush_all_tables
  end

  describe 'ensure' do
    context 'present' do
      pp1 = <<-EOS
          firewallchain { 'MY_CHAIN:filter:IPv4':
            ensure  => present,
          }
      EOS
      it 'applies cleanly' do
        # Run it twice and test for idempotency
        apply_manifest(pp1, catch_failures: true)
        apply_manifest(pp1, catch_changes: do_catch_changes)
      end

      it 'finds the chain' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{MY_CHAIN})
        end
      end
    end

    context 'absent' do
      pp2 = <<-EOS
          firewallchain { 'MY_CHAIN:filter:IPv4':
            ensure  => absent,
          }
      EOS
      it 'applies cleanly' do
        # Run it twice and test for idempotency
        apply_manifest(pp2, catch_failures: true)
        apply_manifest(pp2, catch_changes: do_catch_changes)
      end

      it 'fails to find the chain' do
        shell('iptables-save') do |r|
          expect(r.stdout).not_to match(%r{MY_CHAIN})
        end
      end
    end
  end

  # XXX purge => false is not yet implemented
  # context 'adding a firewall rule to a chain:' do
  #    pp3 = <<-EOS
  #      firewallchain { 'MY_CHAIN:filter:IPv4':
  #        ensure  => present,
  #      }
  #      firewall { '100 my rule':
  #        chain   => 'MY_CHAIN',
  #        action  => 'accept',
  #        proto   => 'tcp',
  #        dport   => 5000,
  #      }
  #    EOS
  #  it 'applies cleanly' do
  #    # Run it twice and test for idempotency
  #    apply_manifest(pp3, :catch_failures => true)
  #    apply_manifest(pp3, :catch_changes => do_catch_changes)
  #  end
  # end

  # context 'not purge firewallchain chains:' do
  #    pp4 = <<-EOS
  #      firewallchain { 'MY_CHAIN:filter:IPv4':
  #        ensure  => present,
  #        purge   => false,
  #        before  => Resources['firewall'],
  #      }
  #      resources { 'firewall':
  #        purge => true,
  #      }
  #    EOS
  #  it 'does not purge the rule' do
  #    # Run it twice and test for idempotency
  #    apply_manifest(pp4, :catch_failures => true) do |r|
  #      expect(r.stdout).to_not match(/removed/)
  #      expect(r.stderr).to eq('')
  #    end
  #    apply_manifest(pp4, :catch_changes => do_catch_changes)
  #  end

  #    pp5 = <<-EOS
  #      firewall { '100 my rule':
  #        chain   => 'MY_CHAIN',
  #        action  => 'accept',
  #        proto   => 'tcp',
  #        dport   => 5000,
  #      }
  #    EOS
  #  it 'still has the rule' do
  #    # Run it twice and test for idempotency
  #    apply_manifest(pp5, :catch_changes => do_catch_changes)
  #  end
  # end

  describe 'policy' do
    after :all do
      shell('iptables -t filter -P FORWARD ACCEPT')
    end

    context 'DROP' do
      pp6 = <<-EOS
          firewallchain { 'FORWARD:filter:IPv4':
            policy  => 'drop',
          }
      EOS
      it 'applies cleanly' do
        # Run it twice and test for idempotency
        apply_manifest(pp6, catch_failures: true)
        apply_manifest(pp6, catch_changes: do_catch_changes)
      end

      it 'finds the chain' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{FORWARD DROP})
        end
      end
    end
  end
end
