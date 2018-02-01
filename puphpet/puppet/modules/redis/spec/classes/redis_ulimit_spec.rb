require 'spec_helper'

describe 'redis::ulimit' do
  # add these two lines in a single test block to enable puppet and hiera debug mode
  # Puppet::Util::Log.level = :debug
  # Puppet::Util::Log.newdestination(:console)

  context 'with managed_by_cluster_manager true' do
    let(:facts) {
      debian_facts
    }
    let :pre_condition do
      [
        'class { redis:
          managed_by_cluster_manager => true,
        }'
      ]
    end
    it { should compile.with_all_deps }
    it do
      is_expected.to contain_file("/etc/security/limits.d/redis.conf").with(
        {
          "ensure"  => "file",
          "owner"   => "root",
          "group"   => "root",
          "mode"    => "0644",
          "content" => "redis soft nofile 65536\nredis hard nofile 65536\n",
        }
      )
    end
  end

  context 'with managed_by_cluster_manager true but not managing service' do
    let(:facts) {
      debian_facts.merge({
        :service_provider => 'systemd',
      })
    }
    let :pre_condition do
      [
        'class { "redis":
          managed_by_cluster_manager => true,
          service_manage             => false,
          notify_service             => false,
        }'
      ]
    end
    it { should compile.with_all_deps }
    it do
      is_expected.to contain_file("/etc/security/limits.d/redis.conf").with(
        {
          "ensure"  => "file",
          "owner"   => "root",
          "group"   => "root",
          "mode"    => "0644",
          "content" => "redis soft nofile 65536\nredis hard nofile 65536\n",
        }
      )
    end
  end

  context 'on a systemd system' do
    let(:facts) {
      debian_facts.merge({
        :service_provider => 'systemd',
      })
    }
    let :pre_condition do
      [
        'class { redis:
          ulimit => "7777",
        }'
      ]
    end
    it { should compile.with_all_deps }
    it do
      is_expected.to contain_file("/etc/systemd/system/redis-server.service.d/limit.conf").with(
      {
        "ensure" => "file",
        "owner"  => "root",
        "group"  => "root",
        "mode"   => "0444",
      }
      )
    end

    it do
      is_expected.to contain_augeas("Systemd redis ulimit").with(
      {
        'incl'     => '/etc/systemd/system/redis-server.service.d/limits.conf',
        'lens'     => 'Systemd.lns',
        'context'  => '/etc/systemd/system/redis-server.service.d/limits.conf',
        'changes'  => [
          "defnode nofile Service/LimitNOFILE \"\"",
          "set $nofile/value \"7777\""
        ],
        'notify'   => [
          'Exec[systemd-reload-redis]',
          ],
        }
        )
    end
  end

  context 'on a non-systemd system' do
    context 'Ubuntu 1404 system' do
      let(:facts) {
        ubuntu_1404_facts.merge({
          :service_provider => 'debian',
        })
      }
      let :pre_condition do
        [
          'class { redis:
            ulimit => "7777",
          }'
        ]
      end
      it { should compile.with_all_deps }
      it do
        is_expected.not_to contain_file('/etc/systemd/system/redis-server.service.d/limit.conf')
      end

      it do
        is_expected.not_to contain_augeas('Systemd redis ulimit')
      end

      it do
        is_expected.to contain_augeas('redis ulimit').with('changes' => 'set ULIMIT 7777')
        is_expected.to contain_augeas('redis ulimit').with('context' => '/files/etc/default/redis-server')
      end
    end

    context 'CentOS 6 system' do
      let(:facts) {
        centos_6_facts.merge({
          :service_provider => 'redhat',
        })
      }
      let :pre_condition do
        [
          'class { redis:
            ulimit => "7777",
          }'
        ]
      end
      it { should compile.with_all_deps }
      it do
        is_expected.not_to contain_file('/etc/systemd/system/redis-server.service.d/limit.conf')
      end

      it do
        is_expected.not_to contain_augeas('Systemd redis ulimit')
      end

      it do
        is_expected.to contain_augeas('redis ulimit').with('changes' => 'set ULIMIT 7777')
        is_expected.to contain_augeas('redis ulimit').with('context' => '/files/etc/sysconfig/redis')
      end
    end
  end


end
