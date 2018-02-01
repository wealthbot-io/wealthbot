require 'spec_helper_acceptance'

describe 'elasticsearch 5.x', :if => v5x_capable? do
  describe 'basic installation', :with_cleanup do
    describe 'manifest' do
      pp = <<-EOS
        class { 'elasticsearch':
          config => {
            'node.name' => 'elasticsearch001',
            'cluster.name' => '#{test_settings['cluster_name']}',
            'network.host' => '0.0.0.0',
          },
          repo_version => '#{test_settings['repo_version5x']}',
          restart_on_change => true,
        }

        elasticsearch::instance { 'es-01':
          config => {
            'node.name' => 'elasticsearch001',
            'http.port' => '#{test_settings['port_a']}'
          }
        }
      EOS

      it 'applies cleanly' do
        apply_manifest pp, :catch_failures => true
      end
      it 'is idempotent' do
        apply_manifest pp, :catch_changes => true
      end
    end

    describe port(test_settings['port_a']) do
      it 'open', :with_retries do
        should be_listening
      end
    end

    describe server :container do
      describe http "http://localhost:#{test_settings['port_a']}" do
        it 'runs version 5', :with_retries do
          expect(
            JSON.parse(response.body)['version']['number']
          ).to start_with('5')
        end
      end
    end
  end
end
