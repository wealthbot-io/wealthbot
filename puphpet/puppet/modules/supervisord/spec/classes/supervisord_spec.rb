require 'spec_helper'

describe 'supervisord' do

  concatdir = '/var/lib/puppet/concat'
  let(:facts) {{ :concat_basedir => concatdir }}

  it { should contain_class('supervisord') }
  it { should contain_class('supervisord::install') }
  it { should contain_class('supervisord::config') }
  it { should contain_class('supervisord::service') }
  it { should contain_class('supervisord::params') }
  it { should contain_class('supervisord::reload') }
  it { should contain_package('supervisor') }

  describe '#service_name' do
    context 'default' do
      it { should contain_service('supervisord') }
    end

    context 'specified' do
      let(:params) {{ :service_name => 'myservicename' }}
      it { should contain_service('myservicename') }
    end
  end

  describe '#install_pip' do
    context 'default' do
      it { should_not contain_class('supervisord::pip') }
    end

    context 'true' do
      let(:params) {{ :install_pip => true }}
      it { should contain_class('supervisord::pip') }
      it { should contain_exec('install_setuptools') }
      it { should contain_exec('install_pip') }
    end

    context 'true and RedHat' do
      let(:params) {{ :install_pip => true }}
      let(:facts) {{ :osfamily => 'RedHat', :concat_basedir => concatdir }}
      it { should contain_exec('pip_provider_name_fix') }
    end
    context 'true and package_install_options not specified' do
      let(:params) {{ :install_pip => true, :package_install_options => false  }}
      it { should contain_package('supervisor').with_install_options(false) }
    end
  end

  describe '#env_var' do
    context 'default' do
      it { should contain_class('supervisord').without_env_hash }
      it { should contain_class('supervisord').without_env_string }
    end
  end

  describe '#global_environment' do
    context 'default' do
      it { should contain_class('supervisord').without_env_string }
    end
    context 'is specified' do
      let(:params) {{ :global_environment => { 'key1' => 'value1', 'key2' => 'value2' } }}
      it { should contain_concat__fragment('supervisord_main')\
        .with_content(/environment=key1='value1',key2='value2'/) }
    end
  end

  describe '#install_init' do
    context 'false' do
      it { should_not contain_file('/etc/init.d/supervisord') }
    end

    context 'with custom init script' do
      let(:params) {{ :init_script => '/etc/init/supervisord', :init_script_template => 'supervisord/init/Debian/systemd.erb', :init_defaults => false, :install_init => true }}
      it { should contain_file('/etc/init/supervisord')}
    end

    describe 'on supported OS' do
      describe 'with Debian' do
        context 'Wheezy (7.x)' do
          let(:facts) {{ :osfamily => 'Debian', :operatingsystemmajrelease => '7', :concat_basedir => concatdir }}
          it { should contain_file('/etc/init.d/supervisord') }
          it { should contain_file('/etc/default/supervisor') }
        end
        context 'Jessie (8.x)' do
          let(:facts) {{ :osfamily => 'Debian', :operatingsystemmajrelease => '8', :concat_basedir => concatdir }}
          it { should contain_file('/etc/systemd/system/supervisord.service') }
          it { should_not contain_file('/etc/default/supervisor') }
        end
      end
      describe 'with RedHat' do
        context 'Centos 6' do
          let(:facts) {{ :osfamily => 'RedHat', :operatingsystemmajrelease => '6', :concat_basedir => concatdir }}
          it { should contain_file('/etc/init.d/supervisord') }
          it { should contain_file('/etc/sysconfig/supervisord') }
        end
        context 'Centos 7' do
          let(:facts) {{ :osfamily => 'RedHat', :operatingsystemmajrelease => '7', :concat_basedir => concatdir }}
          it { should contain_file('/etc/systemd/system/supervisord.service') }
          it { should_not contain_file('/etc/default/supervisor') }
        end
        context 'Amazon' do
          let(:facts) {{ :osfamily => 'RedHat', :operatingsystem => 'Amazon', :concat_basedir => concatdir }}
            it { should contain_file('/etc/init.d/supervisord') }
            it { should contain_file('/etc/sysconfig/supervisord') }
        end

      end
      context 'with Suse' do
        let(:facts) {{ :osfamily => 'Suse', :concat_basedir => concatdir }}
        it { should contain_file('/etc/init.d/supervisord') }
        it { should contain_file('/etc/sysconfig/supervisor') }
      end
      describe 'with Ubuntu' do
        context 'Trusty (14.04)' do
          let(:facts) {{ :osfamily => 'Debian', :operatingsystem => 'Ubuntu', :operatingsystemmajrelease => '14.04', :concat_basedir => concatdir }}
          it { should contain_file('/etc/init.d/supervisord') }
          it { should contain_file('/etc/default/supervisor') }
        end
        context 'Xenial (16.04)' do
          let(:facts) {{ :osfamily => 'Debian', :operatingsystem => 'Ubuntu', :operatingsystemmajrelease => '16.04', :concat_basedir => concatdir }}
          it { should contain_file('/etc/systemd/system/supervisord.service') }
          it { should_not contain_file('/etc/default/supervisor') }
        end
      end
    end
  end

  describe '#unix_socket' do
    context 'default' do
      it { should contain_concat__fragment('supervisord_unix')}
    end
    context 'false' do
      let(:params) {{ :unix_socket => false }}
      it { should_not contain_concat__fragment('supervisord_unix')}
    end
  end

  describe '#inet_server' do
    context 'default' do
      it { should_not contain_concat__fragment('supervisord_inet')}
    end
    context 'true' do
      let(:params) {{ :inet_server => true }}
      it { should contain_concat__fragment('supervisord_inet')}
    end
  end

  describe '#ctl_socket' do
    context 'default' do
      it { should contain_concat__fragment('supervisord_ctl')\
        .with_content(/serverurl=unix:\/\/\/var\/run\/supervisor.sock$/)}
    end
    context 'http' do
      let(:params) {{ :inet_server => true, :ctl_socket => 'inet'}}
      it { should contain_concat__fragment('supervisord_ctl')\
        .with_content(/serverurl=http:\/\/127.0.0.1:9001$/)}
    end
  end

  describe '#run_path' do
    context 'default' do
      it { should_not contain_file('/var/run') }
      it { should contain_concat__fragment('supervisord_main') \
        .with_content(/pidfile=\/var\/run\/supervisord.pid$/) }
    end
    context 'is specified' do
      let(:params) {{ :run_path => '/opt/supervisord/run' }}
      it { should contain_file('/opt/supervisord/run') }
      it { should contain_concat__fragment('supervisord_main') \
        .with_content(/pidfile=\/opt\/supervisord\/run\/supervisord.pid$/) }
    end
  end

  describe '#log_path' do
    context 'default' do
      it { should contain_file('/var/log/supervisor') }
      it { should contain_concat__fragment('supervisord_main') \
        .with_content(/logfile=\/var\/log\/supervisor\/supervisord.log$/) }
    end
    context 'is specified' do
      let(:params) {{ :log_path => '/opt/supervisord/logs' }}
      it { should contain_file('/opt/supervisord/logs')}
      it { should contain_concat__fragment('supervisord_main') \
        .with_content(/logfile=\/opt\/supervisord\/logs\/supervisord.log$/) }
    end
  end

  describe '#config_include' do
    context 'default' do
      it { should contain_file('/etc/supervisor.d') }
      it { should contain_concat__fragment('supervisord_main') \
        .with_content(/files=\/etc\/supervisor.d\/\*.conf$/) }
    end
    context 'is specified' do
      let(:params) {{ :config_include => '/opt/supervisord/conf.d' }}
      it { should contain_file('/opt/supervisord/conf.d') }
      it { should contain_concat__fragment('supervisord_main') \
        .with_content(/files=\/opt\/supervisord\/conf.d\/\*.conf$/) }
    end
  end

  describe '#config_dirs' do
    context 'is specified' do
      let(:params) {{ :config_dirs => ['/etc/supervisor.d/*.conf', '/opt/supervisor.d/*', '/usr/share/supervisor.d/*.config'] }}
      it { should contain_concat__fragment('supervisord_main') \
        .with_content(/files=\/etc\/supervisor.d\/\*.conf \/opt\/supervisor.d\/\* \/usr\/share\/supervisor.d\/\*.config$/) }
    end
  end

  describe '#config_file' do
    context 'default' do
      it { should contain_file('/etc/supervisord.conf') }
    end
    context 'is specified' do
      let(:params) {{ :config_file => '/opt/supervisord/supervisor.conf' }}
      it { should contain_file('/opt/supervisord/supervisor.conf') }
    end
  end

  describe '#nodaemon' do
    context 'default' do
      it { should contain_concat__fragment('supervisord_main') \
        .with_content(/nodaemon=false$/) }
    end
    context 'true' do
      let(:params) {{ :nodaemon => true }}
      it { should contain_concat__fragment('supervisord_main') \
        .with_content(/nodaemon=true$/) }
    end
    context 'invalid' do
      let(:params) {{ :nodaemon => 'invalid' }}
      it { expect { raise_error(Puppet::Error, /is not a boolean/) }}
    end
  end

  describe '#minfds' do
    context 'default' do
      it { should contain_concat__fragment('supervisord_main') \
        .with_content(/minfds=1024$/) }
    end
    context 'specified' do
      let(:params) {{ :minfds => 2048 }}
      it { should contain_concat__fragment('supervisord_main') \
        .with_content(/minfds=2048$/) }
    end
    context 'invalid' do
      let(:params) {{ :minfds => 'string' }}
      it { expect { raise_error(Puppet::Error, /invalid minfds/) }}
    end
  end

  describe '#minprocs' do
    context 'default' do
      it { should contain_concat__fragment('supervisord_main') \
        .with_content(/minprocs=200$/) }
    end
    context 'specified' do
      let(:params) {{ :minprocs => 300 }}
      it { should contain_concat__fragment('supervisord_main') \
        .with_content(/minprocs=300$/) }
    end
    context 'invalid' do
      let(:params) {{ :minfds => 'string' }}
      it { expect { raise_error(Puppet::Error, /invalid minprocs/) }}
    end
  end

  describe '#strip_ansi' do
    context 'default' do
      it { should_not contain_concat__fragment('supervisord_main') \
        .with_content(/strip_ansi$/) }
    end
    context 'true' do
      let(:params) {{ :strip_ansi => true }}
      it { should contain_concat__fragment('supervisord_main') \
        .with_content(/strip_ansi=true$/) }
    end
    context 'invalid' do
      let(:params) {{ :strip_ansi => 'string' }}
      it { expect { raise_error(Puppet::Error, /is not a boolean/) }}
    end
  end

  describe '#user' do
    context 'default' do
      it { should_not contain_concat__fragment('supervisord_main') \
        .with_content(/user$/) }
    end
    context 'specified' do
      let(:params) {{ :user => 'myuser' }}
      it { should contain_concat__fragment('supervisord_main') \
        .with_content(/user=myuser$/) }
    end
  end

  describe '#identifier' do
    context 'default' do
      it { should_not contain_concat__fragment('supervisord_main') \
        .with_content(/identifier$/) }
    end
    context 'specified' do
      let(:params) {{ :identifier => 'myidentifier' }}
      it { should contain_concat__fragment('supervisord_main') \
        .with_content(/identifier=myidentifier$/) }
    end
  end

  describe '#directory' do
    context 'default' do
      it { should_not contain_concat__fragment('supervisord_main') \
        .with_content(/directory$/) }
    end
    context 'specified' do
      let(:params) {{ :directory => '/opt/supervisord' }}
      it { should contain_concat__fragment('supervisord_main') \
        .with_content(/directory=\/opt\/supervisord$/) }
    end
  end

  describe '#nocleanup' do
    context 'default' do
      it { should_not contain_concat__fragment('supervisord_main') \
        .with_content(/nocleanup$/) }
    end
    context 'true' do
      let(:params) {{ :nocleanup => true }}
      it { should contain_concat__fragment('supervisord_main') \
        .with_content(/nocleanup=true$/) }
    end
    context 'invalid' do
      let(:params) {{ :nocleanup => 'string' }}
      it { expect { raise_error(Puppet::Error, /is not a boolean/) }}
    end
  end

  describe '#childlogdir' do
    context 'default' do
      it { should_not contain_concat__fragment('supervisord_main') \
        .with_content(/childlogdir$/) }
    end
    context 'specified' do
      let(:params) {{ :childlogdir => '/opt/supervisord/logdir' }}
      it { should contain_concat__fragment('supervisord_main') \
        .with_content(/childlogdir=\/opt\/supervisord\/logdir$/) }
    end
    context 'invalid' do
      let(:params) {{ :childlogdir => 'not_a_path' }}
      it { expect { raise_error(Puppet::Error, /is not an absolute path/) }}
    end
  end
end
