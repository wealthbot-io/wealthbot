require 'spec_helper'

describe 'nodejs::npm', :type => :define do
  let(:title) { 'nodejs::npm' }
  let(:facts) {{
    :kernel         => 'linux',
    :hardwaremodel  => 'x86',
    :osfamily       => 'Debian',
    :path           => '/usr/bin:/bin:/usr/sbin:/sbin:/usr/local/bin',
    :processorcount => 2,
  }}

  describe 'install npm package' do
    let (:params) {{
      :name      => 'yo-foo',
      :pkg_name  => 'yo',
      :directory => '/foo'
    }}

    it { should contain_exec('npm_install_yo_/foo') \
      .with_command('npm install  yo') \
      .with_unless("npm list -p -l | grep '/foo/node_modules/yo:yo'")
    }
  end

  describe 'uninstall npm package' do
    let (:params) {{
      :name      => 'foo-yo',
      :ensure    => 'absent',
      :pkg_name  => 'yo',
      :directory => '/foo'
    }}

    it { should contain_exec('npm_remove_yo_/foo') \
      .with_command('npm remove  yo') \
      .with_onlyif("npm list -p -l | grep '/foo/node_modules/yo:yo'")
    }
  end

  describe 'install npm package with version' do
    let (:params) {{
      :name      => 'foo-yo',
      :version   => '1.4',
      :pkg_name  => 'yo',
      :directory => '/foo'
    }}

    it { should contain_exec('npm_install_yo_/foo') \
      .with_command('npm install  yo@1.4') \
      .with_unless("npm list -p -l | grep '/foo/node_modules/yo:yo@1.4'")
    }
  end

  describe 'home path for unix systems' do
    # just assert against OS families
    operating_systems = ['Debian', 'RedHat']
    operating_systems.each do |os|
      let (:params) {{
        :name      => 'foo-yo',
        :exec_user => 'ma27',
        :pkg_name  => 'yo',
        :directory => '/foo',
        :home_dir  => '/home/ma27',
      }}
      let(:facts) {{
        :operatingsystem => os,
        :osfamily        => os,
        :hardwaremodel   => 'x86',
        :kernel          => 'linux',
        :path            => '/usr/bin:/bin:/usr/sbin:/sbin:/usr/local/bin',
        :processorcount  => 2,
      }}

      it { should contain_exec('npm_install_yo_/foo') \
        .with_command('npm install  yo') \
        .with_unless("npm list -p -l | grep '/foo/node_modules/yo:yo'") \
        .with_environment('HOME=/home/ma27')
      }
    end
  end

  describe 'installation from a package.json file' do
    let (:params) {{
      :list      => true,
      :directory => '/foo',
      :options   => '-x -z'
    }}

    it { should contain_exec('npm_install_dir_/foo') \
      .with_command('npm install -x -z')
    }
  end
end
