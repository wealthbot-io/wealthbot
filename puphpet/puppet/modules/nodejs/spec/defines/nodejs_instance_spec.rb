require 'spec_helper'

describe 'nodejs::instance', :type => :define do
  let(:title) { 'nodejs::instance' }
  let(:facts) {{
    :kernel         => 'linux',
    :hardwaremodel  => 'x86',
    :osfamily       => 'Debian',
    :processorcount => 2,
  }}

  describe 'with latest lts release' do
    let(:params) {{
      :ensure               => 'present',
      :version              => 'v4.4.7',
      :target_dir           => '/usr/local/bin',
      :make_install         => true,
      :cpu_cores            => 2,
      :default_node_version => nil,
      :timeout              => 0,
    }}

    it { should contain_nodejs__instance__download('nodejs-download-v4.4.7') \
      .with_source('https://nodejs.org/dist/v4.4.7/node-v4.4.7.tar.gz') \
      .with_destination('/usr/local/node/node-v4.4.7.tar.gz')
    }

    it { should contain_file('nodejs-check-tar-v4.4.7') \
      .with_ensure('file') \
      .with_path('/usr/local/node/node-v4.4.7.tar.gz')
    }

    it { should contain_exec('nodejs-unpack-v4.4.7') \
      .with_command('tar -xzvf node-v4.4.7.tar.gz -C /usr/local/node/node-v4.4.7 --strip-components=1') \
      .with_cwd('/usr/local/node') \
      .with_unless('test -f /usr/local/node/node-v4.4.7/bin/node')
    }
  end

  describe 'default parameters with cpu_cores set manually to 1' do

    let(:params) {{
      :make_install         => true,
      :ensure               => 'present',
      :target_dir           => '/usr/local/bin',
      :version              => 'v6.2.0',
      :cpu_cores            => 1,
      :default_node_version => nil,
      :timeout              => 0,
    }}

    it { should contain_exec('nodejs-make-install-v6.2.0') \
      .with_command('./configure --prefix=/usr/local/node/node-v6.2.0 && make -j 1 && make -j 1 install') \
      .with_cwd('/usr/local/node/node-v6.2.0') \
      .with_unless('test -f /usr/local/node/node-v6.2.0/bin/node')
    }
  end

  describe 'with specific version v6.0.0' do

    let(:params) {{
      :version              => 'v6.0.0',
      :ensure               => 'present',
      :target_dir           => '/usr/local/bin',
      :make_install         => true,
      :cpu_cores            => 2,
      :default_node_version => nil,
      :timeout              => 0,
    }}

    it { should contain_file('nodejs-install-dir') \
      .with_ensure('directory')
    }

    it { should contain_nodejs__instance__download('nodejs-download-v6.0.0') \
      .with_source('https://nodejs.org/dist/v6.0.0/node-v6.0.0.tar.gz') \
      .with_destination('/usr/local/node/node-v6.0.0.tar.gz') \
      .with_timeout(0)
    }

    it { should contain_file('nodejs-check-tar-v6.0.0') \
      .with_ensure('file') \
      .with_path('/usr/local/node/node-v6.0.0.tar.gz')
    }

    it { should contain_exec('nodejs-unpack-v6.0.0') \
      .with_command('tar -xzvf node-v6.0.0.tar.gz -C /usr/local/node/node-v6.0.0 --strip-components=1') \
      .with_cwd('/usr/local/node') \
      .with_unless('test -f /usr/local/node/node-v6.0.0/bin/node')
    }

    it { should contain_file('/usr/local/node/node-v6.0.0') \
      .with_ensure('directory')
    }

    it { should contain_exec('nodejs-make-install-v6.0.0') \
      .with_command('./configure --prefix=/usr/local/node/node-v6.0.0 && make -j 2 && make -j 2 install') \
      .with_cwd('/usr/local/node/node-v6.0.0') \
      .with_unless('test -f /usr/local/node/node-v6.0.0/bin/node')
    }

    it { should contain_file('nodejs-symlink-bin-with-version-v6.0.0') \
      .with_ensure('link') \
      .with_path('/usr/local/bin/node-v6.0.0') \
      .with_target('/usr/local/node/node-v6.0.0/bin/node')
    }

    it { should contain_file('npm-symlink-bin-with-version-v6.0.0') \
      .with_ensure('file') \
      .with_mode('0755') \
      .with_path('/usr/local/bin/npm-v6.0.0') \
      .with_content(/(.*)\/usr\/local\/bin\/node-v6.0.0 \/usr\/local\/node\/node-v6.0.0\/bin\/npm "\$@"/)
    }

    it { should contain_file('nodejs-npmrc-etc-dir-v6.0.0') \
      .with_ensure('directory') \
      .with_path('/usr/local/node/node-v6.0.0/etc')
    }

    it { should contain_file('nodejs-npmrc-v6.0.0') \
      .with_path('/usr/local/node/node-v6.0.0/etc/npmrc')
    }

    it { should_not contain_file('/usr/local/bin/node') }
    it { should_not contain_file('/usr/local/bin/npm') }

    it { should_not contain_nodejs__instance__download('npm-download-v6.0.0') }
    it { should_not contain_exec('npm-install-v6.0.0') }
  end

  describe 'specific version v6.0.0 and cpu_cores manually set to 1' do

    let(:params) {{
      :ensure               => 'present',
      :target_dir           => '/usr/local/bin',
      :make_install         => true,
      :version              => 'v6.0.0',
      :cpu_cores            => 1,
      :default_node_version => nil,
      :timeout              => 0,
    }}

    it { should contain_exec('nodejs-make-install-v6.0.0') \
      .with_command('./configure --prefix=/usr/local/node/node-v6.0.0 && make -j 1 && make -j 1 install') \
      .with_cwd('/usr/local/node/node-v6.0.0') \
      .with_unless('test -f /usr/local/node/node-v6.0.0/bin/node')
    }
  end

  describe 'with a given target_dir' do
    let(:params) {{
      :ensure               => 'present',
      :version              => 'v6.2.0',
      :make_install         => true,
      :target_dir           => '/bin',
      :cpu_cores            => 1,
      :default_node_version => nil,
      :timeout              => 0,
    }}

    it { should contain_file('nodejs-symlink-bin-with-version-v6.2.0') \
      .with_ensure('link') \
      .with_path('/bin/node-v6.2.0') \
      .with_target('/usr/local/node/node-v6.2.0/bin/node')
    }
  end

  describe 'with make_install = false' do
    let(:params) {{
      :version              => 'v6.2.0',
      :ensure               => 'present',
      :target_dir           => '/usr/local/bin',
      :cpu_cores            => 2,
      :make_install         => false,
      :default_node_version => nil,
      :timeout              => 0,
    }}

    it { should_not contain_exec('nodejs-make-install-v6.2.0') }
  end

  describe 'uninstall' do
    describe 'any instance' do
      let(:params) {{
        :version              => 'v0.12.0',
        :ensure               => 'absent',
        :make_install         => true,
        :target_dir           => '/usr/local/bin',
        :cpu_cores            => 1,
        :default_node_version => 'v4.6.0',
        :timeout              => 0,
      }}

      it { should contain_file('/usr/local/node/node-v0.12.0') \
        .with(:ensure => 'absent', :force => true, :recurse => true) \
      }

      it { should contain_file('/usr/local/bin/node-v0.12.0') \
        .with_ensure('absent') \
      }
    end

    describe 'default instance' do
      let(:params) {{
        :version              => 'v5.6.0',
        :ensure               => 'absent',
        :target_dir           => '/usr/local/bin',
        :make_install         => false,
        :cpu_cores            => 1,
        :default_node_version => 'v5.6.0',
        :timeout              => 0,
      }}

      it { should compile.and_raise_error(/Can't remove the instance/) }
    end
  end

  describe 'nodejs download url' do
    let(:params) {{
      :version              => 'v6.0.0',
      :ensure               => 'present',
      :make_install         => false,
      :target_dir           => '/usr/local/bin',
      :cpu_cores            => 1,
      :default_node_version => 'v5.6.0',
      :timeout              => 0,
    }}

    describe 'os' do
      describe 'darwin' do
        let(:facts) {{
          :hardwaremodel  => '2.6.32-504.12.2.el6.x86_64',
          :kernel         => 'darwin',
          :processorcount => 1,
        }}

        it { should contain_nodejs__instance__download('nodejs-download-v6.0.0') \
          .with_source('https://nodejs.org/dist/v6.0.0/node-v6.0.0-darwin-x64.tar.gz') \
        }
      end

      describe 'linux' do
        let(:facts) {{
          :hardwaremodel  => '2.6.32-504.12.2.el6.x86_64',
          :kernel         => 'linux',
          :processorcount => 1,
        }}

        it { should contain_nodejs__instance__download('nodejs-download-v6.0.0') \
          .with_source('https://nodejs.org/dist/v6.0.0/node-v6.0.0-linux-x64.tar.gz') \
        }
      end
    end

    describe 'arch' do
      describe 'x64' do
        let(:facts) {{
          :hardwaremodel  => '2.6.32-504.12.2.el6.x86_64',
          :kernel         => 'linux',
          :processorcount => 1,
        }}

        it { should contain_nodejs__instance__download('nodejs-download-v6.0.0') \
          .with_source('https://nodejs.org/dist/v6.0.0/node-v6.0.0-linux-x64.tar.gz') \
        }
      end

      describe 'ARM' do
        describe 'armv6l' do
          let(:facts) {{
            :hardwaremodel  => 'armv6l',
            :kernel         => 'linux',
            :processorcount => 1,
          }}

          it { should contain_nodejs__instance__download('nodejs-download-v6.0.0') \
            .with_source('https://nodejs.org/dist/v6.0.0/node-v6.0.0-linux-armv6l.tar.gz') \
          }
        end

        describe 'armv7l' do
          let(:facts) {{
            :hardwaremodel  => 'armv7l',
            :kernel         => 'linux',
            :processorcount => 1,
          }}

          it { should contain_nodejs__instance__download('nodejs-download-v6.0.0') \
            .with_source('https://nodejs.org/dist/v6.0.0/node-v6.0.0-linux-armv7l.tar.gz') \
          }
        end
      end

      describe 'default' do
        let(:facts) {{
          :hardwaremodel  => '2.6.32-504.12.2.el6.x86',
          :kernel         => 'linux',
          :processorcount => 1,
        }}

        it { should contain_nodejs__instance__download('nodejs-download-v6.0.0') \
          .with_source('https://nodejs.org/dist/v6.0.0/node-v6.0.0-linux-x86.tar.gz') \
        }
      end
    end
  end
end
