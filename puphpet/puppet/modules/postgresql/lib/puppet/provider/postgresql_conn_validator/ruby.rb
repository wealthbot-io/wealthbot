$LOAD_PATH.unshift(File.join(File.dirname(__FILE__),"..","..",".."))
require 'puppet/util/postgresql_validator'

# This file contains a provider for the resource type `postgresql_conn_validator`,
# which validates the puppetdb connection by attempting an https connection.

Puppet::Type.type(:postgresql_conn_validator).provide(:ruby) do
  desc "A provider for the resource type `postgresql_conn_validator`,
        which validates the PostgreSQL connection by attempting a query
        to the target PostgreSQL server."

  # Test to see if the resource exists, returns true if it does, false if it
  # does not.
  #
  # Here we simply monopolize the resource API, to execute a test to see if the
  # database is connectable. When we return a state of `false` it triggers the
  # create method where we can return an error message.
  #
  # @return [bool] did the test succeed?
  def exists?
    validator.attempt_connection(resource[:sleep], resource[:tries])
  end

  # This method is called when the exists? method returns false.
  #
  # @return [void]
  def create
    # If `#create` is called, that means that `#exists?` returned false, which
    # means that the connection could not be established... so we need to
    # cause a failure here.
    raise Puppet::Error, "Unable to connect to PostgreSQL server! (#{resource[:host]}:#{resource[:port]})"
  end

  # Returns the existing validator, if one exists otherwise creates a new object
  # from the class.
  #
  # @api private
  def validator
    @validator ||= Puppet::Util::PostgresqlValidator.new(resource)
  end

end

