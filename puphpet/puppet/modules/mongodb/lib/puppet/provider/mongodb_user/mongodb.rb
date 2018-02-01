require File.expand_path(File.join(File.dirname(__FILE__), '..', 'mongodb'))
Puppet::Type.type(:mongodb_user).provide(:mongodb, parent: Puppet::Provider::Mongodb) do
  desc 'Manage users for a MongoDB database.'

  defaultfor kernel: 'Linux'

  def self.instances
    require 'json'

    if db_ismaster
      if mongo_24?
        dbs = JSON.parse mongo_eval('printjson(db.getMongo().getDBs()["databases"].map(function(db){return db["name"]}))') || 'admin'

        allusers = []

        dbs.each do |db|
          users = JSON.parse mongo_eval('printjson(db.system.users.find().toArray())', db)

          allusers += users.map do |user|
            new(name: user['_id'],
                ensure: :present,
                username: user['user'],
                database: db,
                roles: user['roles'].sort,
                password_hash: user['pwd'])
          end
        end
        return allusers
      else
        users = JSON.parse mongo_eval('printjson(db.system.users.find().toArray())')

        users.map do |user|
          new(name: user['_id'],
              ensure: :present,
              username: user['user'],
              database: user['db'],
              roles: from_roles(user['roles'], user['db']),
              password_hash: user['credentials']['MONGODB-CR'])
        end
      end
    else
      Puppet.warning 'User info is available only from master host'
      return []
    end
  end

  # Assign prefetched users based on username and database, not on id and name
  def self.prefetch(resources)
    users = instances
    resources.each do |name, resource|
      provider = users.find { |user| user.username == (resource[:username]) && user.database == (resource[:database]) }
      resources[name].provider = provider if provider
    end
  end

  mk_resource_methods

  def create
    if db_ismaster
      if mongo_24?
        if @resource[:password_hash]
          raise Puppet::Error, "password_hash can't be set on MongoDB older than 3.0; use password instead"
        end
        user = {
          user: @resource[:username],
          pwd: @resource[:password],
          roles: @resource[:roles]
        }

        mongo_eval("db.addUser(#{user.to_json})", @resource[:database])
      else
        password_hash = @resource[:password_hash]

        if password_hash
        elsif @resource[:password]
          password_hash = Puppet::Util::MongodbMd5er.md5(@resource[:username], @resource[:password])
        end
        cmd_json = <<-EOS.gsub(%r{^\s*}, '').gsub(%r{$\n}, '')
	{
	  "createUser": "#{@resource[:username]}",
	  "pwd": "#{password_hash}",
	  "customData": {"createdBy": "Puppet Mongodb_user['#{@resource[:name]}']"},
	  "roles": #{@resource[:roles].to_json},
	  "digestPassword": false
	}
	EOS

        mongo_eval("db.runCommand(#{cmd_json})", @resource[:database])
      end
    else
      Puppet.warning 'User creation is available only from master host'

      @property_hash[:ensure] = :present
      @property_hash[:username] = @resource[:username]
      @property_hash[:database] = @resource[:database]
      @property_hash[:password_hash] = ''
      @property_hash[:roles] = @resource[:roles]

      exists? ? (return true) : (return false)
    end
  end

  def destroy
    if db_ismaster
      if mongo_24?
        mongo_eval("db.removeUser('#{@resource[:username]}')")
      else
        mongo_eval("db.dropUser('#{@resource[:username]}')")
      end
    else
      mongo_eval("db.dropUser('#{@resource[:username]}')")
    end
  end

  def exists?
    !(@property_hash[:ensure] == :absent || @property_hash[:ensure].nil?)
  end

  def password_hash=(_value)
    if db_ismaster
      cmd_json = <<-EOS.gsub(%r{^\s*}, '').gsub(%r{$\n}, '')
      {
          "updateUser": "#{@resource[:username]}",
          "pwd": "#{@resource[:password_hash]}",
          "digestPassword": false
      }
      EOS
      mongo_eval("db.runCommand(#{cmd_json})", @resource[:database])
    else
      Puppet.warning 'User password operations are available only from master host'
    end
  end

  def password=(value)
    if mongo_24?
      mongo_eval("db.changeUserPassword('#{@resource[:username]}','#{value}')", @resource[:database])
    else
      cmd_json = <<-EOS.gsub(%r{^\s*}, '').gsub(%r{$\n}, '')
      {
          "updateuser": "#{@resource[:username]}",
          "pwd": "#{@resource[:password]}",
          "digestpassword": true
      }
      EOS

      mongo_eval("db.runCommand(#{cmd_json})", @resource[:database])
    end
  end

  def roles=(roles)
    if db_ismaster
      if mongo_24?
        mongo_eval("db.system.users.update({user:'#{@resource[:username]}'}, { $set: {roles: #{@resource[:roles].to_json}}})")
      else
        grant = roles - @property_hash[:roles]
        unless grant.empty?
          mongo_eval("db.getSiblingDB('#{@resource[:database]}').grantRolesToUser('#{@resource[:username]}', #{grant. to_json})")
        end

        revoke = @property_hash[:roles] - roles
        unless revoke.empty?
          mongo_eval("db.getSiblingDB('#{@resource[:database]}').revokeRolesFromUser('#{@resource[:username]}', #{revoke.to_json})")
        end
      end
    else
      Puppet.warning 'User roles operations are available only from master host'
    end
  end

  private

  def self.from_roles(roles, db)
    roles.map do |entry|
      if entry['db'] == db
        entry['role']
      else
        "#{entry['role']}@#{entry['db']}"
      end
    end.sort
  end
end
