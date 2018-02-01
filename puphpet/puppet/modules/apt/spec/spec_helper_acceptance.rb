require 'beaker-rspec'
require 'beaker/puppet_install_helper'
require 'beaker/module_install_helper'

def install_bolt_on(hosts)
  on(hosts, "/opt/puppetlabs/puppet/bin/gem install --source http://rubygems.delivery.puppetlabs.net bolt -v '> 0.0.1'", acceptable_exit_codes: [0, 1]).stdout
end

def pe_install?
  ENV['PUPPET_INSTALL_TYPE'] =~ %r{pe}i
end

run_puppet_install_helper
install_bolt_on(hosts) unless pe_install?
install_module_on(hosts)
install_module_dependencies_on(hosts)

UNSUPPORTED_PLATFORMS = %w[RedHat Suse windows AIX Solaris].freeze

DEFAULT_PASSWORD = if default[:hypervisor] == 'vagrant'
                     'vagrant'
                   elsif default[:hypervisor] == 'vcloud'
                     'Qu@lity!'
                   end

def puppet_version
  (on default, puppet('--version')).output.chomp
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

# This method allows a block to be passed in and if an exception is raised
# that matches the 'error_matcher' matcher, the block will wait a set number
# of seconds before retrying.
# Params:
# - max_retry_count - Max number of retries
# - retry_wait_interval_secs - Number of seconds to wait before retry
# - error_matcher - Matcher which the exception raised must match to allow retry
# Example Usage:
# retry_on_error_matching(3, 5, /OpenGPG Error/) do
#   apply_manifest(pp, :catch_failures => true)
# end
def retry_on_error_matching(max_retry_count = 3, retry_wait_interval_secs = 5, error_matcher = nil)
  try = 0
  begin
    try += 1
    yield
  rescue StandardError => e
    raise unless try < max_retry_count && (error_matcher.nil? || e.message =~ error_matcher)
    sleep retry_wait_interval_secs
    retry
  end
end

RSpec.configure do |c|
  File.expand_path(File.join(File.dirname(__FILE__), '..'))

  # Readable test descriptions
  c.formatter = :documentation

  # Configure all nodes in nodeset
  c.before :suite do
    run_puppet_access_login(user: 'admin') if pe_install? && puppet_version =~ %r{(5\.\d\.\d)}
  end
end
