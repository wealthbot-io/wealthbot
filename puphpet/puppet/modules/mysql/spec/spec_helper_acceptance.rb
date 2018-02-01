require 'puppet'
require 'beaker-rspec'
require 'beaker/puppet_install_helper'
require 'beaker/module_install_helper'
require 'beaker/i18n_helper'

def install_bolt_on(hosts)
  on(hosts, "/opt/puppetlabs/puppet/bin/gem install --source http://rubygems.delivery.puppetlabs.net bolt -v '> 0.0.1'", acceptable_exit_codes: [0, 1]).stdout
end

def pe_install?
  ENV['PUPPET_INSTALL_TYPE'] =~ %r{pe}i
end

run_puppet_install_helper
install_ca_certs unless ENV['PUPPET_INSTALL_TYPE'] =~ %r{pe}i
install_bolt_on(hosts) unless pe_install?
install_module_on(hosts)
install_module_dependencies_on(hosts)

UNSUPPORTED_PLATFORMS = %w[Windows Solaris AIX].freeze

def puppet_version
  (on default, puppet('--version')).output.chomp
end

DEFAULT_PASSWORD = if default[:hypervisor] == 'vagrant'
                     'vagrant'
                   elsif default[:hypervisor] == 'vcloud'
                     'Qu@lity!'
                   end

def run_puppet_access_login(user:, password: '~!@#$%^*-/ aZ', lifetime: '5y')
  on(master, puppet('access', 'login', '--username', user, '--lifetime', lifetime), stdin: password)
end

def run_task(task_name:, params: nil, password: DEFAULT_PASSWORD)
  if pe_install?
    run_puppet_task(task_name: task_name, params: params)
  else
    run_bolt_task(task_name: task_name, params: params, password: password)
  end
end

def run_bolt_task(task_name:, params: nil, password: DEFAULT_PASSWORD)
  on(master, "/opt/puppetlabs/puppet/bin/bolt task run #{task_name} --modules /etc/puppetlabs/code/modules/service --nodes localhost --password #{password} #{params}", acceptable_exit_codes: [0, 1]).stdout # rubocop:disable Metrics/LineLength
end

def run_puppet_task(task_name:, params: nil)
  on(master, puppet('task', 'run', task_name, '--nodes', fact_on(master, 'fqdn'), params.to_s), acceptable_exit_codes: [0, 1]).stdout
end

def expect_multiple_regexes(result:, regexes:)
  regexes.each do |regex|
    expect(result).to match(regex)
  end
end

RSpec.configure do |c|
  # Readable test descriptions
  c.formatter = :documentation

  # detect the situation where PUP-5016 is triggered and skip the idempotency tests in that case
  # also note how fact('puppetversion') is not available because of PUP-4359
  if fact('osfamily') == 'Debian' && fact('operatingsystemmajrelease') == '8' && shell('puppet --version').stdout =~ %r{^4\.2}
    c.filter_run_excluding skip_pup_5016: true
  end

  # Configure all nodes in nodeset
  c.before :suite do
    run_puppet_access_login(user: 'admin') if pe_install?
    hosts.each do |host|
      # This will be removed, this is temporary to test localisation.
      if (fact('osfamily') == 'Debian' || fact('osfamily') == 'RedHat') && (puppet_version >= '4.10.5' && puppet_version < '5.2.0')
        on(host, 'mkdir /opt/puppetlabs/puppet/share/locale/ja')
        on(host, 'touch /opt/puppetlabs/puppet/share/locale/ja/puppet.po')
      end
      if fact('osfamily') == 'Debian'
        # install language on debian systems
        install_language_on(host, 'ja_JP.utf-8') if not_controller(host)
        # This will be removed, this is temporary to test localisation.
      end
      # Required for binding tests.
      if fact('osfamily') == 'RedHat'
        if fact('operatingsystemmajrelease') =~ %r{7} || fact('operatingsystem') =~ %r{Fedora}
          shell('yum install -y bzip2')
        end
      end
      on host, puppet('module', 'install', 'stahnma/epel')
    end
  end
end

shared_examples 'a idempotent resource' do
  it 'applies with no errors' do
    apply_manifest(pp, catch_failures: true)
  end

  it 'applies a second time without changes', :skip_pup_5016 do
    apply_manifest(pp, catch_changes: true)
  end
end
