require 'spec_helper_acceptance'

describe 'postgresql::server::grant:', :unless => UNSUPPORTED_PLATFORMS.include?(fact('osfamily')) do

  let(:db) { 'grant_priv_test' }
  let(:owner) { 'psql_grant_priv_owner' }
  let(:user) { 'psql_grant_priv_tester' }
  let(:password) { 'psql_grant_role_pw' }
  let(:pp_install) { "class {'postgresql::server': }"}

  let(:pp_setup) { pp_setup = <<-EOS.unindent
    $db = #{db}
    $owner = #{owner}
    $user = #{user}
    $password = #{password}

    class { 'postgresql::server': }

    postgresql::server::role { $owner:
      password_hash => postgresql_password($owner, $password),
    }

    # Since we are not testing pg_hba or any of that, make a local user for ident auth
    user { $owner:
      ensure => present,
    }

    postgresql::server::database { $db:
      owner   => $owner,
      require => Postgresql::Server::Role[$owner],
    }

    # Create a user to grant privileges to
    postgresql::server::role { $user:
      db      => $db,
      require => Postgresql::Server::Database[$db],
    }

    # Make a local user for ident auth
    user { $user:
      ensure => present,
    }

    # Grant them connect to the database
    postgresql::server::database_grant { "allow connect for ${user}":
      privilege => 'CONNECT',
      db        => $db,
      role      => $user,
    }
    EOS
  }

  context 'LANGUAGE' do
    describe 'GRANT * ON LANGUAGE' do
      #testing grants on language requires a superuser
      let(:superuser) { 'postgres' }
      let(:pp_lang) { pp_setup + <<-EOS.unindent

          postgresql_psql { 'make sure plpgsql exists':
            command   => 'CREATE LANGUAGE plpgsql',
            db        => $db,
            psql_user => '#{superuser}',
            unless    => "SELECT 1 from pg_language where lanname = 'plpgsql'",
            require   => Postgresql::Server::Database[$db],
          }

          postgresql::server::grant { 'grant usage on plpgsql':
            psql_user     => '#{superuser}',
            privilege     => 'USAGE',
            object_type   => 'LANGUAGE',
            object_name   => 'plpgsql',
            role          => $user,
            db            => $db,
            require       => [ Postgresql_psql['make sure plpgsql exists'],
                               Postgresql::Server::Role[$user], ]
        }
        EOS
      }

        it 'is expected to run idempotently' do
          apply_manifest(pp_install)

          #postgres version
          result = shell('psql --version')
          version = result.stdout.match(%r{\s(\d\.\d)})[1]

          if version >= '8.4.0'
            apply_manifest(pp_lang, :catch_failures => true)
            apply_manifest(pp_lang, :catch_changes => true)
          end
        end

        it 'is expected to GRANT USAGE ON LANGUAGE plpgsql to ROLE' do
          result = shell('psql --version')
          version = result.stdout.match(%r{\s(\d\.\d)})[1]

          if version >= '8.4.0'
            ## Check that the privilege was granted to the user
            psql("-d #{db} --command=\"SELECT 1 WHERE has_language_privilege('#{user}', 'plpgsql', 'USAGE')\"", superuser) do |r|
              expect(r.stdout).to match(/\(1 row\)/)
              expect(r.stderr).to eq('')
            end
          end
        end

      let(:pp_onlyif) { pp_setup + <<-EOS.unindent
          postgresql::server::grant { 'grant usage on BSql':
            psql_user     => '#{superuser}',
            privilege     => 'USAGE',
            object_type   => 'LANGUAGE',
            object_name   => 'bsql',
            role          => $user,
            db            => $db,
            onlyif_exists => true,
        }
        EOS
      }

      #test onlyif_exists function
      it 'is expected to not GRANT USAGE ON (dummy)LANGUAGE BSql to ROLE' do
        apply_manifest(pp_install)

        #postgres version
        result = shell('psql --version')
        version = result.stdout.match(%r{\s(\d\.\d)})[1]

        if version >= '8.4.0'
          apply_manifest(pp_onlyif, :catch_failures => true)
          apply_manifest(pp_onlyif, :catch_changes => true)
        end
      end
    end
  end

  context 'sequence' do
    it 'should grant usage on a sequence to a user' do
      begin
        pp = pp_setup + <<-EOS.unindent

          postgresql_psql { 'create test sequence':
            command   => 'CREATE SEQUENCE test_seq',
            db        => $db,
            psql_user => $owner,
            unless    => "SELECT 1 FROM information_schema.sequences WHERE sequence_name = 'test_seq'",
            require   => Postgresql::Server::Database[$db],
          }

          postgresql::server::grant { 'grant usage on test_seq':
            privilege   => 'USAGE',
            object_type => 'SEQUENCE',
            object_name => 'test_seq',
            db          => $db,
            role        => $user,
            require     => [ Postgresql_psql['create test sequence'],
                             Postgresql::Server::Role[$user], ]
          }
        EOS

        apply_manifest(pp_install, :catch_failures => true)

        #postgres version
        result = shell('psql --version')
        version = result.stdout.match(%r{\s(\d\.\d)})[1]

        if version >= '9.0'
          apply_manifest(pp, :catch_failures => true)
          apply_manifest(pp, :catch_changes => true)

          ## Check that the privilege was granted to the user
          psql("-d #{db} --command=\"SELECT 1 WHERE has_sequence_privilege('#{user}', 'test_seq', 'USAGE')\"", user) do |r|
            expect(r.stdout).to match(/\(1 row\)/)
            expect(r.stderr).to eq('')
          end
        end
      end
    end

    it 'should grant update on a sequence to a user' do
      begin
        pp = pp_setup + <<-EOS.unindent

          postgresql_psql { 'create test sequence':
            command   => 'CREATE SEQUENCE test_seq',
            db        => $db,
            psql_user => $owner,
            unless    => "SELECT 1 FROM information_schema.sequences WHERE sequence_name = 'test_seq'",
            require   => Postgresql::Server::Database[$db],
          }

          postgresql::server::grant { 'grant update on test_seq':
            privilege   => 'UPDATE',
            object_type => 'SEQUENCE',
            object_name => 'test_seq',
            db          => $db,
            role        => $user,
            require     => [ Postgresql_psql['create test sequence'],
                             Postgresql::Server::Role[$user], ]
          }
        EOS

        apply_manifest(pp_install, :catch_failures => true)

        #postgres version
        result = shell('psql --version')
        version = result.stdout.match(%r{\s(\d\.\d)})[1]

        if version >= '9.0'
          apply_manifest(pp, :catch_failures => true)
          apply_manifest(pp, :catch_changes => true)

          ## Check that the privilege was granted to the user
          psql("-d #{db} --command=\"SELECT 1 WHERE has_sequence_privilege('#{user}', 'test_seq', 'UPDATE')\"", user) do |r|
            expect(r.stdout).to match(/\(1 row\)/)
            expect(r.stderr).to eq('')
          end
        end
      end
    end
  end

  context 'all sequences' do
    it 'should grant usage on all sequences to a user' do
      begin
        pp = pp_setup + <<-EOS.unindent

          postgresql_psql { 'create test sequences':
            command   => 'CREATE SEQUENCE test_seq2; CREATE SEQUENCE test_seq3;',
            db        => $db,
            psql_user => $owner,
            unless    => "SELECT 1 FROM information_schema.sequences WHERE sequence_name = 'test_seq2'",
            require   => Postgresql::Server::Database[$db],
          }

          postgresql::server::grant { 'grant usage on all sequences':
            privilege   => 'USAGE',
            object_type => 'ALL SEQUENCES IN SCHEMA',
            object_name => 'public',
            db          => $db,
            role        => $user,
            require     => [ Postgresql_psql['create test sequences'],
                             Postgresql::Server::Role[$user], ]
          }
        EOS

        apply_manifest(pp_install, :catch_failures => true)

        #postgres version
        result = shell('psql --version')
        version = result.stdout.match(%r{\s(\d\.\d)})[1]

        if version >= '9.0'
          apply_manifest(pp, :catch_failures => true)
          apply_manifest(pp, :catch_changes => true)

          ## Check that the privileges were granted to the user, this check is not available on version < 9.0
          psql("-d #{db} --command=\"SELECT 1 WHERE has_sequence_privilege('#{user}', 'test_seq2', 'USAGE') AND has_sequence_privilege('#{user}', 'test_seq3', 'USAGE')\"", user) do |r|
            expect(r.stdout).to match(/\(1 row\)/)
            expect(r.stderr).to eq('')
          end
        end
      end
    end

    it 'should grant update on all sequences to a user' do
      begin
        pp = pp_setup + <<-EOS.unindent

          postgresql_psql { 'create test sequences':
            command   => 'CREATE SEQUENCE test_seq2; CREATE SEQUENCE test_seq3;',
            db        => $db,
            psql_user => $owner,
            unless    => "SELECT 1 FROM information_schema.sequences WHERE sequence_name = 'test_seq2'",
            require   => Postgresql::Server::Database[$db],
          }

          postgresql::server::grant { 'grant usage on all sequences':
            privilege   => 'UPDATE',
            object_type => 'ALL SEQUENCES IN SCHEMA',
            object_name => 'public',
            db          => $db,
            role        => $user,
            require     => [ Postgresql_psql['create test sequences'],
                             Postgresql::Server::Role[$user], ]
          }
        EOS

        apply_manifest(pp_install, :catch_failures => true)

        #postgres version
        result = shell('psql --version')
        version = result.stdout.match(%r{\s(\d\.\d)})[1]

        if version >= '9.0'
          apply_manifest(pp, :catch_failures => true)
          apply_manifest(pp, :catch_changes => true)

          ## Check that the privileges were granted to the user
          psql("-d #{db} --command=\"SELECT 1 WHERE has_sequence_privilege('#{user}', 'test_seq2', 'UPDATE') AND has_sequence_privilege('#{user}', 'test_seq3', 'UPDATE')\"", user) do |r|
            expect(r.stdout).to match(/\(1 row\)/)
            expect(r.stderr).to eq('')
          end
        end
      end
    end
  end
end
