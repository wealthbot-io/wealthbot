require 'json'
require 'yaml'

def mongod_conf_file
  file = if File.exist? '/etc/mongod.conf'
           '/etc/mongod.conf'
         else
           '/etc/mongodb.conf'
         end
  file
end

Facter.add('mongodb_is_master') do
  setcode do
    if %w[mongo mongod].all? { |m| Facter::Util::Resolution.which m }
      file = mongod_conf_file
      config = YAML.load_file(file)
      mongo_port = nil
      if config.is_a?(Hash) # Using a valid YAML file for mongo 2.6
        unless config['net.port'].nil?
          mongo_port = "--port #{config['net.port']}"
        end
        if config['net.ssl.mode'] == 'requireSSL'
          ssl = "--ssl --host #{Facter.value(:fqdn)}"
        end
        unless config['net.ssl.PEMKeyFile'].nil?
          sslkey = "--sslPEMKeyFile #{config['net.ssl.PEMKeyFile']}"
        end
        unless config['net.ssl.CAFile'].nil?
          sslca = "--sslCAFile #{config['net.ssl.CAFile']}"
        end
        ipv6 = '--ipv6' unless config['net.ipv6'].nil?
      else # It has to be a key-value config file
        config = {}
        File.readlines(file).map do |line|
          k, v = line.split('=')
          config[k.rstrip] = v.lstrip.chomp if k && v
        end
        mongo_port = "--port #{config['port']}" unless config['port'].nil?
        if config['ssl'] == 'requireSSL'
          ssl = "--ssl --host #{Facter.value(:fqdn)}"
        end
        unless config['sslcert'].nil?
          sslkey = "--sslPEMKeyFile #{config['sslcert']}"
        end
        sslca = "--sslCAFile #{config['sslca']}" unless config['sslca'].nil?
        ipv6 = '--ipv6' unless config['ipv6'].nil?
      end
      e = File.exist?('/root/.mongorc.js') ? 'load(\'/root/.mongorc.js\'); ' : ''

      # Check if the mongodb server is responding:
      Facter::Core::Execution.exec("mongo --quiet #{ssl} #{sslkey} #{sslca} #{ipv6} #{mongo_port} --eval \"#{e}printjson(db.adminCommand({ ping: 1 }))\"")

      if $CHILD_STATUS.success?
        Facter::Core::Execution.exec("mongo --quiet #{ssl} #{sslkey} #{sslca} #{ipv6} #{mongo_port} --eval \"#{e}db.isMaster().ismaster\"")
      else
        'not_responding'
      end
    else
      'not_installed'
    end
  end
end
