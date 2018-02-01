require 'spec_helper_acceptance'

describe 'firewall MSS' do
  before :all do
    iptables_flush_all_tables
    ip6tables_flush_all_tables
  end

  describe 'mss ipv4 tests' do
    context '1360' do
      pp1 = <<-EOS
          class { '::firewall': }
          firewall {
            '502 - set_mss':
              proto     => 'tcp',
              tcp_flags => 'SYN,RST SYN',
              jump      => 'TCPMSS',
              set_mss   => '1360',
              mss       => '1361:1541',
              chain     => 'FORWARD',
              table     => 'mangle',
          }
      EOS
      it 'applies' do
        apply_manifest(pp1, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save -t mangle') do |r|
          expect(r.stdout).to match(%r{-A FORWARD -p tcp -m tcp --tcp-flags SYN,RST SYN -m tcpmss --mss 1361:1541 -m comment --comment "502 - set_mss" -j TCPMSS --set-mss 1360})
        end
      end
    end

    context 'clamp_mss_to_pmtu' do
      pp2 = <<-EOS
          class { '::firewall': }
          firewall {
            '503 - clamp_mss_to_pmtu':
              proto             => 'tcp',
              chain             => 'FORWARD',
              tcp_flags         => 'SYN,RST SYN',
              jump              => 'TCPMSS',
              clamp_mss_to_pmtu => true,
          }
      EOS
      it 'applies' do
        apply_manifest(pp2, catch_failures: true)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A FORWARD -p tcp -m tcp --tcp-flags SYN,RST SYN -m comment --comment "503 - clamp_mss_to_pmtu" -j TCPMSS --clamp-mss-to-pmtu})
        end
      end
    end
  end

  if default['platform'] !~ %r{el-5} && default['platform'] !~ %r{sles-10}
    describe 'mss ipv6 tests' do
      context '1360' do
        pp3 = <<-EOS
            class { '::firewall': }
            firewall {
              '502 - set_mss':
                proto     => 'tcp',
                tcp_flags => 'SYN,RST SYN',
                jump      => 'TCPMSS',
                set_mss   => '1360',
                mss       => '1361:1541',
                chain     => 'FORWARD',
                table     => 'mangle',
                provider  => 'ip6tables',
            }
        EOS
        it 'applies' do
          apply_manifest(pp3, catch_failures: true)
        end

        it 'contains the rule' do
          shell('ip6tables-save -t mangle') do |r|
            expect(r.stdout).to match(%r{-A FORWARD -p tcp -m tcp --tcp-flags SYN,RST SYN -m tcpmss --mss 1361:1541 -m comment --comment "502 - set_mss" -j TCPMSS --set-mss 1360})
          end
        end
      end

      context 'clamp_mss_to_pmtu' do
        pp4 = <<-EOS
            class { '::firewall': }
            firewall {
              '503 - clamp_mss_to_pmtu':
                proto             => 'tcp',
                chain             => 'FORWARD',
                tcp_flags         => 'SYN,RST SYN',
                jump              => 'TCPMSS',
                clamp_mss_to_pmtu => true,
                provider          => 'ip6tables',
            }
        EOS
        it 'applies' do
          apply_manifest(pp4, catch_failures: true)
        end

        it 'contains the rule' do
          shell('ip6tables-save') do |r|
            expect(r.stdout).to match(%r{-A FORWARD -p tcp -m tcp --tcp-flags SYN,RST SYN -m comment --comment "503 - clamp_mss_to_pmtu" -j TCPMSS --clamp-mss-to-pmtu})
          end
        end
      end
    end
  end
end
