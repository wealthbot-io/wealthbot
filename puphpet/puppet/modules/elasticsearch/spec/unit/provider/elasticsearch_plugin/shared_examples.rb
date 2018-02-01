require 'spec_helper_rspec'

shared_examples 'plugin provider' do |version|
  describe "elasticsearch #{version}" do
    before(:each) do
      allow(File).to receive(:open)
      allow(provider).to receive(:es_version).and_return version
    end

    describe 'setup' do
      it 'installs with default parameters' do
        expect(provider).to receive(:plugin).with(
          ['install', resource_name].tap do |args|
            if Puppet::Util::Package.versioncmp(version, '2.2.0') >= 0
              args.insert 1, '--batch'
            end
          end
        )
        provider.create
      end

      it 'installs via URLs' do
        resource[:url] = 'http://url/to/my/plugin.zip'
        expect(provider).to receive(:plugin).with(
          ['install'] + ['http://url/to/my/plugin.zip'].tap do |args|
            args.unshift('kopf', '--url') if version.start_with? '1'

            if Puppet::Util::Package.versioncmp(version, '2.2.0') >= 0
              args.unshift '--batch'
            end

            args
          end
        )
        provider.create
      end

      it 'installs with a local file' do
        resource[:source] = '/tmp/plugin.zip'
        expect(provider).to receive(:plugin).with(
          ['install'] + ['file:///tmp/plugin.zip'].tap do |args|
            args.unshift('kopf', '--url') if version.start_with? '1'

            if Puppet::Util::Package.versioncmp(version, '2.2.0') >= 0
              args.unshift '--batch'
            end

            args
          end
        )
        provider.create
      end

      describe 'proxying' do
        it 'installs behind a proxy' do
          resource[:proxy] = 'http://localhost:3128'
          if version.start_with? '2'
            expect(provider)
              .to receive(:plugin)
              .with([
                '-Dhttp.proxyHost=localhost',
                '-Dhttp.proxyPort=3128',
                '-Dhttps.proxyHost=localhost',
                '-Dhttps.proxyPort=3128',
                'install',
                resource_name
              ])
            provider.create
          else
            expect(provider.with_environment do
              ENV['ES_JAVA_OPTS']
            end).to eq([
              '-Dhttp.proxyHost=localhost',
              '-Dhttp.proxyPort=3128',
              '-Dhttps.proxyHost=localhost',
              '-Dhttps.proxyPort=3128'
            ].join(' '))
          end
        end

        it 'uses authentication credentials' do
          resource[:proxy] = 'http://elastic:password@es.local:8080'
          if version.start_with? '2'
            expect(provider)
              .to receive(:plugin)
              .with([
                '-Dhttp.proxyHost=es.local',
                '-Dhttp.proxyPort=8080',
                '-Dhttp.proxyUser=elastic',
                '-Dhttp.proxyPassword=password',
                '-Dhttps.proxyHost=es.local',
                '-Dhttps.proxyPort=8080',
                '-Dhttps.proxyUser=elastic',
                '-Dhttps.proxyPassword=password',
                'install',
                resource_name
              ])
            provider.create
          else
            expect(provider.with_environment do
              ENV['ES_JAVA_OPTS']
            end).to eq([
              '-Dhttp.proxyHost=es.local',
              '-Dhttp.proxyPort=8080',
              '-Dhttp.proxyUser=elastic',
              '-Dhttp.proxyPassword=password',
              '-Dhttps.proxyHost=es.local',
              '-Dhttps.proxyPort=8080',
              '-Dhttps.proxyUser=elastic',
              '-Dhttps.proxyPassword=password'
            ].join(' '))
          end
        end
      end

      describe 'configdir' do
        it 'sets the ES_PATH_CONF env var' do
          resource[:configdir] = '/etc/elasticsearch'
          expect(provider.with_environment do
            ENV['ES_PATH_CONF']
          end).to eq('/etc/elasticsearch')
        end
      end
    end # of setup

    describe 'java_opts' do
      it 'uses authentication credentials' do
        resource[:java_opts] = ['-Des.plugins.staging=4a2ffaf5']
        expect(provider.with_environment do
          ENV['ES_JAVA_OPTS']
        end).to eq('-Des.plugins.staging=4a2ffaf5')
      end
    end

    describe 'java_home' do
      it 'sets the JAVA_HOME env var' do
        resource[:java_home] = '/opt/foo'
        expect(provider.with_environment do
          ENV['JAVA_HOME']
        end).to eq('/opt/foo')
      end
    end

    describe 'java_home unset' do
      existing_java_home = ENV['JAVA_HOME']
      it 'does not change JAVA_HOME env var' do
        resource[:java_home] = ''
        expect(provider.with_environment do
          ENV['JAVA_HOME']
        end).to eq(existing_java_home)
      end
    end

    describe 'plugin_name' do
      let(:resource_name) { 'appbaseio/dejaVu' }

      it 'maintains mixed-case names' do
        expect(provider.pluginfile).to include('dejaVu')
      end
    end

    describe 'removal' do
      it 'uninstalls the plugin' do
        expect(provider).to receive(:plugin).with(
          ['remove', resource_name.split('-').last]
        )
        provider.destroy
      end
    end
  end
end
