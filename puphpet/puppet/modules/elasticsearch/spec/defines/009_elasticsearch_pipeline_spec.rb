require 'spec_helper'

describe 'elasticsearch::pipeline', :type => 'define' do
  let(:title) { 'testpipeline' }
  let(:pre_condition) do
    'class { "elasticsearch" : }'
  end

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

      describe 'parameter validation' do
        %i[api_ca_file api_ca_path].each do |param|
          let :params do
            {
              :ensure => 'present',
              :content => {},
              param => 'foo/cert'
            }
          end

          it 'validates cert paths' do
            is_expected.to compile.and_raise_error(/expects a/)
          end
        end

        describe 'missing parent class' do
          let(:pre_condition) {}
          it { should_not compile }
        end
      end

      describe 'class parameter inheritance' do
        let :params do
          {
            :ensure => 'present',
            :content => {}
          }
        end
        let(:pre_condition) do
          <<-EOS
            class { 'elasticsearch' :
              api_protocol => 'https',
              api_host => '127.0.0.1',
              api_port => 9201,
              api_timeout => 11,
              api_basic_auth_username => 'elastic',
              api_basic_auth_password => 'password',
              api_ca_file => '/foo/bar.pem',
              api_ca_path => '/foo/',
              validate_tls => false,
            }
          EOS
        end

        it do
          should contain_elasticsearch__pipeline(title)
          should contain_es_instance_conn_validator("#{title}-ingest-pipeline")
            .that_comes_before("elasticsearch_pipeline[#{title}]")
          should contain_elasticsearch_pipeline(title).with(
            :ensure => 'present',
            :content => {},
            :protocol => 'https',
            :host => '127.0.0.1',
            :port => 9201,
            :timeout => 11,
            :username => 'elastic',
            :password => 'password',
            :ca_file => '/foo/bar.pem',
            :ca_path => '/foo/',
            :validate_tls => false
          )
        end
      end

      describe 'pipeline deletion' do
        let :params do
          {
            :ensure => 'absent'
          }
        end

        it 'removes pipelines' do
          should contain_elasticsearch_pipeline(title).with(:ensure => 'absent')
        end
      end
    end
  end
end
