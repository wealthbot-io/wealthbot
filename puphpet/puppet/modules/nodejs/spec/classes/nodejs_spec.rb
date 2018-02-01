require 'spec_helper'

describe 'nodejs', :type => :class do
  let(:title) { 'nodejs' }

  let(:facts) {{
    :kernel         => 'linux',
    :hardwaremodel  => 'x86',
    :osfamily       => 'Debian',
    :processorcount => 2,
  }}

  before(:each) { 
    Puppet::Parser::Functions.newfunction(:evaluate_version, :type => :rvalue) do |args|
      return 'v6.0.1' if args[0] == 'latest'
      return args[0] # simply return default
    end

    Puppet::Parser::Functions.newfunction(:validate_nodejs_version) {
       |args| 'v6.0.1'
    }
  }

  describe 'with node_path' do
    let(:params) {{
      :node_path => '/usr/local/node/node-v5.4.1/lib/node_modules'
    }}

    it { should contain_file('/etc/profile.d/nodejs.sh') \
      .with_content(/(.*)NODE_PATH=\/usr\/local\/node\/node-v5.4.1\/lib\/node_modules/)
    }
  end

  describe 'manages nodejs instances' do
    let(:params) {{
      :instances        => {
        "nodejs-latest" => { "version" => "latest" }
      },
      :version             => "latest",
      :instances_to_remove => ["v0.12.2"]
    }}

    it { should contain_class('nodejs::instances') \
      .with_instances({ "nodejs-latest" => { "version" => "latest" } }) \
      .with_node_version("v6.0.1") \
      .with_target_dir("/usr/local/bin") \
      .with_make_install(false) \
      .with_cpu_cores(2) \
      .with_instances_to_remove(["v0.12.2"])
    }
  end

  describe 'package setup is included by default' do
    it { should contain_class('nodejs::instance::pkgs') \
      .with_make_install(false) \
    }
  end

  describe 'package setup can be excluded' do
    let(:params) {{
      :build_deps => false,
    }}

    it { should_not contain_class('::nodejs::instance::pkgs') }
  end
end
