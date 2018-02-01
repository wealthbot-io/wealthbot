require 'spec_helper'

describe 'elasticsearch', :type => 'class' do
  default_params = {
    :config => { 'node.name' => 'foo' }
  }

  let(:params) do
    default_params.merge({})
  end

  on_supported_os(
    :hardwaremodels => ['x86_64'],
    :supported_os => [
      {
        'operatingsystem' => 'CentOS',
        'operatingsystemrelease' => ['7']
      }
    ]
  ).each do |os, facts|
    context "on #{os}" do
      context 'hiera' do
        describe 'indices' do
          context 'single indices' do
            let(:facts) { facts.merge(:scenario => 'singleindex') }

            it { should contain_elasticsearch__index('baz')
              .with(
                :ensure => 'present',
                :settings => {
                  'index' => {
                    'number_of_shards' => 1
                  }
                }
              ) }
            it { should contain_elasticsearch_index('baz') }
            it { should contain_es_instance_conn_validator(
              'baz-index-conn-validator'
            ) }
          end

          context 'no indices' do
            let(:facts) { facts.merge(:scenario => '') }

            it { should_not contain_elasticsearch__index('baz') }
          end
        end

        describe 'instances' do
          context 'single instance' do
            let(:facts) { facts.merge(:scenario => 'singleinstance') }

            include_examples 'instance', 'es-hiera-single', :systemd
          end

          context 'multiple instances' do
            let(:facts) { facts.merge(:scenario => 'multipleinstances') }

            include_examples 'instance', 'es-hiera-multiple-1', :systemd
            include_examples 'instance', 'es-hiera-multiple-2', :systemd
          end

          context 'no instances' do
            let(:facts) { facts.merge(:scenario => '') }

            it { should_not contain_elasticsearch__instance('es-hiera-multiple-1') }
            it { should_not contain_elasticsearch__instance('es-hiera-multiple-2') }
          end

          context 'multiple instances using lookup_options' do
            let(:facts) do
              facts.merge(
                :common => 'defaultinstance-merged',
                :scenario => 'singleinstance'
              )
            end

            include_examples 'instance', 'default', :systemd
            include_examples 'instance', 'es-hiera-single', :systemd
          end
        end # of instances

        describe 'pipelines' do
          context 'single pipeline' do
            let(:facts) { facts.merge(:scenario => 'singlepipeline') }

            it { should contain_elasticsearch__pipeline('testpipeline')
              .with(
                :ensure => 'present',
                :content => {
                  'description' => 'Add the foo field',
                  'processors' => [
                    {
                      'set' => {
                        'field' => 'foo',
                        'value' => 'bar'
                      }
                    }
                  ]
                }
              ) }
            it { should contain_elasticsearch_pipeline('testpipeline') }
          end

          context 'no pipelines' do
            let(:facts) { facts.merge(:scenario => '') }

            it { should_not contain_elasticsearch__pipeline('testpipeline') }
          end
        end

        describe 'plugins' do
          context 'single plugin' do
            let(:facts) { facts.merge(:scenario => 'singleplugin') }

            it { should contain_elasticsearch__plugin('mobz/elasticsearch-head')
              .with(
                :ensure => 'present',
                :module_dir => 'head',
                :instances => ['es-hiera-single']
              ) }
            it { should contain_elasticsearch_plugin('mobz/elasticsearch-head') }
          end

          context 'no plugins' do
            let(:facts) { facts.merge(:scenario => '') }

            it { should_not contain_elasticsearch__plugin(
              'mobz/elasticsearch-head/1.0.0'
            ) }
          end
        end

        describe 'roles' do
          context 'single roles' do
            let(:facts) { facts.merge(:scenario => 'singlerole') }
            let(:params) do
              default_params.merge(:security_plugin => 'x-pack')
            end

            it { should contain_elasticsearch__role('admin')
              .with(
                :ensure => 'present',
                :privileges => {
                  'cluster' => 'monitor',
                  'indices' => {
                    '*' => 'all'
                  }
                },
                :mappings => [
                  'cn=users,dc=example,dc=com'
                ]
              ) }
            it { should contain_elasticsearch_role('admin') }
            it { should contain_elasticsearch_role_mapping('admin') }
          end

          context 'no roles' do
            let(:facts) { facts.merge(:scenario => '') }

            it { should_not contain_elasticsearch__role('admin') }
          end
        end

        describe 'scripts' do
          context 'single scripts' do
            let(:facts) { facts.merge(:scenario => 'singlescript') }

            it { should contain_elasticsearch__script('myscript')
              .with(
                :ensure => 'present',
                :source => 'puppet:///file/here'
              ) }
            it { should contain_file('/usr/share/elasticsearch/scripts/here') }
          end

          context 'no roles' do
            let(:facts) { facts.merge(:scenario => '') }

            it { should_not contain_elasticsearch__script('myscript') }
          end
        end

        describe 'templates' do
          context 'single template' do
            let(:facts) { facts.merge(:scenario => 'singletemplate') }

            it { should contain_elasticsearch__template('foo')
              .with(
                :ensure => 'present',
                :content => {
                  'template' => 'foo-*',
                  'settings' => {
                    'index' => {
                      'number_of_replicas' => 0
                    }
                  }
                }
              ) }
            it { should contain_elasticsearch_template('foo') }
          end

          context 'no templates' do
            let(:facts) { facts.merge(:scenario => '') }

            it { should_not contain_elasticsearch__template('foo') }
          end
        end

        describe 'users' do
          context 'single users' do
            let(:facts) { facts.merge(:scenario => 'singleuser') }
            let(:params) do
              default_params.merge(:security_plugin => 'x-pack')
            end

            it { should contain_elasticsearch__user('elastic')
              .with(
                :ensure => 'present',
                :roles => ['admin'],
                :password => 'password'
              ) }
            it { should contain_elasticsearch_user('elastic') }
          end

          context 'no users' do
            let(:facts) { facts.merge(:scenario => '') }

            it { should_not contain_elasticsearch__user('elastic') }
          end
        end
      end
    end
  end
end
