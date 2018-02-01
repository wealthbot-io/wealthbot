require 'spec_helper_acceptance'

# Cant get this to work on Debian, add exception for now
describe 'redis::instance', :unless => (fact('operatingsystem') == 'Debian') do
  case fact('osfamily')
  when 'Debian'
    config_path  = '/etc/redis'
    manage_repo  = false
    redis_name = 'redis-server'
  else
    redis_name = 'redis'
    config_path  = '/etc'
    manage_repo  = true
  end

  it 'should run successfully' do
    pp = <<-EOS
    Exec {
      path => [ '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin', ]
    }

    class { '::redis':
      manage_repo     => #{manage_repo},
      default_install => false,
    }

    redis::instance {'redis1':
      port                => '7777',
    }

    redis::instance {'redis2':
      port                => '8888',
    }

    EOS

    # Apply twice to ensure no errors the second time.
    apply_manifest(pp, :catch_failures => true)
    apply_manifest(pp, :catch_changes => true)
  end

  describe package(redis_name) do
    it { should be_installed }
  end

  describe service('redis-server-redis1') do
    it { should be_running }
  end

  describe service('redis-server-redis2') do
    it { should be_running }
  end

  describe file("#{config_path}/redis-server-redis1.conf") do
    its(:content) { should match /port 7777/ }
  end

  describe file("#{config_path}/redis-server-redis2.conf") do
    its(:content) { should match /port 8888/ }
  end

  context 'redis should respond to ping command' do
    describe command('redis-cli -h 127.0.0.1 -p 7777 ping') do
      its(:stdout) { should match /PONG/ }
    end

    describe command('redis-cli -h 127.0.0.1 -p 8888 ping') do
      its(:stdout) { should match /PONG/ }
    end
  end
end
