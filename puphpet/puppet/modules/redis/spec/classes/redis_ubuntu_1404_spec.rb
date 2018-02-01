require 'spec_helper'

describe 'redis' do
  context 'on Ubuntu 1404' do

    let(:facts) {
      ubuntu_1404_facts
    }

    context 'should set Ubuntu specific values' do

      context 'when $::redis_server_version fact is not present (older features not enabled)' do

        let(:facts) {
          ubuntu_1404_facts.merge({
            :redis_server_version => nil,
          })
        }

        it { should contain_file('/etc/redis/redis.conf.puppet').with('content' => /^hash-max-ziplist-entries/) }
        it { should contain_file('/etc/redis/redis.conf.puppet').without('content' => /^tcp-backlog/) }
        it { should contain_file('/etc/redis/redis.conf.puppet').without('content' => /^protected-mode/) }

      end

      context 'when $::redis_server_version fact is not present and package_ensure is a newer version(3.2.1) (older features enabled)' do

        let(:facts) {
          ubuntu_1404_facts.merge({
            :redis_server_version => nil,
          })
        }
        let (:params) { { :package_ensure => '3.2.1' } }

        it { should contain_file('/etc/redis/redis.conf.puppet').with('content' => /^hash-max-ziplist-entries/) }
        it { should contain_file('/etc/redis/redis.conf.puppet').with('content' => /^protected-mode/) }
        it { should contain_file('/etc/redis/redis.conf.puppet').with('content' => /^tcp-backlog/) }

      end

      context 'when $::redis_server_version fact is not present and package_ensure is a newer version(3:3.2.1) (older features enabled)' do

        let(:facts) {
          ubuntu_1404_facts.merge({
            :redis_server_version => nil,
          })
        }
        let (:params) { { :package_ensure => '3:3.2.1' } }

        it { should contain_file('/etc/redis/redis.conf.puppet').with('content' => /^hash-max-ziplist-entries/) }
        it { should contain_file('/etc/redis/redis.conf.puppet').with('content' => /^protected-mode/) }
        it { should contain_file('/etc/redis/redis.conf.puppet').with('content' => /^tcp-backlog/) }

      end

      context 'when $::redis_server_version fact is not present and package_ensure is a newer version(4:4.0-rc3) (older features enabled)' do

        let(:facts) {
          ubuntu_1404_facts.merge({
            :redis_server_version => nil,
          })
        }
        let (:params) { { :package_ensure => '4:4.0-rc3' } }

        it { should contain_file('/etc/redis/redis.conf.puppet').with('content' => /^hash-max-ziplist-entries/) }
        it { should contain_file('/etc/redis/redis.conf.puppet').without('content' => /^protected-mode/) }
        it { should contain_file('/etc/redis/redis.conf.puppet').with('content' => /^tcp-backlog/) }

      end
      context 'when $::redis_server_version fact is not present and package_ensure is a newer version(4.0-rc3) (older features enabled)' do

        let(:facts) {
          ubuntu_1404_facts.merge({
            :redis_server_version => nil,
          })
        }
        let (:params) { { :package_ensure => '4.0-rc3' } }

        it { should contain_file('/etc/redis/redis.conf.puppet').with('content' => /^hash-max-ziplist-entries/) }
        it { should contain_file('/etc/redis/redis.conf.puppet').without('content' => /^protected-mode/) }
        it { should contain_file('/etc/redis/redis.conf.puppet').with('content' => /^tcp-backlog/) }

      end

      context 'when $::redis_server_version fact is present but the older version (older features not enabled)' do

        let(:facts) {
          ubuntu_1404_facts.merge({
            :redis_server_version => '2.8.4',
          })
        }

        it { should contain_file('/etc/redis/redis.conf.puppet').with('content' => /^hash-max-ziplist-entries/) }
        it { should contain_file('/etc/redis/redis.conf.puppet').without('content' => /^tcp-backlog/) }
        it { should contain_file('/etc/redis/redis.conf.puppet').without('content' => /^protected-mode/) }

      end

      context 'when $::redis_server_version fact is present but a newer version (older features enabled)' do

        let(:facts) {
          ubuntu_1404_facts.merge({
            :redis_server_version => '3.2.1',
          })
        }

        it { should contain_file('/etc/redis/redis.conf.puppet').with('content' => /^hash-max-ziplist-entries/) }
        it { should contain_file('/etc/redis/redis.conf.puppet').with('content' => /^tcp-backlog/) }
        it { should contain_file('/etc/redis/redis.conf.puppet').with('content' => /^protected-mode/) }

      end
    end

  end

end
