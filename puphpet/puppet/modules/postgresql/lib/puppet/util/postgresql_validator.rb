module Puppet
  module Util
    class PostgresqlValidator
      attr_reader :resource

      def initialize(resource)
        @resource = resource
      end

      def build_psql_cmd
        final_cmd = []

        cmd_init = "#{@resource[:psql_path]} --tuples-only --quiet --no-psqlrc"

        final_cmd.push cmd_init

        cmd_parts = {
          :host => "--host #{@resource[:host]}",
          :port => "--port #{@resource[:port]}",
          :db_username => "--username #{@resource[:db_username]}",
          :db_name => "--dbname #{@resource[:db_name]}",
          :command => "--command '#{@resource[:command]}'"
        }

        cmd_parts.each do |k,v|
          final_cmd.push v if @resource[k]
        end

        final_cmd.join ' '
      end

      def parse_connect_settings
        c_settings = @resource[:connect_settings] || {}
        c_settings.merge! ({ 'PGPASSWORD' => @resource[:db_password] }) if @resource[:db_password]
        return c_settings.map { |k,v| "#{k}=#{v}" }
      end

      def attempt_connection(sleep_length, tries)
        (0..tries-1).each do |try|
          Puppet.debug "PostgresqlValidator.attempt_connection: Attempting connection to #{@resource[:db_name]}"
          Puppet.debug "PostgresqlValidator.attempt_connection: #{build_validate_cmd}"
          result = execute_command
          if result && result.length > 0
            Puppet.debug "PostgresqlValidator.attempt_connection: Connection to #{@resource[:db_name] || parse_connect_settings.select { |elem| elem.match /PGDATABASE/ }} successful!"
            return true
          else
            Puppet.warning "PostgresqlValidator.attempt_connection: Sleeping for #{sleep_length} seconds"
            sleep sleep_length
          end
        end
        false
      end

      private

      def execute_command
        Execution.execute(build_validate_cmd, :uid => @resource[:run_as])
      end

      def build_validate_cmd
        "#{parse_connect_settings.join(' ')} #{build_psql_cmd} "
      end
    end
  end
end
