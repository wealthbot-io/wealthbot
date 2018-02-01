require File.expand_path(File.join(File.dirname(__FILE__), '..', 'util', 'mongodb_md5er'))
Puppet::Type.newtype(:mongodb_user) do
  @doc = 'Manage a MongoDB user. This includes management of users password as well as privileges.'

  ensurable

  def initialize(*args)
    super
    # Sort roles array before comparison.
    self[:roles] = Array(self[:roles]).sort!
  end

  newparam(:name, namevar: true) do
    desc 'The name of the resource.'
  end

  newproperty(:username) do
    desc 'The name of the user.'
    defaultto { @resource[:name] }
  end

  newproperty(:database) do
    desc "The user's target database."
    defaultto do
      raise Puppet::Error, "Parameter 'database' must be set" if provider.database == :absent
    end
    newvalues(%r{^[\w-]+$})
  end

  newparam(:tries) do
    desc 'The maximum amount of two second tries to wait MongoDB startup.'
    defaultto 10
    newvalues(%r{^\d+$})
    munge do |value|
      Integer(value)
    end
  end

  newproperty(:roles, array_matching: :all) do
    desc "The user's roles."
    defaultto ['dbAdmin']
    newvalue(%r{^\w+$})

    # Pretty output for arrays.
    def should_to_s(value)
      value.inspect
    end

    def to_s?(value)
      value.inspect
    end
  end

  newproperty(:password_hash) do
    desc 'The password hash of the user. Use mongodb_password() for creating hash. Only available on MongoDB 3.0 and later.'
    defaultto do
      if @resource[:password].nil?
        raise Puppet::Error, "Property 'password_hash' must be set. Use mongodb_password() for creating hash." if provider.database == :absent
      end
    end
    newvalue(%r{^\w+$})
  end

  newproperty(:password) do
    desc 'The plaintext password of the user.'
    # magic should/is comparison because mongo only returns hashes, but can only
    # consume plaintext on pre-3.0
    def should_to_s(value = @should)
      # Why is this an array sometimes? Ubuntu 14.04...
      value = value.first if value.is_a? Array
      Puppet::Util::MongodbMd5er.md5(@resource[:username], value)
    end

    def to_s?(_value = @is)
      @resource.provider.password_hash
    end

    def insync?(_is)
      should_to_s == to_s?
    end
  end

  autorequire(:package) do
    'mongodb_client'
  end

  autorequire(:service) do
    'mongodb'
  end

  autorequire(:mongodb_database) do
    self[:database]
  end

  validate do
    if self[:password_hash].nil? && self[:password].nil? && provider.password.nil? && provider.password_hash.nil?
      err("Either 'password_hash' or 'password' should be provided")
    elsif !self[:password_hash].nil? && !self[:password].nil?
      err("Only one of 'password_hash' or 'password' should be provided")
    end
  end
end
