require 'spec_helper_acceptance'

# CentOS 6 Redis package is too old for Sentinel (2.4.10, needs 2.8+)
describe 'redis::sentinel', :unless => (fact('osfamily') == 'RedHat' && (fact('operatingsystemmajrelease') == '6')) do
  case fact('osfamily')
  when 'Debian'
    redis_name = 'redis-server'
  else
    redis_name = 'redis'
  end

  it 'should run successfully' do
    pp = <<-EOS

    $master_name      = 'mymaster'
    $redis_master     = '127.0.0.1'
    $failover_timeout = '10000'

    class { 'redis':
      manage_repo => true,
    }
    ->
    class { 'redis::sentinel':
      master_name      => $master_name,
      redis_host       => $redis_master,
      failover_timeout => $failover_timeout,
    }
    EOS

    apply_manifest(pp, :catch_failures => true)
  end

  describe package(redis_name) do
    it { should be_installed }
  end

  describe service(redis_name) do
    it { should be_running }
  end

  describe service('redis-sentinel') do
    it { should be_running }
  end

  case fact('osfamily')
  when 'Debian'
    describe package('redis-sentinel') do
      it { should be_installed }
    end
  end

  context 'redis should respond to ping command' do
    describe command('redis-cli ping') do
      its(:stdout) { should match /PONG/ }
    end
  end

  context 'redis-sentinel should return correct sentinel master' do
    describe command('redis-cli -p 26379 SENTINEL masters') do
      its(:stdout) { should match /^mymaster/ }
    end
  end

end
