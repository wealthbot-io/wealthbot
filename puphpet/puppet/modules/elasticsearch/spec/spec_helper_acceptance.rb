require 'beaker-rspec'
require 'securerandom'
require 'thread'
require 'infrataster/rspec'
require 'rspec/retry'

require_relative 'spec_helper_tls'
require_relative 'spec_utilities'

def test_settings
  RSpec.configuration.test_settings
end

def f
  RSpec.configuration.fact
end

RSpec.configure do |c|
  c.add_setting :test_settings, :default => {}
  unless ENV['snapshot_version'].nil?
    c.add_setting :snapshot_version
    c.snapshot_version = ENV['snapshot_version']
  end

  # rspec-retry
  c.display_try_failure_messages = true
  c.default_sleep_interval = 5
  # General-case retry keyword for unstable tests
  c.around :each, :with_retries do |example|
    example.run_with_retry retry: 4
  end
  # More forgiving retry config for really flaky tests
  c.around :each, :with_generous_retries do |example|
    example.run_with_retry retry: 10
  end

  # Helper hook for module cleanup
  c.after :context, :with_cleanup do
    apply_manifest <<-EOS
      class { 'elasticsearch':
        ensure      => 'absent',
        manage_repo => true,
      }
      elasticsearch::instance { 'es-01': ensure => 'absent' }

      file { '/usr/share/elasticsearch/plugin':
        ensure  => 'absent',
        force   => true,
        recurse => true,
        require => Class['elasticsearch'],
      }
    EOS
  end

  c.before :context, :with_certificates do
    @keystore_password = SecureRandom.hex
    @role = [*('a'..'z')].sample(8).join

    # Setup TLS cert placement
    @tls = gen_certs(2, '/tmp')

    create_remote_file hosts, @tls[:ca][:cert][:path], @tls[:ca][:cert][:pem]
    @tls[:clients].each do |node|
      node.each do |_type, params|
        create_remote_file hosts, params[:path], params[:pem]
      end
    end
  end

  c.after :context, :then_purge do
    shell 'rm -rf {/usr/share,/etc,/var/lib}/elasticsearch*'
  end
end

files_dir = ENV['files_dir'] || './spec/fixtures/artifacts'
RSpec.configuration.test_settings['files_dir'] = files_dir

# General bootstrapping steps for each host
hosts.each do |host|
  # Set the host to 'aio' in order to adopt the puppet-agent style of
  # installation, and configure paths/etc.
  host[:type] = 'aio'
  configure_defaults_on host, 'aio'

  # Install Puppet
  #
  # We spawn a thread to print dots periodically while installing puppet to
  # avoid inactivity timeouts in Travis. Don't judge me.
  progress = Thread.new do
    print 'Installing puppet..'
    print '.' while sleep 5
  end

  case host.name
  when /debian-9/, /opensuse/
    # A few special cases need to be installed from gems (if the distro is
    # very new and has no puppet repo package or has no upstream packages).
    install_puppet_from_gem(
      host,
      version: Gem.loaded_specs['puppet'].version
    )
  else
    # Otherwise, just use the all-in-one agent package.
    install_puppet_agent_on(
      host,
      puppet_agent_version: to_agent_version(Gem.loaded_specs['puppet'].version)
    )
  end
  # Quit the print thread and include some debugging.
  progress.exit
  puts "done. Installed version #{shell('puppet --version').output}"

  RSpec.configure do |c|
    c.add_setting :fact, :default => JSON.parse(fact('', '-j'))
  end

  if f['os']['family'] == 'Suse'
    install_package host,
                    '--force-resolution augeas-devel libxml2-devel ruby-devel'
    on host, 'gem install ruby-augeas --no-ri --no-rdoc'
  end

  ext = case f['os']['family']
        when 'Debian'
          'deb'
        else
          'rpm'
        end

  snapshot_package = {
    :src => "#{files_dir}/elasticsearch-2.3.5.#{ext}",
    :dst => "/tmp/elasticsearch-2.3.5.#{ext}"
  }

  scp_to host,
         snapshot_package[:src],
         snapshot_package[:dst]
  scp_to host,
         "#{files_dir}/elasticsearch-kopf.zip",
         '/tmp/elasticsearch-kopf.zip'

  RSpec.configuration.test_settings['snapshot_package'] = \
    "file:#{snapshot_package[:dst]}"

  test_settings['integration_package'] = {
    :src => "#{files_dir}/elasticsearch-snapshot.#{ext}",
    :dst => "/tmp/elasticsearch-snapshot.#{ext}",
    :file => "file:/tmp/elasticsearch-snapshot.#{ext}"
  }

  Infrataster::Server.define(:docker) do |server|
    server.address = host[:ip]
    server.ssh = host[:ssh].tap { |s| s.delete :forward_agent }
  end
  Infrataster::Server.define(:container) do |server|
    server.address = host[:vm_ip] # this gets ignored anyway
    server.from = :docker
  end
end

RSpec.configure do |c|
  c.before :suite do
    # Install module and dependencies
    install_dev_puppet_module :ignore_list => [
      'junit'
    ] + Beaker::DSL::InstallUtils::ModuleUtils::PUPPET_MODULE_INSTALL_IGNORE

    hosts.each do |host|
      copy_hiera_data_to(host, 'spec/fixtures/hiera/hieradata/')

      modules = %w[archive datacat java java_ks stdlib]

      dist_module = {
        'Debian' => ['apt'],
        'Suse'   => ['zypprepo'],
        'RedHat' => ['concat']
      }[f['os']['family']]

      modules += dist_module unless dist_module.nil?

      modules.each do |mod|
        copy_module_to host,
          :module_name => mod,
          :source      => "spec/fixtures/modules/#{mod}"
      end

      on(host, 'mkdir -p etc/puppet/modules/another/files/')

      # Apt doesn't update package caches sometimes, ensure we're caught up.
      shell 'apt-get update' if f['os']['family'] == 'Debian'
    end

    # Use the Java class once before the suite of tests
    unless shell('command -v java', :accept_all_exit_codes => true).exit_code.zero?
      apply_manifest <<~MANIFEST
        class { "java" :
          distribution => "jre",
        }
      MANIFEST
    end
  end

  c.after :suite do
    if ENV['ES_VERSION']
      hosts.each do |host|
        timestamp = Time.now
        log_dir = File.join('./spec/logs', timestamp.strftime('%F_%H_%M_%S'))
        FileUtils.mkdir_p(log_dir) unless File.directory?(log_dir)
        scp_from(host, '/var/log/elasticsearch', log_dir)
      end
    end
  end
end

require_relative 'spec_acceptance_common'

# Java 8 is only easy to manage on recent distros
def v5x_capable?
  (f['os']['family'] == 'RedHat' and \
    not (f['os']['name'] == 'OracleLinux' and \
    f['os']['release']['major'] == '6')) or \
    f.dig 'os', 'distro', 'codename' == 'xenial'
end
