require 'spec_helper'

# rubocop:disable Metrics/LineLength
describe 'elasticsearch::instance', :type => 'define' do
  let(:title) { 'es-instance' }
  let(:pre_condition) { 'class { "elasticsearch": }' }

  on_supported_os.each do |os, facts|
    context "on #{os}" do
      shared_examples 'systemd' do
        it { should contain_elasticsearch__service__systemd(title) }
        it { should contain_elasticsearch_service_file("#{systemd_service_path}/elasticsearch-#{title}.service") }
        it { should contain_file("#{systemd_service_path}/elasticsearch-#{title}.service") }
        it { should contain_exec("systemd_reload_#{title}") }
      end

      shared_examples 'init' do
        it { should contain_elasticsearch__service__init(title) }
        it { should contain_elasticsearch_service_file("/etc/init.d/elasticsearch-#{title}") }
        it { should contain_file("/etc/init.d/elasticsearch-#{title}") }
      end

      if (facts[:os]['name'] == 'OpenSuSE' and facts[:os]['release']['major'].to_i >= 13) or facts[:os]['name'] == 'SLES'
        let(:systemd_service_path) { '/usr/lib/systemd/system' }
      else
        let(:systemd_service_path) { '/lib/systemd/system' }
      end

      case facts[:os]['family']
      when 'Debian'
        let(:defaults_path) { '/etc/default' }
        let(:pkg_ext) { 'deb' }
        let(:pkg_prov) { 'dpkg' }
        case facts[:os]['name']
        when 'Debian'
          if facts[:os]['release']['major'].to_i >= 8
            let(:initscript) { 'systemd' }

            include_examples 'systemd'
          else
            let(:initscript) { 'Debian' }

            include_examples 'init'
          end
        when 'Ubuntu'
          if facts[:os]['release']['major'].to_i >= 15
            let(:initscript) { 'systemd' }

            include_examples 'systemd'
          else
            let(:initscript) { 'Debian' }

            include_examples 'init'
          end
        end
      when 'RedHat'
        let(:defaults_path) { '/etc/sysconfig' }
        let(:pkg_ext) { 'rpm' }
        let(:pkg_prov) { 'rpm' }
        if facts[:os]['release']['major'].to_i >= 7
          let(:initscript) { 'systemd' }

          include_examples 'systemd'
        else
          let(:initscript) { 'RedHat' }

          include_examples 'init'
        end
      when 'Suse'
        let(:defaults_path) { '/etc/sysconfig' }
        let(:pkg_ext) { 'rpm' }
        let(:pkg_prov) { 'rpm' }
        let(:initscript) { 'systemd' }

        include_examples 'systemd'
      end

      let(:facts) do
        facts.merge('scenario' => '', 'common' => '')
      end

      it { should contain_elasticsearch__service(
        'es-instance'
      ).with(
        :init_template =>
          "elasticsearch/etc/init.d/elasticsearch.#{initscript}.erb",
        :init_defaults => {
          'CONF_DIR'       => '/etc/elasticsearch/es-instance',
          'ES_PATH_CONF'   => '/etc/elasticsearch/es-instance',
          'DATA_DIR'       => '/var/lib/elasticsearch',
          'ES_JVM_OPTIONS' => '/etc/elasticsearch/es-instance/jvm.options',
          'LOG_DIR'        => '/var/log/elasticsearch/es-instance',
          'ES_HOME'        => '/usr/share/elasticsearch'
        }
      )}
    end # of on os context
  end # of on supported OSes loop

  # Test all non OS-specific functionality with just a single distro
  on_supported_os(
    :hardwaremodels => ['x86_64'],
    :supported_os => [
      {
        'operatingsystem' => 'CentOS',
        'operatingsystemrelease' => ['6']
      }
    ]
  ).each do |os, facts|
    context "on #{os}" do
      let(:facts) { facts.merge(
        :scenario => '',
        :common => ''
      ) }

      let(:params) do
        { :config => { 'node' => { 'name' => 'test' } } }
      end

      describe 'config file' do
        it { should contain_augeas('defaults_es-instance') }
        it { should contain_datacat_fragment('main_config_es-instance') }
        it { should contain_datacat('/etc/elasticsearch/es-instance/elasticsearch.yml') }
        it { should contain_datacat_collector(
          '/etc/elasticsearch/es-instance/elasticsearch.yml'
        ) }
        it { should contain_file('/etc/elasticsearch/es-instance/elasticsearch.yml') }
      end

      describe 'service restarts' do
        context 'do not happen when restart_on_change is false (default)' do
          it { should_not contain_datacat(
            '/etc/elasticsearch/es-instance/elasticsearch.yml'
          ).that_notifies('Elasticsearch::Service[es-instance]') }
          it { should_not contain_file(
            '/etc/elasticsearch/es-instance/jvm.options'
          ).that_notifies('Elasticsearch::Service[es-instance]') }
          it { should_not contain_package(
            'elasticsearch'
          ).that_notifies('Elasticsearch::Service[es-instance]') }
        end

        context 'happen when restart_on_change is true' do
          let(:pre_condition) do
            'class { "elasticsearch": restart_on_change => true }'
          end

          it { should contain_datacat(
            '/etc/elasticsearch/es-instance/elasticsearch.yml'
          ).that_notifies('Elasticsearch::Service[es-instance]') }
          it { should contain_file(
            '/etc/elasticsearch/es-instance/jvm.options'
          ).that_notifies('Elasticsearch::Service[es-instance]') }
          it { should contain_package(
            'elasticsearch'
          ).that_notifies('Elasticsearch::Service[es-instance]') }
        end

        context 'on package change' do
          let(:pre_condition) do
            'class { "elasticsearch": restart_package_change => true }'
          end

          it { should_not contain_datacat(
            '/etc/elasticsearch/es-instance/elasticsearch.yml'
          ).that_notifies('Elasticsearch::Service[es-instance]') }
          it { should contain_package(
            'elasticsearch'
          ).that_notifies('Elasticsearch::Service[es-instance]') }
        end

        context 'on config change' do
          let(:pre_condition) do
            'class { "elasticsearch": restart_config_change => true }'
          end

          it { should contain_datacat(
            '/etc/elasticsearch/es-instance/elasticsearch.yml'
          ).that_notifies('Elasticsearch::Service[es-instance]') }
          it { should contain_file(
          '/etc/elasticsearch/es-instance/jvm.options'
          ).that_notifies('Elasticsearch::Service[es-instance]') }
          it { should_not contain_package(
            'elasticsearch'
          ).that_notifies('Elasticsearch::Service[es-instance]') }
        end
      end

      context 'config dir' do
        context 'default' do
          it { should contain_exec('mkdir_configdir_elasticsearch_es-instance') }
          it { should contain_file('/etc/elasticsearch/es-instance').with(:ensure => 'directory') }
          it { should contain_datacat_fragment('main_config_es-instance') }
          it { should contain_datacat('/etc/elasticsearch/es-instance/elasticsearch.yml') }

          it { should contain_file('/etc/elasticsearch/es-instance/logging.yml') }
          it { should contain_file('/etc/elasticsearch/es-instance/log4j2.properties') }
          it { should contain_file('/etc/elasticsearch/es-instance/jvm.options') }
          it { should contain_file('/usr/share/elasticsearch/scripts') }
          it { should contain_file('/etc/elasticsearch/es-instance/scripts').with(:target => '/usr/share/elasticsearch/scripts') }
        end

        context 'set in main class' do
          let(:pre_condition) { <<-EOS
            class { "elasticsearch":
              configdir => "/etc/elasticsearch-config"
            }
          EOS
          }

          it { should contain_exec('mkdir_configdir_elasticsearch_es-instance') }
          it { should contain_file('/etc/elasticsearch-config').with(:ensure => 'directory') }
          it { should contain_file('/usr/share/elasticsearch/templates_import').with(:ensure => 'directory') }
          it { should contain_file('/etc/elasticsearch-config/es-instance').with(:ensure => 'directory') }
          it { should contain_datacat_fragment('main_config_es-instance') }
          it { should contain_datacat('/etc/elasticsearch-config/es-instance/elasticsearch.yml') }

          it { should contain_file('/etc/elasticsearch-config/es-instance/jvm.options') }
          it { should contain_file('/etc/elasticsearch-config/es-instance/logging.yml') }
          it { should contain_file('/etc/elasticsearch-config/es-instance/log4j2.properties') }
          it { should contain_file('/usr/share/elasticsearch/scripts') }
          it { should contain_file('/etc/elasticsearch-config/es-instance/scripts').with(:target => '/usr/share/elasticsearch/scripts') }
        end

        context 'set in instance' do
          let(:params) do {
            :configdir => '/etc/elasticsearch-config/es-instance'
          } end

          it { should contain_exec('mkdir_configdir_elasticsearch_es-instance') }
          it { should contain_file('/etc/elasticsearch').with(:ensure => 'directory') }
          it { should contain_file('/etc/elasticsearch-config/es-instance').with(:ensure => 'directory') }
          it { should contain_datacat_fragment('main_config_es-instance') }
          it { should contain_datacat('/etc/elasticsearch-config/es-instance/elasticsearch.yml') }

          it { should contain_file('/etc/elasticsearch-config/es-instance/jvm.options') }
          it { should contain_file('/etc/elasticsearch-config/es-instance/logging.yml') }
          it { should contain_file('/etc/elasticsearch-config/es-instance/log4j2.properties') }
          it { should contain_file('/usr/share/elasticsearch/scripts') }
          it { should contain_file('/etc/elasticsearch-config/es-instance/scripts').with(:target => '/usr/share/elasticsearch/scripts') }
        end
      end

      context 'data directory' do
        shared_examples 'data directories' do |data_dirs|
          data_dirs.each do |dir|
            it { should contain_exec('mkdir_logdir_elasticsearch_es-instance') }
            it { should contain_exec('mkdir_datadir_elasticsearch_es-instance') }
            it { should contain_file("/var/lib/#{dir}").with(:ensure => 'directory') }
          end
        end

        context 'default' do
          include_examples 'data directories', ['elasticsearch']
        end

        context 'datadir_instance_directories' do
          let(:pre_condition) do
            <<-EOS
              class { "elasticsearch":
                datadir_instance_directories => false
              }
            EOS
          end

          it { should contain_exec('mkdir_logdir_elasticsearch_es-instance') }
          it { should_not contain_exec('mkdir_datadir_elasticsearch_es-instance') }
          it { should_not contain_file('/var/lib/elasticsearch/es-instance').with(:ensure => 'directory') }
          it { should contain_file('/var/lib/elasticsearch').with(:ensure => 'directory') }
        end

        context 'single from main config ' do
          let(:pre_condition) { <<-EOS
            class { "elasticsearch":
              datadir => "/var/lib/elasticsearch-data"
            }
          EOS
          }

          include_examples 'data directories',
                          ['elasticsearch-data', 'elasticsearch-data/es-instance']
        end

        context 'single from instance config' do
          let(:params) do {
            :datadir => '/var/lib/elasticsearch/data'
          } end

          include_examples 'data directories', ['elasticsearch/data']
        end

        context 'multiple from main config' do
          let(:pre_condition) { <<-EOS
            class { "elasticsearch":
              datadir => [
                "/var/lib/elasticsearch-data01",
                "/var/lib/elasticsearch-data02"
              ]
            }
          EOS
          }

          include_examples(
            'data directories',
            (1..2).map do |n|
              dir = "elasticsearch-data#{n.to_s.rjust(2, '0')}"
              [dir, "#{dir}/es-instance"]
            end.flatten
          )
        end

        context 'multiple from instance config' do
          let(:params) do {
            :datadir => [
              '/var/lib/elasticsearch-data/01',
              '/var/lib/elasticsearch-data/02'
            ]
          } end

          include_examples(
            'data directories',
            (1..2).map { |n| "elasticsearch-data/#{n.to_s.rjust(2, '0')}" }
          )
        end

        context 'conflicting setting path.data' do
          let(:params) do {
            :datadir => '/var/lib/elasticsearch/data',
            :config  => { 'path.data' => '/var/lib/elasticsearch/otherdata' }
          } end

          include_examples 'data directories', ['elasticsearch/data']
          it { should_not contain_file('/var/lib/elasticsearch/otherdata').with(:ensure => 'directory') }
        end

        context 'conflicting setting path => data' do
          let(:params) do {
            :datadir => '/var/lib/elasticsearch/data',
            :config  => {
              'path' => { 'data' => '/var/lib/elasticsearch/otherdata' }
            }
          } end

          include_examples 'data directories', ['elasticsearch/data']
          it { should_not contain_file('/var/lib/elasticsearch/otherdata').with(:ensure => 'directory') }
        end

        context 'with other path options defined' do
          let(:params) do {
            :datadir => '/var/lib/elasticsearch/data',
            :config  => { 'path' => { 'home' => '/var/lib/elasticsearch' } }
          } end

          include_examples 'data directories', ['elasticsearch/data']
        end
      end

      context 'logs directory' do
        context 'default' do
          it { should contain_file('/var/log/elasticsearch/es-instance')
            .with(:ensure => 'directory') }
          it { should contain_file('/var/log/elasticsearch')
            .with(:ensure => 'directory') }
        end

        context 'single from main config ' do
          let(:pre_condition) { <<-EOS
            class { "elasticsearch":
              logdir => "/var/log/elasticsearch-logs"
            }
          EOS
          }

          it { should contain_file('/var/log/elasticsearch-logs')
            .with(:ensure => 'directory') }
          it { should contain_file('/var/log/elasticsearch-logs/es-instance')
            .with(:ensure => 'directory') }
        end

        context 'single from instance config' do
          let(:params) do {
            :logdir => '/var/log/elasticsearch/logs-a'
          } end

          it { should contain_file('/var/log/elasticsearch/logs-a').with(:ensure => 'directory') }
        end

        context 'Conflicting setting path.logs' do
          let(:params) do {
            :logdir => '/var/log/elasticsearch/logs-a',
            :config => { 'path.logs' => '/var/log/elasticsearch/otherlogs' }
          } end

          it { should contain_file('/var/log/elasticsearch/logs-a')
            .with(:ensure => 'directory') }
          it { should_not contain_file('/var/log/elasticsearch/otherlogs')
            .with(:ensure => 'directory') }
        end

        context 'Conflicting setting path => logs' do
          let(:params) do {
            :logdir => '/var/log/elasticsearch/logs-a',
            :config => { 'path' => { 'logs' => '/var/log/elasticsearch/otherlogs' } }
          } end

          it { should contain_file('/var/log/elasticsearch/logs-a')
            .with(:ensure => 'directory') }
          it { should_not contain_file('/var/log/elasticsearch/otherlogs')
            .with(:ensure => 'directory') }
        end

        context 'With other path options defined' do
          let(:params) do {
            :logdir => '/var/log/elasticsearch/logs-a',
            :config => { 'path' => { 'home' => '/var/log/elasticsearch' } }
          } end

          it { should contain_file('/var/log/elasticsearch/logs-a').with(:ensure => 'directory') }
        end
      end

      context 'logging' do
        context 'default' do
          it { should contain_file('/etc/elasticsearch/es-instance/logging.yml')
            .with_content(
              /^logger.index.search.slowlog: TRACE, index_search_slow_log_file$/,
              /type: dailyRollingFile/,
              /datePattern: "'.'yyyy-MM-dd"/
            ).with(:source => nil)
          }
        end

        context 'from main class' do
          context 'config' do
            let(:pre_condition) { <<-EOS
              class { "elasticsearch":
                logging_config => {
                  "index.search.slowlog" => "DEBUG, index_search_slow_log_file"
                }
              }
            EOS
            }

            it 'writes correct yaml' do
              should contain_file('/etc/elasticsearch/es-instance/logging.yml')
                .with_content(
                  /^logger.index.search.slowlog: DEBUG, index_search_slow_log_file$/
                ).with(:source => nil)
            end
          end

          context 'logging file ' do
            let(:pre_condition) { <<-EOS
              class { "elasticsearch":
                logging_file => "puppet:///path/to/logging.yml"
              }
            EOS
            }

            it 'sets the right source' do
              should contain_file('/etc/elasticsearch/es-instance/logging.yml')
                .with(
                  :source => 'puppet:///path/to/logging.yml',
                  :content => nil
                )
            end
          end
        end

        context 'from instance' do
          context 'config' do
            let(:params) do {
              :logging_config => {
                'index.search.slowlog' => 'INFO, index_search_slow_log_file'
              }
            } end

            it 'writes correct yaml' do
              should contain_file('/etc/elasticsearch/es-instance/logging.yml')
                .with_content(/^logger.index.search.slowlog: INFO, index_search_slow_log_file$/)
                .with(:source => nil)
            end
          end

          context 'logging file' do
            let(:params) do {
              :logging_file => 'puppet:///path/to/logging.yml'
            } end

            it 'sets the right source' do
              should contain_file('/etc/elasticsearch/es-instance/logging.yml')
                .with(
                  :source => 'puppet:///path/to/logging.yml',
                  :content => nil
                )
            end
          end

          context 'deprecation logging' do
            let(:params) do {
              :deprecation_logging => true
            } end

            it 'writes correct yaml' do
              should contain_file('/etc/elasticsearch/es-instance/logging.yml')
                .with_content(/^logger.deprecation: DEBUG, deprecation_log_file$/)
                .with(:source => nil)
            end
            it 'configures the deprecation log' do
              should contain_file('/etc/elasticsearch/es-instance/logging.yml')
                .with_content(
                  /deprecation_log_file:$/,
                  /type: dailyRollingFile$/,
                  %r(file: ${path.logs}/${cluster.name}_deprecation.log$),
                  /datePattern: "'.'yyyy-MM-dd"$/,
                  /layout:$/,
                  /type: pattern$/,
                  /conversionPattern: "[%d{ISO8601}][%-5p][%-25c] %m%n"$/
                ).with(:source => nil)
            end
          end

          context 'deprecation logging level' do
            let(:params) do {
              :deprecation_logging => true,
              :deprecation_logging_level => 'INFO'
            } end

            it 'writes correct yaml' do
              should contain_file('/etc/elasticsearch/es-instance/logging.yml')
                .with_content(/^logger.deprecation: INFO, deprecation_log_file$/)
                .with(:source => nil)
            end
          end
        end

        describe 'rollingFile apender' do
          let(:pre_condition) do
            %(
              class { 'elasticsearch':
                file_rolling_type             => 'rollingFile',
                rolling_file_max_backup_index => 10,
                rolling_file_max_file_size    => '100MB',
              }
            )
          end

          it { should contain_file('/etc/elasticsearch/es-instance/logging.yml')
            .with_content(
              /type: rollingFile/,
              /maxBackupIndex: 10/,
              /maxBackupIndex: 10/,
              /maxFileSize: 100MB/
            )
          }
        end
      end

      shared_examples 'file permissions' do |owner, group|
        it { should contain_file('/var/lib/elasticsearch/es-instance')
          .with(:owner => owner) }
        it { should contain_file('/etc/elasticsearch/es-instance')
          .with(
            :owner => owner,
            :group => group,
            :mode  => '0755'
          ) }
        it { should contain_datacat('/etc/elasticsearch/es-instance/elasticsearch.yml')
          .with(
            :owner => owner,
            :group => group,
            :mode  => '0440'
          ) }
        it { should contain_file('/etc/elasticsearch/es-instance/elasticsearch.yml')
          .with(
            :owner => owner,
            :group => group,
            :mode  => '0440'
          ) }
        it { should contain_file('/etc/elasticsearch/es-instance/logging.yml')
          .with(
            :owner => owner,
            :group => group,
            :mode  => '0644'
          ) }
        it { should contain_file('/etc/elasticsearch/es-instance/log4j2.properties')
          .with(
            :owner => owner,
            :group => group,
            :mode  => '0644'
          ) }

        it { should contain_file('/var/lib/elasticsearch/es-instance')
          .with(
            :owner => owner,
            :group => nil,
            :mode  => '0755'
          ) }
        it { should contain_file('/var/log/elasticsearch/es-instance')
          .with(
            :owner => owner,
            :group => nil,
            :mode  => '0755'
          ) }
      end

      describe 'default file permissions' do
        let(:pre_condition) { ' class { "elasticsearch":} ' }

        include_examples 'file permissions', 'elasticsearch', 'elasticsearch'
      end

      context 'running as an other user' do
        let(:pre_condition) { <<-EOS
          class { "elasticsearch":
            elasticsearch_user  => "myesuser",
            elasticsearch_group => "myesgroup"
          }
        EOS
        }

        include_examples 'file permissions', 'myesuser', 'myesgroup'
      end

      context 'setting different service status then main class' do
        let(:pre_condition) { 'class {"elasticsearch": status => "enabled" }' }

        context 'status option' do
          let(:params) do {
            :status => 'running'
          } end

          it { should contain_service('elasticsearch-instance-es-instance').with(:ensure => 'running', :enable => false) }
        end
      end

      context 'init_template' do
        context 'default' do
          it { should contain_elasticsearch__service('es-instance')
            .with(:init_template => 'elasticsearch/etc/init.d/elasticsearch.RedHat.erb') }
        end

        context 'override in main class' do
          let(:pre_condition) { <<-EOS
            class { "elasticsearch":
              init_template => "elasticsearch/etc/init.d/elasticsearch.systemd.erb"
            }
          EOS
          }

          it { should contain_elasticsearch__service('es-instance')
            .with(:init_template => 'elasticsearch/etc/init.d/elasticsearch.systemd.erb') }
        end
      end

      describe 'security plugins' do
        describe 'system_key' do
          context 'inherited' do
            let(:pre_condition) do
              %(
                class { 'elasticsearch':
                  security_plugin => 'shield',
                  system_key => '/tmp/key'
                }
              )
            end

            it { should contain_file('/etc/elasticsearch/es-instance/shield') }
            it { should contain_file(
              '/etc/elasticsearch/es-instance/shield/system_key'
            ).with(
              :source => '/tmp/key',
              :mode => '0400',
              :owner => 'elasticsearch'
            ) }
          end

          context 'from instance' do
            let(:pre_condition) { "class { 'elasticsearch': security_plugin => 'x-pack' }" }

            let(:params) do {
              :system_key => 'puppet:///test/key'
            } end

            it { should contain_file('/etc/elasticsearch/es-instance/x-pack') }
            it { should contain_file(
              '/etc/elasticsearch/es-instance/x-pack/system_key'
            ).with(
              :source => 'puppet:///test/key',
              :mode => '0400',
              :owner => 'elasticsearch'
            ) }
          end
        end
      end

      describe 'recursive configuration directory management' do
        ['shield', 'x-pack'].each do |plugin|
          context 'shield' do
            context 'without resource notifications' do
              let(:pre_condition) do
                %(
                class { 'elasticsearch':
                  security_plugin => '#{plugin}',
                }
              )
              end

              it "copies the #{plugin} directory from the source" do
                should(
                  contain_file(
                    "/etc/elasticsearch/es-instance/#{plugin}"
                  ).with(
                    :ensure  => 'directory',
                    :mode    => '0755',
                    :source  => "/etc/elasticsearch/#{plugin}",
                    :recurse => 'remote',
                    :owner   => 'root',
                    :group   => '0',
                    :before  => 'Elasticsearch::Service[es-instance]'
                  )
                )
              end
            end

            context 'with resource notifications' do
              let(:pre_condition) do
                %(
                class { 'elasticsearch':
                  security_plugin => '#{plugin}',
                  restart_on_change => true,
                }
              )
              end

              it "copies the #{plugin} directory from the source" do
                should(
                  contain_file(
                    "/etc/elasticsearch/es-instance/#{plugin}"
                  ).with(
                    :ensure  => 'directory',
                    :mode    => '0755',
                    :source  => "/etc/elasticsearch/#{plugin}",
                    :recurse => 'remote',
                    :owner   => 'root',
                    :group   => '0',
                    :before  => 'Elasticsearch::Service[es-instance]',
                    :notify  => 'Elasticsearch::Service[es-instance]'
                  )
                )
              end
            end
          end
        end
      end

      describe 'jvm.options' do
        let(:pre_condition) do
          %(
              class { 'elasticsearch':
                jvm_options => [
                  '-Xms4g',
                  '-Xmx4g'
                ]
              }
          )
        end

        context 'from parent class' do
          it do
            should contain_file('/etc/elasticsearch/es-instance/jvm.options')
              .with_content(/
                -Dfile.encoding=UTF-8.
                -Dio.netty.noKeySetOptimization=true.
                -Dio.netty.noUnsafe=true.
                -Dio.netty.recycler.maxCapacityPerThread=0.
                -Djava.awt.headless=true.
                -Djna.nosys=true.
                -Dlog4j.shutdownHookEnabled=false.
                -Dlog4j2.disable.jmx=true.
                -XX:\+AlwaysPreTouch.
                -XX:\+HeapDumpOnOutOfMemoryError.
                -XX:\+PrintGCApplicationStoppedTime.
                -XX:\+PrintGCDateStamps.
                -XX:\+PrintGCDetails.
                -XX:\+PrintTenuringDistribution.
                -XX:\+UseCMSInitiatingOccupancyOnly.
                -XX:\+UseConcMarkSweepGC.
                -XX:\+UseGCLogFileRotation.
                -XX:-OmitStackTraceInFastThrow.
                -XX:CMSInitiatingOccupancyFraction=75.
                -XX:GCLogFileSize=64m.
                -XX:NumberOfGCLogFiles=32.
                -Xloggc:\/var\/log\/elasticsearch\/es-instance\/gc.log.
                -Xms4g.
                -Xmx4g.
                -Xss1m.
                -server.
              /xm)
          end
        end

        context 'from instance' do
          let(:params) do
            {
              :jvm_options => [
                '-Xms8g',
                '-Xmx8g'
              ]
            }
          end

          it do
            should contain_file('/etc/elasticsearch/es-instance/jvm.options')
              .with_content(/
                -Dfile.encoding=UTF-8.
                -Dio.netty.noKeySetOptimization=true.
                -Dio.netty.noUnsafe=true.
                -Dio.netty.recycler.maxCapacityPerThread=0.
                -Djava.awt.headless=true.
                -Djna.nosys=true.
                -Dlog4j.shutdownHookEnabled=false.
                -Dlog4j2.disable.jmx=true.
                -XX:\+AlwaysPreTouch.
                -XX:\+HeapDumpOnOutOfMemoryError.
                -XX:\+PrintGCApplicationStoppedTime.
                -XX:\+PrintGCDateStamps.
                -XX:\+PrintGCDetails.
                -XX:\+PrintTenuringDistribution.
                -XX:\+UseCMSInitiatingOccupancyOnly.
                -XX:\+UseConcMarkSweepGC.
                -XX:\+UseGCLogFileRotation.
                -XX:-OmitStackTraceInFastThrow.
                -XX:CMSInitiatingOccupancyFraction=75.
                -XX:GCLogFileSize=64m.
                -XX:NumberOfGCLogFiles=32.
                -Xloggc:\/var\/log\/elasticsearch\/es-instance\/gc.log.
                -Xms8g.
                -Xmx8g.
                -Xss1m.
                -server.
              /xm)
          end
        end
      end

      describe 'keystore' do
        let(:settings) do {
          'cloud.aws.access_key' => 'AKIA...',
          'cloud.aws.secret_key' => 'AKIA...'
        } end

        describe 'secrets' do
          context 'inherited' do
            let(:pre_condition) do
              <<-EOS
                class { 'elasticsearch':
                  secrets => #{settings}
                }
              EOS
            end

            it { should contain_elasticsearch_keystore('es-instance').with_settings(settings) }
          end

          context 'from instance' do
            let :params do {
              :secrets => settings
            } end

            it { should contain_elasticsearch_keystore('es-instance').with_settings(settings) }
          end

          context 'notify events' do
            let(:pre_condition) do
              <<-EOS
                class { 'elasticsearch':
                  restart_on_change => true
                }
              EOS
            end

            let :params do {
              :secrets => {}
            } end

            it { should contain_elasticsearch_keystore('es-instance').that_notifies('Elasticsearch::Service[es-instance]') }
          end
        end

        describe 'purge_secrets' do
          context 'default' do
            let :params do {
              :secrets => settings
            } end

            it { should contain_elasticsearch_keystore('es-instance').with_purge(false) }
          end

          context 'inherited' do
            let(:pre_condition) do
              <<-EOS
                class { 'elasticsearch':
                  purge_secrets => true,
                  secrets => #{settings}
                }
              EOS
            end

            it { should contain_elasticsearch_keystore('es-instance').with_purge(true) }
          end

          context 'from instance' do
            let :params do {
              :purge_secrets => true,
              :secrets       => settings
            } end

            it { should contain_elasticsearch_keystore('es-instance').with_purge(true) }
          end
        end
      end
    end
  end
end
