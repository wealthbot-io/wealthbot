Puppet::Type.newtype(:postgresql_conn_validator) do

  @doc = "Verify that a connection can be successfully established between a node
          and the PostgreSQL server.  Its primary use is as a precondition to
          prevent configuration changes from being applied if the PostgreSQL
          server cannot be reached, but it could potentially be used for other
          purposes such as monitoring."

  ensurable do
    defaultvalues
    defaultto :present
  end

  newparam(:name, :namevar => true) do
    desc 'An arbitrary name used as the identity of the resource.'
  end

  newparam(:db_name) do
    desc "The name of the database you are trying to validate a connection with."
  end

  newparam(:db_username) do
    desc "A user that has access to the target PostgreSQL database."
  end

  newparam(:db_password) do
    desc "The password required to access the target PostgreSQL database."
  end

  newparam(:host) do
    desc 'The DNS name or IP address of the server where PostgreSQL should be running.'
  end

  newparam(:port) do
    desc 'The port that the PostgreSQL server should be listening on.'

    validate do |value|
      Integer(value)
    end
    munge do |value|
      Integer(value)
    end
  end

  newparam(:connect_settings) do
    desc 'Hash of environment variables for connection to a db.'
  end

  newparam(:sleep) do
    desc "The length of sleep time between connection tries."

    validate do |value|
      Integer(value)
    end
    munge do |value|
      Integer(value)
    end

    defaultto 2
  end

  newparam(:tries) do
    desc "The number of tries to validate the connection to the target PostgreSQL database."

    validate do |value|
      Integer(value)
    end
    munge do |value|
      Integer(value)
    end

    defaultto 10
  end

  newparam(:psql_path) do
    desc "Path to the psql command."
  end

  newparam(:run_as) do
    desc "System user that will run the psql command."
  end

  newparam(:command) do
    desc "Command to run against target database."

    defaultto "SELECT 1"
  end
end
