require 'spec_helper_acceptance'

describe 'connmark property' do
  describe 'connmark' do
    context '50' do
      pp = <<-EOS
          class { '::firewall': }
          firewall { '502 - test':
            proto    => 'all',
            connmark => '0x1',
            action   => reject,
          }
      EOS
      it 'applies' do
        apply_manifest(pp, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A INPUT -m connmark --mark 0x1 -m comment --comment "502 - test" -j REJECT --reject-with icmp-port-unreachable})
        end
      end
    end
  end
end
