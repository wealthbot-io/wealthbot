require 'spec_helper_acceptance'

describe 'firewall time' do
  before :all do
    iptables_flush_all_tables
    ip6tables_flush_all_tables
  end

  if default['platform'] =~ %r{ubuntu-1404} || default['platform'] =~ %r{debian-7} || default['platform'] =~ %r{debian-8} || default['platform'] =~ %r{el-7}
    describe 'time tests ipv4' do
      context 'set all time parameters' do
        pp1 = <<-EOS
            class { '::firewall': }
            firewall { '805 - test':
              proto              => tcp,
              dport              => '8080',
              action             => accept,
              chain              => 'OUTPUT',
              date_start         => '2016-01-19T04:17:07',
              date_stop          => '2038-01-19T04:17:07',
              time_start         => '6:00',
              time_stop          => '17:00:00',
              month_days         => '7',
              week_days          => 'Tue',
              kernel_timezone    => true,
            }
        EOS
        it 'applies' do
          apply_manifest(pp1, catch_failures: true)
          apply_manifest(pp1, catch_changes: do_catch_changes)
        end

        it 'contains the rule' do
          shell('iptables-save') do |r|
            expect(r.stdout).to match(%r{-A OUTPUT -p tcp -m multiport --dports 8080 -m time --timestart 06:00:00 --timestop 17:00:00 --monthdays 7 --weekdays Tue --datestart 2016-01-19T04:17:07 --datestop 2038-01-19T04:17:07 --kerneltz -m comment --comment "805 - test" -j ACCEPT}) # rubocop:disable Metrics/LineLength : Cannot reduce line length to the required size
          end
        end
      end
    end

    describe 'time tests ipv6' do
      context 'set all time parameters' do
        pp2 = <<-EOS
            class { '::firewall': }
            firewall { '805 - test':
              proto              => tcp,
              dport              => '8080',
              action             => accept,
              chain              => 'OUTPUT',
              date_start         => '2016-01-19T04:17:07',
              date_stop          => '2038-01-19T04:17:07',
              time_start         => '6:00',
              time_stop          => '17:00:00',
              month_days         => '7',
              week_days          => 'Tue',
              kernel_timezone    => true,
              provider           => 'ip6tables',
            }
        EOS
        it 'applies' do
          apply_manifest(pp2, catch_failures: true)
          apply_manifest(pp2, catch_changes: do_catch_changes)
        end

        it 'contains the rule' do
          shell('ip6tables-save') do |r|
            expect(r.stdout).to match(%r{-A OUTPUT -p tcp -m multiport --dports 8080 -m time --timestart 06:00:00 --timestop 17:00:00 --monthdays 7 --weekdays Tue --datestart 2016-01-19T04:17:07 --datestop 2038-01-19T04:17:07 --kerneltz -m comment --comment "805 - test" -j ACCEPT}) # rubocop:disable Metrics/LineLength : Cannot reduce line length to the required size
          end
        end
      end
    end
  end
end
