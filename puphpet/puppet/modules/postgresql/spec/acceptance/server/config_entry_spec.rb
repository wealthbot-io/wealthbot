require 'spec_helper_acceptance'

describe 'postgresql::server::config_entry' do

  let(:pp_setup) { <<-EOS
    class { 'postgresql::server':
      postgresql_conf_path => '/tmp/postgresql.conf',
      }
    EOS
  }

  context 'unix_socket_directories' do
    let(:pp_test) { pp_setup + <<-EOS
      postgresql::server::config_entry { 'unix_socket_directories':
        value => '/var/socket/, /root/'
      }
      EOS
    }

    #get postgresql version
    apply_manifest("class { 'postgresql::server': }")
    result = shell('psql --version')
    version = result.stdout.match(%r{\s(\d\.\d)})[1]

    if version >= '9.3'
      it 'is expected to run idempotently' do
        apply_manifest(pp_test, :catch_failures => true)
        apply_manifest(pp_test, :catch_changes => true)
      end

      it 'is expected to contain directories' do
        shell('cat /tmp/postgresql.conf') do |output|
          expect(output.stdout).to contain("unix_socket_directories = '/var/socket/, /root/'")
        end
      end
    end
  end
end
