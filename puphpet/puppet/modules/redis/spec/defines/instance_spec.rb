require 'spec_helper'

describe 'redis::instance', :type => :define do
  let :pre_condition do
    'class { "redis":
      default_install => false,
    }'
  end
  let :title do
    'app2'
  end
  describe 'os-dependent items' do
    context "on Ubuntu systems" do
      context '14.04' do
        let(:facts) {
          ubuntu_1404_facts
        }
        it { should contain_file('/etc/redis/redis-server-app2.conf.puppet').with('content' => /^bind 127.0.0.1/) }
        it { should contain_file('/etc/redis/redis-server-app2.conf.puppet').with('content' => /^logfile \/var\/log\/redis\/redis-server-app2.log/) }
        it { should contain_file('/etc/redis/redis-server-app2.conf.puppet').with('content' => /^dir \/var\/lib\/redis\/redis-server-app2/) }
        it { should contain_file('/etc/redis/redis-server-app2.conf.puppet').with('content' => /^unixsocket \/var\/run\/redis\/redis-server-app2.sock/) }
        it { should contain_file('/var/lib/redis/redis-server-app2') }
        it { should contain_service('redis-server-app2').with_ensure('running') }
        it { should contain_service('redis-server-app2').with_enable('true') }
        it { should contain_file('/etc/init.d/redis-server-app2').with_content(/DAEMON_ARGS=\/etc\/redis\/redis-server-app2.conf/) }
        it { should contain_file('/etc/init.d/redis-server-app2').with_content(/PIDFILE=\/var\/run\/redis\/redis-server-app2.pid/) }
      end
      context '16.04' do
        let(:facts) {
          ubuntu_1604_facts.merge({
            :service_provider => 'systemd',
          })
        }
        it { should contain_file('/etc/redis/redis-server-app2.conf.puppet').with('content' => /^bind 127.0.0.1/) }
        it { should contain_file('/etc/redis/redis-server-app2.conf.puppet').with('content' => /^logfile \/var\/log\/redis\/redis-server-app2.log/) }
        it { should contain_file('/etc/redis/redis-server-app2.conf.puppet').with('content' => /^dir \/var\/lib\/redis\/redis-server-app2/) }
        it { should contain_file('/etc/redis/redis-server-app2.conf.puppet').with('content' => /^unixsocket \/var\/run\/redis\/redis-server-app2.sock/) }
        it { should contain_file('/var/lib/redis/redis-server-app2') }
        it { should contain_service('redis-server-app2').with_ensure('running') }
        it { should contain_service('redis-server-app2').with_enable('true') }
        it { should contain_file('/etc/systemd/system/redis-server-app2.service').with_content(/ExecStart=\/usr\/bin\/redis-server \/etc\/redis\/redis-server-app2.conf/) }
      end
    end
    context "on CentOS systems" do
      context '6' do
        let(:facts) {
          centos_6_facts
        }
        it { should contain_file('/etc/redis-server-app2.conf.puppet').with('content' => /^bind 127.0.0.1/) }
        it { should contain_file('/etc/redis-server-app2.conf.puppet').with('content' => /^logfile \/var\/log\/redis\/redis-server-app2.log/) }
        it { should contain_file('/etc/redis-server-app2.conf.puppet').with('content' => /^dir \/var\/lib\/redis\/redis-server-app2/) }
        it { should contain_file('/etc/redis-server-app2.conf.puppet').with('content' => /^unixsocket \/var\/run\/redis\/redis-server-app2.sock/) }
        it { should contain_file('/var/lib/redis/redis-server-app2') }
        it { should contain_service('redis-server-app2').with_ensure('running') }
        it { should contain_service('redis-server-app2').with_enable('true') }
        it { should contain_file('/etc/init.d/redis-server-app2').with_content(/REDIS_CONFIG="\/etc\/redis-server-app2.conf"/) }
        it { should contain_file('/etc/init.d/redis-server-app2').with_content(/pidfile="\/var\/run\/redis\/redis-server-app2.pid"/) }
      end
      context '7' do
        let(:facts) {
          centos_7_facts.merge({
            :service_provider => 'systemd',
          })
        }
        it { should contain_file('/etc/redis-server-app2.conf.puppet').with('content' => /^bind 127.0.0.1/) }
        it { should contain_file('/etc/redis-server-app2.conf.puppet').with('content' => /^logfile \/var\/log\/redis\/redis-server-app2.log/) }
        it { should contain_file('/etc/redis-server-app2.conf.puppet').with('content' => /^dir \/var\/lib\/redis\/redis-server-app2/) }
        it { should contain_file('/etc/redis-server-app2.conf.puppet').with('content' => /^unixsocket \/var\/run\/redis\/redis-server-app2.sock/) }
        it { should contain_file('/var/lib/redis/redis-server-app2') }
        it { should contain_service('redis-server-app2').with_ensure('running') }
        it { should contain_service('redis-server-app2').with_enable('true') }
        it { should contain_file('/etc/systemd/system/redis-server-app2.service').with_content(/ExecStart=\/usr\/bin\/redis-server \/etc\/redis-server-app2.conf/) }
      end
    end
  end
end
