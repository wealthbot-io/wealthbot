require 'spec_helper'

describe 'postgresql::server::config', :type => :class do
  let (:pre_condition) do
    "include postgresql::server"
  end

  describe 'on RedHat 7' do
    let :facts do
      {
        :osfamily => 'RedHat',
        :operatingsystem => 'CentOS',
        :operatingsystemrelease => '7.0',
        :concat_basedir => tmpfilename('server'),
        :kernel => 'Linux',
        :id => 'root',
        :path => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
        :selinux => true,
      }
    end
    it 'should have the correct systemd-override file' do
      is_expected.to contain_file('systemd-override').with ({
        :ensure => 'present',
        :path => '/etc/systemd/system/postgresql.service',
        :owner => 'root',
        :group => 'root',
      })
      is_expected.to contain_file('systemd-override') \
        .with_content(/.include \/usr\/lib\/systemd\/system\/postgresql.service/)
    end

    describe 'with manage_package_repo => true and a version' do
      let (:pre_condition) do
        <<-EOS
          class { 'postgresql::globals':
            manage_package_repo => true,
            version => '9.4',
          }->
          class { 'postgresql::server': }
        EOS
      end

      it 'should have the correct systemd-override file' do
        is_expected.to contain_file('systemd-override').with ({
          :ensure => 'present',
          :path => '/etc/systemd/system/postgresql-9.4.service',
          :owner => 'root',
          :group => 'root',
        })
        is_expected.to contain_file('systemd-override') \
          .with_content(/.include \/usr\/lib\/systemd\/system\/postgresql-9.4.service/)
      end
    end
  end

  describe 'on Fedora 21' do
    let :facts do
      {
        :osfamily => 'RedHat',
        :operatingsystem => 'Fedora',
        :operatingsystemrelease => '21',
        :concat_basedir => tmpfilename('server'),
        :kernel => 'Linux',
        :id => 'root',
        :path => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
        :selinux => true,
      }
    end
    it 'should have the correct systemd-override file' do
      is_expected.to contain_file('systemd-override').with ({
        :ensure => 'present',
        :path => '/etc/systemd/system/postgresql.service',
        :owner => 'root',
        :group => 'root',
      })
      is_expected.to contain_file('systemd-override') \
        .with_content(/.include \/lib\/systemd\/system\/postgresql.service/)
    end

    describe 'with manage_package_repo => true and a version' do
      let (:pre_condition) do
        <<-EOS
          class { 'postgresql::globals':
            manage_package_repo => true,
            version => '9.4',
          }->
          class { 'postgresql::server': }
        EOS
      end

      it 'should have the correct systemd-override file' do
        is_expected.to contain_file('systemd-override').with ({
          :ensure => 'present',
          :path => '/etc/systemd/system/postgresql-9.4.service',
          :owner => 'root',
          :group => 'root',
        })
        is_expected.to contain_file('systemd-override') \
          .with_content(/.include \/lib\/systemd\/system\/postgresql-9.4.service/)
      end
    end
  end

  describe 'on Gentoo' do
    let (:pre_condition) do
      <<-EOS
        class { 'postgresql::globals':
          version => '9.5',
        }->
        class { 'postgresql::server': }
      EOS
    end
    let :facts do
      {
        :osfamily => 'Gentoo',
        :operatingsystem => 'Gentoo',
        :operatingsystemrelease => 'unused',
        :concat_basedir => tmpfilename('server'),
        :kernel => 'Linux',
        :id => 'root',
        :path => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
        :selinux => false,
      }
    end
    it 'should have the correct systemd-override file' do
      is_expected.to contain_file('systemd-override').with ({
        :ensure => 'present',
        :path => '/etc/systemd/system/postgresql-9.5.service',
        :owner => 'root',
        :group => 'root',
      })
      is_expected.to contain_file('systemd-override') \
        .with_content(/.include \/usr\/lib64\/systemd\/system\/postgresql-9.5.service/)
    end
  end

  describe 'with managed pg_hba_conf and ipv4acls' do
    let (:pre_condition) do
      <<-EOS
        class { 'postgresql::globals':
          version => '9.5',
        }->
        class { 'postgresql::server':
          manage_pg_hba_conf => true,
          ipv4acls => [
            'hostnossl all all 0.0.0.0/0 reject',
            'hostssl all all 0.0.0.0/0 md5'
          ]
        }
      EOS
    end
    let :facts do
      {
        :osfamily => 'RedHat',
        :operatingsystem => 'CentOS',
        :operatingsystemrelease => '7.0',
        :concat_basedir => tmpfilename('server'),
        :kernel => 'Linux',
        :id => 'root',
        :path => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
        :selinux => true,
      }
    end
    it 'should have hba rule default' do
      is_expected.to contain_postgresql__server__pg_hba_rule('local access as postgres user')
    end
    it 'should have hba rule ipv4acls' do
      is_expected.to contain_postgresql__server__pg_hba_rule('postgresql class generated rule ipv4acls 0')
    end
  end
end
