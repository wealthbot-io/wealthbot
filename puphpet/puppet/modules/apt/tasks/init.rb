#!/opt/puppetlabs/puppet/bin/ruby
require 'json'
require 'open3'
require 'puppet'

def apt_get(action)
  cmd_string = "apt-get #{action}"
  cmd_string << ' -y' if  action == 'upgrade'
  stdout, stderr, status = Open3.capture3(cmd_string)
  raise Puppet::Error, stderr if status != 0
  { status: stdout.strip }
end

params = JSON.parse(STDIN.read)
action = params['action']

begin
  result = apt_get(action)
  puts result.to_json
  exit 0
rescue Puppet::Error => e
  puts({ status: 'failure', error: e.message }.to_json)
  exit 1
end
