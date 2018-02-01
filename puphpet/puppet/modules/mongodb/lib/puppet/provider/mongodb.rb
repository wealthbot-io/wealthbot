require 'yaml'
require 'json'
class Puppet::Provider::Mongodb < Puppet::Provider
  # Without initvars commands won't work.
  initvars
  commands mongo: 'mongo'

  # Optional defaults file
  def self.mongorc_file
    "load('#{Facter.value(:root_home)}/.mongorc.js'); " if File.file?("#{Facter.value(:root_home)}/.mongorc.js")
  end

  def mongorc_file
    self.class.mongorc_file
  end

  def self.mongod_conf_file
    file = if File.exist? '/etc/mongod.conf'
             '/etc/mongod.conf'
           else
             '/etc/mongodb.conf'
           end
    file
  end

  def self.mongo_conf
    file = mongod_conf_file
    # The mongo conf is probably a key-value store, even though 2.6 is
    # supposed to use YAML, because the config template is applied
    # based on $mongodb::globals::version which is the user will not
    # necessarily set. This attempts to get the port from both types of
    # config files.
    config = YAML.load_file(file)
    config_hash = {}
    if config.is_a?(Hash) # Using a valid YAML file for mongo 2.6
      config_hash['bindip'] = config['net.bindIp']
      config_hash['port'] = config['net.port']
      config_hash['ipv6'] = config['net.ipv6']
      config_hash['allowInvalidHostnames'] = config['net.ssl.allowInvalidHostnames']
      config_hash['ssl'] = config['net.ssl.mode']
      config_hash['sslcert'] = config['net.ssl.PEMKeyFile']
      config_hash['sslca'] = config['net.ssl.CAFile']
      config_hash['auth'] = config['security.authorization']
      config_hash['shardsvr'] = config['sharding.clusterRole']
      config_hash['confsvr'] = config['sharding.clusterRole']
    else # It has to be a key-value config file
      config = {}
      File.readlines(file).map do |line|
        k, v = line.split('=')
        config[k.rstrip] = v.lstrip.chomp if k && v
      end
      config_hash['bindip'] = config['bind_ip']
      config_hash['port'] = config['port']
      config_hash['ipv6'] = config['ipv6']
      config_hash['ssl'] = config['sslOnNormalPorts']
      config_hash['allowInvalidHostnames'] = config['allowInvalidHostnames']
      config_hash['sslcert'] = config['sslPEMKeyFile']
      config_hash['sslca'] = config['sslCAFile']
      config_hash['auth'] = config['auth']
      config_hash['shardsvr'] = config['shardsvr']
      config_hash['confsvr'] = config['confsvr']
    end

    config_hash
  end

  def self.ipv6_is_enabled(config = nil)
    config ||= mongo_conf
    config['ipv6']
  end

  def self.ssl_is_enabled(config = nil)
    config ||= mongo_conf
    ssl_mode = config.fetch('ssl')
    ssl_mode.nil? ? false : ssl_mode != 'disabled'
  end

  def self.ssl_invalid_hostnames(config = nil)
    config ||= mongo_conf
    config['allowInvalidHostnames']
  end

  def self.mongo_cmd(db, host, cmd)
    config = mongo_conf

    args = [db, '--quiet', '--host', host]
    args.push('--ipv6') if ipv6_is_enabled(config)
    args.push('--sslAllowInvalidHostnames') if ssl_invalid_hostnames(config)

    if ssl_is_enabled(config)
      args.push('--ssl')
      args += ['--sslPEMKeyFile', config['sslcert']]

      ssl_ca = config['sslca']
      args += ['--sslCAFile', ssl_ca] unless ssl_ca.nil?
    end

    args += ['--eval', cmd]
    mongo(args)
  end

  def self.conn_string
    config = mongo_conf
    bindip = config.fetch('bindip')
    if bindip
      first_ip_in_list = bindip.split(',').first
      ip_real = case first_ip_in_list
                when '0.0.0.0'
                  '127.0.0.1'
                when %r{\[?::0\]?}
                  '::1'
                else
                  first_ip_in_list
                end
    end

    port = config.fetch('port')
    shardsvr = config.fetch('shardsvr')
    confsvr = config.fetch('confsvr')
    port_real = if port
                  port
                elsif !port && (confsvr.eql?('configsvr') || confsvr.eql?('true'))
                  27_019
                elsif !port && (shardsvr.eql?('shardsvr') || shardsvr.eql?('true'))
                  27_018
                else
                  27_017
                end

    "#{ip_real}:#{port_real}"
  end

  def self.db_ismaster
    cmd_ismaster = 'db.isMaster().ismaster'
    cmd_ismaster = mongorc_file + cmd_ismaster if mongorc_file
    db = 'admin'
    res = mongo_cmd(db, conn_string, cmd_ismaster).to_s.chomp
    res.eql?('true') ? true : false
  end

  def db_ismaster
    self.class.db_ismaster
  end

  def self.auth_enabled(config = nil)
    config ||= mongo_conf
    config['auth'] && config['auth'] != 'disabled'
  end

  # Mongo Command Wrapper
  def self.mongo_eval(cmd, db = 'admin', retries = 10, host = nil)
    retry_count = retries
    retry_sleep = 3
    cmd = mongorc_file + cmd if mongorc_file

    out = nil
    retry_count.times do |n|
      begin
        out = if host
                mongo_cmd(db, host, cmd)
              else
                mongo_cmd(db, conn_string, cmd)
              end
      rescue => e
        Puppet.debug "Request failed: '#{e.message}' Retry: '#{n}'"
        sleep retry_sleep
        next
      end
      break
    end

    unless out
      raise Puppet::ExecutionFailure, "Could not evaluate MongoDB shell command: #{cmd}"
    end

    %w[ObjectId NumberLong].each do |data_type|
      out.gsub!(%r{#{data_type}\(([^)]*)\)}, '\1')
    end
    out.gsub!(%r{^Error\:.+}, '')
    out.gsub!(%r{^.*warning\:.+}, '') # remove warnings if sslAllowInvalidHostnames is true
    out.gsub!(%r{^.*The server certificate does not match the host name.+}, '') # remove warnings if sslAllowInvalidHostnames is true mongo 3.x
    out
  end

  def mongo_eval(cmd, db = 'admin', retries = 10, host = nil)
    self.class.mongo_eval(cmd, db, retries, host)
  end

  # Mongo Version checker
  def self.mongo_version
    @mongo_version ||= mongo_eval('db.version()')
  end

  def mongo_version
    self.class.mongo_version
  end

  def self.mongo_24?
    v = mongo_version
    !v[%r{^2\.4\.}].nil?
  end

  def mongo_24?
    self.class.mongo_24?
  end
end
