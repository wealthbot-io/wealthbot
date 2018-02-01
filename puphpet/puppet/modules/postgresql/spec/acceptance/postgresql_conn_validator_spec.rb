require 'spec_helper_acceptance'

describe 'postgresql_conn_validator', :unless => UNSUPPORTED_PLATFORMS.include?(fact('osfamily')) do

  let(:install_pp) { <<-EOS
    class { 'postgresql::server':
      postgres_password => 'space password',
    }->
    postgresql::server::role { 'testuser':
      password_hash => postgresql_password('testuser','test1'),
    }->
    postgresql::server::database { 'testdb':
      owner   => 'testuser',
      require => Postgresql::Server::Role['testuser']
    }->
    postgresql::server::database_grant { 'allow connect for testuser':
      privilege => 'ALL',
      db        => 'testdb',
      role      => 'testuser',
    }

  EOS

  }

  context 'local connection' do
    it 'validates successfully with defaults' do
      pp = <<-EOS
        #{install_pp}->
        postgresql_conn_validator { 'validate this':
          db_name     => 'testdb',
          db_username => 'testuser',
          db_password => 'test1',
          host        => 'localhost',
          psql_path   => '/usr/bin/psql',
        }
    EOS

      apply_manifest(pp, :catch_failures => true)
      apply_manifest(pp, :catch_changes => true)
    end

    it 'works with connect settings hash' do
      pp = <<-EOS
        #{install_pp}->
        postgresql_conn_validator { 'validate this':
          connect_settings => {
            'PGDATABASE' => 'testdb',
            'PGPORT'     => '5432',
            'PGUSER'     => 'testuser',
            'PGPASSWORD' => 'test1',
            'PGHOST'     => 'localhost'
          },
          psql_path => '/usr/bin/psql'
        }
     EOS

      apply_manifest(pp, :catch_failures => true)
      apply_manifest(pp, :catch_changes => true)

    end

    it 'fails gracefully' do
      pp = <<-EOS
        #{install_pp}->
        postgresql_conn_validator { 'validate this':
          psql_path => '/usr/bin/psql',
          tries     => 3
        }
     EOS

      result = apply_manifest(pp)
      expect(result.stderr).to match /Unable to connect to PostgreSQL server/
    end
  end
end
