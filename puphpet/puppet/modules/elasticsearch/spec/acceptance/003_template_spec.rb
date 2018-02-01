require 'spec_helper_acceptance'
require 'json'

describe 'elasticsearch::template', :with_cleanup do
  before :all do
    shell "mkdir -p #{default['distmoduledir']}/another/files"

    create_remote_file default,
                       "#{default['distmoduledir']}/another/files/good.json",
                       JSON.dump(test_settings['template'])

    create_remote_file default,
                       "#{default['distmoduledir']}/another/files/bad.json",
                       JSON.dump(test_settings['template'])[0..-5]
  end

  describe 'valid json template' do
    context 'from source', :with_cleanup do
      it 'should run successfully' do
        pp = <<-EOS
          class { 'elasticsearch':
            config => {
              'node.name' => 'elasticsearch001',
              'cluster.name' => '#{test_settings['cluster_name']}',
              'network.host' => '0.0.0.0',
            },
            repo_version => '#{test_settings['repo_version']}',
          }

          elasticsearch::instance { 'es-01':
            config => {
              'node.name' => 'elasticsearch001',
              'http.port' => '#{test_settings['port_a']}'
            }
          }

          elasticsearch::template { 'foo':
            ensure => 'present',
            source => 'puppet:///modules/another/good.json'
          }
        EOS

        # Run it twice and test for idempotency
        apply_manifest pp, :catch_failures => true
        apply_manifest pp, :catch_changes => true
      end

      describe port(test_settings['port_a']) do
        it 'open', :with_retries do
          should be_listening
        end
      end

      describe server :container do
        describe http(
          "http://localhost:#{test_settings['port_a']}/_template/foo",
          :params => { 'flat_settings' => 'false' }
        ) do
          it 'returns the installed template', :with_retries do
            expect(JSON.parse(response.body)['foo'])
              .to include(test_settings['template'])
          end
        end
      end
    end

    describe 'from content' do
      it 'should run successfully' do
        pp = <<-EOS
          class { 'elasticsearch':
            config => {
              'node.name' => 'elasticsearch001',
              'cluster.name' => '#{test_settings['cluster_name']}',
              'network.host' => '0.0.0.0',
            },
            repo_version => '#{test_settings['repo_version']}',
          }

          elasticsearch::instance { 'es-01':
            config => {
              'node.name' => 'elasticsearch001',
              'http.port' => '#{test_settings['port_a']}'
            }
          }

          elasticsearch::template { 'foo':
            ensure => 'present',
            content => '#{JSON.dump(test_settings['template'])}'
          }
        EOS

        # Run it twice and test for idempotency
        apply_manifest pp, :catch_failures => true
        apply_manifest pp, :catch_changes => true
      end

      describe port(test_settings['port_a']) do
        it 'open', :with_retries do
          should be_listening
        end
      end

      describe server :container do
        describe http(
          "http://localhost:#{test_settings['port_a']}/_template/foo",
          :params => { 'flat_settings' => 'false' }
        ) do
          it 'returns the installed template', :with_retries do
            expect(JSON.parse(response.body)['foo'])
              .to include(test_settings['template'])
          end
        end
      end
    end
  end

  describe 'invalid json template' do
    it 'should fail to apply cleanly' do
      pp = <<-EOS
        class { 'elasticsearch':
          config => {
            'node.name' => 'elasticsearch001',
            'cluster.name' => '#{test_settings['cluster_name']}',
            'network.host' => '0.0.0.0',
          },
          repo_version => '#{test_settings['repo_version']}',
        }

        elasticsearch::instance { 'es-01':
          config => {
            'node.name' => 'elasticsearch001',
            'http.port' => '#{test_settings['port_a']}'
          }
        }

        elasticsearch::template { 'foo':
          ensure => 'present',
          file => 'puppet:///modules/another/bad.json'
        }
      EOS

      apply_manifest pp, :expect_failures => true
    end
  end
end
