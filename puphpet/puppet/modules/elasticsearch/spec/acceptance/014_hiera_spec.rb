require 'spec_helper_acceptance'
require 'json'

describe 'hiera' do
  let :base_manifest do
    <<-EOS
      class { 'elasticsearch':
        repo_version => '#{test_settings['repo_version']}',
        restart_on_change => true,
      }
    EOS
  end

  describe 'single instance' do
    describe 'manifest' do
      before(:all) { write_hiera_config(['singleinstance']) }

      it 'applies cleanly ' do
        apply_manifest base_manifest, :catch_failures => true
      end
      it 'is idempotent' do
        apply_manifest base_manifest, :catch_changes  => true
      end
    end

    describe service('elasticsearch-es-hiera-single') do
      it { should be_enabled }
      it { should be_running }
    end

    describe file('/etc/elasticsearch/es-hiera-single/elasticsearch.yml') do
      it { should be_file }
      it { should contain 'name: es-hiera-single' }
    end

    describe port(test_settings['port_a']) do
      it 'open', :with_retries do
        should be_listening
      end
    end

    describe server :container do
      describe http(
        "http://localhost:#{test_settings['port_a']}"
      ) do
        it 'serves requests' do
          expect(response.status).to eq(200)
        end
      end
    end
  end

  describe 'single instance with plugin' do
    describe 'manifest' do
      before(:all) { write_hiera_config(['singleplugin']) }

      it 'applies cleanly ' do
        apply_manifest base_manifest, :catch_failures => true
      end
      it 'is idempotent' do
        apply_manifest base_manifest, :catch_changes  => true
      end
    end

    describe file('/usr/share/elasticsearch/plugins/head/') do
      it { should be_directory }
    end

    describe port(test_settings['port_a']) do
      it 'open', :with_retries do
        should be_listening
      end
    end

    describe server :container do
      describe http(
        "http://localhost:#{test_settings['port_a']}/_cluster/stats"
      ) do
        it 'reports the plugin as installed', :with_retries do
          plugins = JSON.parse(response.body)['nodes']['plugins'].map do |h|
            h['name']
          end
          expect(plugins).to include('head')
        end
      end
    end
  end

  describe 'multiple instances' do
    describe 'manifest' do
      before(:all) { write_hiera_config(['multipleinstances']) }

      it 'applies cleanly ' do
        apply_manifest base_manifest, :catch_failures => true
      end
      it 'is idempotent' do
        apply_manifest base_manifest, :catch_changes  => true
      end
    end

    describe service('elasticsearch-es-hiera-multiple-1') do
      it { should be_enabled }
      it { should be_running }
    end

    describe service('elasticsearch-es-hiera-multiple-2') do
      it { should be_enabled }
      it { should be_running }
    end

    describe file('/etc/elasticsearch/es-hiera-multiple-1/elasticsearch.yml') do
      it { should be_file }
      it { should contain 'name: es-hiera-multiple-1' }
    end

    describe file('/etc/elasticsearch/es-hiera-multiple-2/elasticsearch.yml') do
      it { should be_file }
      it { should contain 'name: es-hiera-multiple-2' }
    end

    describe port(test_settings['port_a']) do
      it 'open', :with_retries do
        should be_listening
      end
    end

    describe server :container do
      describe http(
        "http://localhost:#{test_settings['port_a']}"
      ) do
        it 'serves requests' do
          expect(response.status).to eq(200)
        end
      end
    end

    describe port(test_settings['port_b']) do
      it 'open', :with_retries do
        should be_listening
      end
    end

    describe server :container do
      describe http(
        "http://localhost:#{test_settings['port_b']}"
      ) do
        it 'serves requests' do
          expect(response.status).to eq(200)
        end
      end
    end
  end

  after :all do
    write_hiera_config([])

    apply_manifest <<-EOS
      class { 'elasticsearch': ensure => 'absent' }
      elasticsearch::instance { 'es-hiera-single': }
      elasticsearch::instance { 'es-hiera-multiple-1': }
      elasticsearch::instance { 'es-hiera-multiple-2': }
      Elasticsearch::Instance { ensure => 'absent' }
    EOS
  end
end
