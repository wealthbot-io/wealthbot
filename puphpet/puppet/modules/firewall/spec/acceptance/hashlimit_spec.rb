
require 'spec_helper_acceptance'

describe 'hashlimit property', if: fact('operatingsystemmajrelease') != '5' && (fact('operatingsystem') != 'Scientific' || fact('operatingsystem') != 'RedHat') do
  before :all do
    iptables_flush_all_tables
    ip6tables_flush_all_tables
  end

  describe 'hashlimit_tests' do
    context 'hashlimit_above' do
      pp1 = <<-EOS
          class { '::firewall': }
          firewall { '800 - hashlimit_above test':
            chain                       => 'INPUT',
            proto                       => 'tcp',
            hashlimit_name              => 'above',
            hashlimit_above             => '526/sec',
            hashlimit_htable_gcinterval => '10',
            hashlimit_mode              => 'srcip,dstip',
            action                      => accept,
          }
      EOS
      it 'applies' do
        apply_manifest(pp1, catch_failures: true)
        apply_manifest(pp1, catch_changes: do_catch_changes)
      end

      regex_array = [%r{-A INPUT}, %r{-p tcp}, %r{--hashlimit-above 526\/sec}, %r{--hashlimit-mode srcip,dstip},
                     %r{--hashlimit-name above}, %r{--hashlimit-htable-gcinterval 10}, %r{-j ACCEPT}]
      it 'contains the rule' do
        shell('iptables-save') do |r|
          regex_array.each do |regex|
            expect(r.stdout).to match(regex)
          end
        end
      end
    end

    context 'hashlimit_above_ip6' do
      pp2 = <<-EOS
          class { '::firewall': }
          firewall { '801 - hashlimit_above test ipv6':
            chain                       => 'INPUT',
            provider                    => 'ip6tables',
            proto                       => 'tcp',
            hashlimit_name              => 'above-ip6',
            hashlimit_above             => '526/sec',
            hashlimit_htable_gcinterval => '10',
            hashlimit_mode              => 'srcip,dstip',
            action                      => accept,
          }
      EOS
      it 'applies' do
        apply_manifest(pp2, catch_failures: true)
        apply_manifest(pp2, catch_changes: do_catch_changes)
      end

      regex_array = [%r{-A INPUT}, %r{-p tcp}, %r{--hashlimit-above 526\/sec}, %r{--hashlimit-mode srcip,dstip},
                     %r{--hashlimit-name above-ip6}, %r{--hashlimit-htable-gcinterval 10}, %r{-j ACCEPT}]
      it 'contains the rule' do
        shell('ip6tables-save') do |r|
          regex_array.each do |regex|
            expect(r.stdout).to match(regex)
          end
        end
      end
    end

    context 'hashlimit_upto' do
      pp3 = <<-EOS
          class { '::firewall': }
          firewall { '802 - hashlimit_upto test':
            chain                   => 'INPUT',
            hashlimit_name          => 'upto',
            hashlimit_upto          => '16/sec',
            hashlimit_burst         => '640',
            hashlimit_htable_size   => '1310000',
            hashlimit_htable_max    => '320000',
            hashlimit_htable_expire => '36000000',
            action                  => accept,
          }
      EOS
      it 'applies' do
        apply_manifest(pp3, catch_failures: true)
        apply_manifest(pp3, catch_changes: do_catch_changes)
      end

      it 'contains the rule' do
        shell('iptables-save') do |r|
          expect(r.stdout).to match(%r{-A INPUT -p tcp -m hashlimit --hashlimit-upto 16\/sec --hashlimit-burst 640 --hashlimit-name upto --hashlimit-htable-size 1310000 --hashlimit-htable-max 320000 --hashlimit-htable-expire 36000000 -m comment --comment "802 - hashlimit_upto test" -j ACCEPT}) # rubocop:disable Metrics/LineLength : Cannot reduce line to required length
        end
      end
    end

    context 'hashlimit_upto_ip6' do
      pp4 = <<-EOS
          class { '::firewall': }
          firewall { '803 - hashlimit_upto test ip6':
            chain                   => 'INPUT',
            provider                => 'ip6tables',
            hashlimit_name          => 'upto-ip6',
            hashlimit_upto          => '16/sec',
            hashlimit_burst         => '640',
            hashlimit_htable_size   => '1310000',
            hashlimit_htable_max    => '320000',
            hashlimit_htable_expire => '36000000',
            action                  => accept,
          }
      EOS
      it 'applies' do
        apply_manifest(pp4, catch_failures: true)
        apply_manifest(pp4, catch_changes: do_catch_changes)
      end

      it 'contains the rule' do
        shell('ip6tables-save') do |r|
          expect(r.stdout).to match(%r{-A INPUT -p tcp -m hashlimit --hashlimit-upto 16\/sec --hashlimit-burst 640 --hashlimit-name upto-ip6 --hashlimit-htable-size 1310000 --hashlimit-htable-max 320000 --hashlimit-htable-expire 36000000 -m comment --comment "803 - hashlimit_upto test ip6" -j ACCEPT}) # rubocop:disable Metrics/LineLength : Cannot reduce line to required length
        end
      end
    end
  end
end
