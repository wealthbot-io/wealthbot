require 'spec_helper'

describe 'postgresql::server::initdb', :type => :class do
  let (:pre_condition) do
    "include postgresql::server"
  end
  describe 'on RedHat' do
    let :facts do
      {
        :osfamily => 'RedHat',
        :operatingsystem => 'CentOS',
        :operatingsystemrelease => '6.0',
        :concat_basedir => tmpfilename('server'),
        :kernel => 'Linux',
        :id => 'root',
        :path => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
        :selinux => true,
      }
    end
    it { is_expected.to contain_file('/var/lib/pgsql/data').with_ensure('directory') }
  end
  describe 'on Amazon' do
    let :facts do
      {
        :osfamily => 'RedHat',
        :operatingsystem => 'Amazon',
        :operatingsystemrelease => '1.0',
        :concat_basedir => tmpfilename('server'),
        :kernel => 'Linux',
        :id => 'root',
        :path => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
        :selinux => true,
      }
    end
    it { is_expected.to contain_file('/var/lib/pgsql92/data').with_ensure('directory') }
  end

  describe 'exec with module_workdir => /var/tmp' do
    let :facts do
      {
        :osfamily => 'RedHat',
        :operatingsystem => 'CentOS',
        :operatingsystemrelease => '6.0',
        :concat_basedir => tmpfilename('server'),
        :kernel => 'Linux',
        :id => 'root',
        :path => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
        :selinux => true,
      }
    end
    let (:pre_condition) do
      <<-EOS
        class { 'postgresql::globals':
          module_workdir => '/var/tmp',
        }->
        class { 'postgresql::server': }
      EOS
    end

    it 'should contain exec with specified working directory' do
      is_expected.to contain_exec('postgresql_initdb').with ({
        :cwd => '/var/tmp',
      })
    end
  end

  describe 'exec with module_workdir => undef' do
    let :facts do
      {
        :osfamily => 'RedHat',
        :operatingsystem => 'CentOS',
        :operatingsystemrelease => '6.0',
        :concat_basedir => tmpfilename('server'),
        :kernel => 'Linux',
        :id => 'root',
        :path => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
        :selinux => true,
      }
    end
    let (:pre_condition) do
      <<-EOS
        class { 'postgresql::globals':
        }->
        class { 'postgresql::server': }
      EOS
    end

    it 'should contain exec with default working directory' do
      is_expected.to contain_exec('postgresql_initdb').with ({
        :cwd => '/tmp',
      })
    end
  end


  describe 'postgresql_psql with module_workdir => /var/tmp' do
    let :facts do
      {
        :osfamily => 'RedHat',
        :operatingsystem => 'CentOS',
        :operatingsystemrelease => '6.0',
        :concat_basedir => tmpfilename('server'),
        :kernel => 'Linux',
        :id => 'root',
        :path => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
        :selinux => true,
      }
    end

    let (:pre_condition) do
      <<-EOS
        class { 'postgresql::globals':
          module_workdir => '/var/tmp',
          encoding       => 'test',
          needs_initdb   => false,
        }->
        class { 'postgresql::server': }
      EOS
    end
    it 'should contain postgresql_psql with specified working directory' do 
      is_expected.to contain_postgresql_psql('Set template1 encoding to test').with({
        :cwd => '/var/tmp',
      })
    end
  end
end

