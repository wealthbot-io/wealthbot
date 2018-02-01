require 'spec_helper'

describe 'nodejs::instances', :type => :class do
  let(:title) { 'nodejs::instances' }
  let(:facts) {{
    :kernel         => 'linux',
    :hardwaremodel  => 'x86',
    :osfamily       => 'Debian',
    :processorcount => 2,
  }}
  before(:each) {
    Puppet::Parser::Functions.newfunction(:evaluate_version, :type => :rvalue) do |args|
      return 'v4.4.7' if args[0] == 'lts'
      return args[0]
    end
  }

  describe 'with a given version' do
    let(:params) {{
      :node_version        => 'v5.0.0',
      :instances           => {},
      :instances_to_remove => [],
      :target_dir          => '/usr/local/bin',
      :make_install        => false,
      :cpu_cores           => 2,
      :nodejs_default_path => '/usr/local/node/node-default',
      :download_timeout    => 0,
    }}

    it { should contain_nodejs__instance('nodejs-custom-instance-v5.0.0') \
      .with_version('v5.0.0')
    }

    it { should contain_file('/usr/local/node/node-default') \
      .with_ensure('link') \
      .with_target('/usr/local/node/node-v5.0.0')
    }
  end

  describe 'it adds multiple instances and declares a default one' do
    let(:params) {{
      :node_version => "v4.4.7",
      :instances    => {
        "node-lts"  => {
          "version" => "lts"
        }
      },
      :instances_to_remove => [],
      :make_install        => false,
      :target_dir          => '/usr/local/bin',
      :cpu_cores           => 2,
      :nodejs_default_path => '/usr/local/node/node-default',
      :download_timeout    => 0,
    }}

    it { should contain_nodejs__instance("nodejs-custom-instance-v4.4.7") \
      .with_version('v4.4.7') \
      .with_ensure('present') \
      .with_target_dir('/usr/local/bin') \
      .with_make_install(false) \
      .with_cpu_cores(2)
    }

    it { should contain_file("/usr/local/node/node-default") \
      .with_ensure("link") \
      .with_target("/usr/local/node/node-v4.4.7") \
    }

    it { should contain_file("/usr/local/bin/node") \
      .with_ensure("link") \
      .with_target("/usr/local/node/node-default/bin/node")
    }

    it { should contain_file("/usr/local/bin/npm") \
      .with_ensure("link") \
      .with_target("/usr/local/node/node-default/bin/npm")
    }
  end

  describe 'adds multiple instances from a hash and completes the hash with default values' do
    let(:params) {{
      :instances    => {
        "v6.7"      => {
          "version" => 'v6.7.0',
        },
        "v6.4"           => {
          "version"      => 'v6.4.0',
          "make_install" => true,
        }
      },
      :node_version        => 'v6.7.0',
      :make_install        => false,
      :target_dir          => '/usr/local/bin',
      :cpu_cores           => 2,
      :instances_to_remove => [],
      :nodejs_default_path => '/usr/local/node/node-default',
      :download_timeout    => 0,
    }}

    it { should contain_nodejs__instance("nodejs-custom-instance-v6.7.0") \
      .with_version('v6.7.0') \
      .with_ensure('present') \
      .with_target_dir('/usr/local/bin') \
      .with_make_install(false) \
      .with_cpu_cores(2)
    }

    it { should contain_nodejs__instance("nodejs-custom-instance-v6.4.0") \
      .with_version('v6.4.0') \
      .with_ensure('present') \
      .with_target_dir('/usr/local/bin') \
      .with_make_install(true) \
      .with_cpu_cores(2)
    }
  end

  describe 'manages instances to be removed' do
    let(:params) {{
      :node_version        => 'v6.5.0',
      :make_install        => false,
      :target_dir          => '/usr/local/bin',
      :cpu_cores           => 2,
      :instances           => {},
      :instances_to_remove => ['v6.4.0'],
      :nodejs_default_path => '/usr/local/node/node-default',
      :download_timeout    => 0,
    }}

    it { should contain_nodejs__instance("nodejs-uninstall-custom-v6.4.0") \
      .with_ensure("absent") \
      .with_target_dir("/usr/local/bin") \
      .with_make_install(false) \
      .with_cpu_cores(0) \
      .with_version("v6.4.0") \
      .with_target_dir("/usr/local/bin") \
    }
  end

  describe 'with an invalid nodejs instance' do
    let(:params) {{
      :node_version => 'v7.0.0',
      :make_install => false,
      :target_dir   => '/usr/local/bin',
      :cpu_cores    => 2,
      :instances    => {
        "node-lts"  => {
          "version" => 'v6.9.1'
        }
      },
      :instances_to_remove => [],
      :nodejs_default_path => '/usr/local/node/node-default',
      :download_timeout    => 0,
    }}

    it { should compile.and_raise_error(/Cannot create a default instance with version/) }
  end
end
