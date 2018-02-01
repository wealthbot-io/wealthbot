#!/opt/puppetlabs/puppet/bin/ruby
require 'json'
require 'open3'
require 'puppet'

def get(sql, database, user, password)
  cmd_string = "mysql -e \"#{sql}\""
  cmd_string << " --database=#{database}" unless database.nil?
  cmd_string << " --user=#{user}" unless user.nil?
  cmd_string << " --password=#{password}" unless password.nil?
  stdout, stderr, status = Open3.capture3(cmd_string)
  raise Puppet::Error, _("stderr: '#{stderr}'") if status != 0
  { status: stdout.strip }
end

params = JSON.parse(STDIN.read)
database = params['database']
user = params['user']
password = params['password']
sql = params['sql']

begin
  result = get(sql, database, user, password)
  puts result.to_json
  exit 0
rescue Puppet::Error => e
  puts({ status: 'failure', error: e.message }.to_json)
  exit 1
end
