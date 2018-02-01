require 'spec_helper_acceptance'

describe 'firewall inverting' do
  before :all do
    iptables_flush_all_tables
    ip6tables_flush_all_tables
  end

  context 'inverting rules' do
    pp1 = <<-EOS
        class { '::firewall': }
        firewall { '601 disallow esp protocol':
          action => 'accept',
          proto  => '! esp',
        }
        firewall { '602 drop NEW external website packets with FIN/RST/ACK set and SYN unset':
          chain     => 'INPUT',
          state     => 'NEW',
          action    => 'drop',
          proto     => 'tcp',
          sport     => ['! http', '! 443'],
          source    => '! 10.0.0.0/8',
          tcp_flags => '! FIN,SYN,RST,ACK SYN',
        }
    EOS
    it 'applies' do
      apply_manifest(pp1, catch_failures: true)
      apply_manifest(pp1, catch_changes: do_catch_changes)
    end

    regex_array = [%r{-A INPUT (-s !|! -s) (10\.0\.0\.0\/8|10\.0\.0\.0\/255\.0\.0\.0).*}, %r{-A INPUT.*(--sports !|! --sports) 80,443.*},
                   %r{-A INPUT.*-m tcp ! --tcp-flags FIN,SYN,RST,ACK SYN.*}, %r{-A INPUT.*-j DROP},
                   %r{-A INPUT (! -p|-p !) esp -m comment --comment "601 disallow esp protocol" -j ACCEPT}]
    it 'contains the rules' do
      shell('iptables-save') do |r|
        regex_array.each do |regex|
          expect(r.stdout).to match(regex)
        end
      end
    end
  end
  context 'inverting partial array rules' do
    pp2 = <<-EOS
        class { '::firewall': }
        firewall { '603 drop 80,443 traffic':
          chain     => 'INPUT',
          action    => 'drop',
          proto     => 'tcp',
          sport     => ['! http', '443'],
        }
    EOS
    it 'raises a failure' do
      apply_manifest(pp2, expect_failures: true) do |r|
        expect(r.stderr).to match(%r{is not prefixed})
      end
    end
  end
end
