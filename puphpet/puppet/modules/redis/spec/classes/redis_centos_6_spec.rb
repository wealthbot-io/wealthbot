require 'spec_helper'

describe 'redis' do
  context 'on CentOS 6' do

    let(:facts) {
      centos_6_facts
    }

    context 'should set CentOS specific values' do

      context 'when $::redis_server_version fact is not present and package_ensure is a newer version(3.2.1) (older features enabled)' do

        let(:facts) {
          centos_6_facts.merge({
            :redis_server_version => nil,
            :puppetversion        => Puppet.version,
          })
        }
        let (:params) { { :package_ensure => '3.2.1' } }

        it { should contain_file('/etc/redis.conf.puppet').without('content' => /^hash-max-zipmap-entries/) }
        it { should contain_file('/etc/redis.conf.puppet').with('content' => /^hash-max-ziplist-entries/) }
        it { should contain_file('/etc/redis.conf.puppet').with('content' => /^protected-mode/) }
        it { should contain_file('/etc/redis.conf.puppet').with('content' => /^tcp-backlog/) }
      end

      context 'when $::redis_server_version fact is not present and package_ensure is a newer version(4.0-rc3) (older features enabled)' do

        let(:facts) {
          centos_6_facts.merge({
            :redis_server_version => nil,
            :puppetversion        => Puppet.version,
          })
        }
        let (:params) { { :package_ensure => '4.0-rc3' } }

        it { should contain_file('/etc/redis.conf.puppet').without('content' => /^hash-max-zipmap-entries/) }
        it { should contain_file('/etc/redis.conf.puppet').with('content' => /^hash-max-ziplist-entries/) }
        it { should contain_file('/etc/redis.conf.puppet').without('content' => /^protected-mode/) }
        it { should contain_file('/etc/redis.conf.puppet').with('content' => /^tcp-backlog/) }
      end

      context 'when $::redis_server_version fact is present but the older version (older features not enabled)' do

        let(:facts) {
          centos_6_facts.merge({
            :redis_server_version => '2.4.10',
            :puppetversion        => Puppet.version,
          })
        }

        it { should contain_file('/etc/redis.conf.puppet').with('content' => /^hash-max-zipmap-entries/) }
        it { should contain_file('/etc/redis.conf.puppet').without('content' => /^hash-max-ziplist-entries/) }
        it { should contain_file('/etc/redis.conf.puppet').without('content' => /^protected-mode/) }
        it { should contain_file('/etc/redis.conf.puppet').without('content' => /^tcp-backlog/) }

      end

      context 'when $::redis_server_version fact is present but a newer version (older features enabled)' do

        let(:facts) {
          centos_6_facts.merge({
            :redis_server_version => '3.2.1',
            :puppetversion        => Puppet.version,
          })
        }

        it { should contain_file('/etc/redis.conf.puppet').without('content' => /^hash-max-zipmap-entries/) }
        it { should contain_file('/etc/redis.conf.puppet').with('content' => /^hash-max-ziplist-entries/) }
        it { should contain_file('/etc/redis.conf.puppet').with('content' => /^protected-mode/) }
        it { should contain_file('/etc/redis.conf.puppet').with('content' => /^tcp-backlog/) }

      end
    end

  end

end
