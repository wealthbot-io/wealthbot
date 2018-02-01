require 'spec_helper'

describe 'elasticsearch', :type => 'class' do
  let(:repo_version) { '1.7' }
  let(:version) { '1.7.6' }
  let(:default_params) do
    {
      :config => {},
      :manage_repo => true,
      :repo_version => repo_version,
      :version => version
    }
  end

  let(:params) { default_params }
  let(:key_source) do
    'https://artifacts.elastic.co/GPG-KEY-elasticsearch'
  end

  # First, randomly select one of our supported OSes to run tests that apply
  # to any distro
  on_supported_os.to_a.sample(1).to_h.each do |os, facts|
    context "on #{os}" do
      let(:facts) do
        facts.merge('scenario' => '', 'common' => '')
      end

      context 'Use stage type for ordering' do
        let(:params) { default_params.merge(:repo_stage => 'setup') }

        it { should contain_stage('setup') }
        it { should contain_class('elasticsearch::repo')
          .with(:stage => 'setup')}
      end
    end
  end

  # The rest of the tests vary across distributions
  on_supported_os.each do |os, facts|
    context "on #{os}" do
      let(:facts) do
        facts.merge('scenario' => '', 'common' => '')
      end

      describe 'distro-specific package repositories' do
        case facts[:os]['family']
        when 'Debian'
          it { should contain_apt__source('elasticsearch')
            .with(
              :location => 'http://packages.elastic.co/elasticsearch/1.7/debian'
            ) }
        when 'RedHat'
          it { should contain_yumrepo('elasticsearch')
            .with(
              :baseurl => 'http://packages.elastic.co/elasticsearch/1.7/centos'
            ) }
        when 'Suse'
          it { should contain_exec('elasticsearch_suse_import_gpg')
            .with(:command => "rpmkeys --import #{key_source}") }
          it { should contain_zypprepo('elasticsearch')
            .with(:baseurl => 'http://packages.elastic.co/elasticsearch/1.7/centos') }
          it { should contain_exec(
            'elasticsearch_zypper_refresh_elasticsearch'
          ) }
        end
      end

      describe 'overriding the repo key ID' do
        let(:params) do
          default_params.merge(
            :repo_key_id => '46095ACC8548582C1A2699A9D27D666CD88E42B4'
          )
        end

        case facts[:os]['family']
        when 'Debian'
          it { is_expected.to contain_apt__source('elasticsearch').with(
            :key => {
              'id' => '46095ACC8548582C1A2699A9D27D666CD88E42B4',
              'source' => key_source
            }
          )}
        when 'Suse'
          it { is_expected.to contain_exec(
            'elasticsearch_suse_import_gpg'
          ).with_unless(
            "test $(rpm -qa gpg-pubkey | grep -i 'D88E42B4' | wc -l) -eq 1"
          )}
        end
      end

      describe 'overriding the repo source URL' do
        let(:key_source) do
          'http://artifacts.elastic.co/GPG-KEY-elasticsearch'
        end
        let(:params) do
          default_params.merge(
            :repo_key_source => key_source
          )
        end

        case facts[:os]['family']
        when 'Debian'
          it { is_expected.to contain_apt__source('elasticsearch').with(
            :key => {
              'id' => '46095ACC8548582C1A2699A9D27D666CD88E42B4',
              'source' => key_source
            }
          )}
        when 'RedHat'
          it { should contain_yumrepo('elasticsearch')
            .with(:gpgkey => key_source) }
        when 'Suse'
          it { should contain_exec('elasticsearch_suse_import_gpg')
            .with(:command => "rpmkeys --import #{key_source}") }
        end
      end

      describe 'overriding repo_proxy' do
        let(:params) do
          default_params.merge(:repo_proxy => 'http://proxy.com:8080')
        end

        case facts[:os]['family']
        when 'RedHat'
          it { is_expected.to contain_yumrepo('elasticsearch')
            .with_proxy('http://proxy.com:8080') }
        end
      end

      describe 'unified release repositories' do
        ['2.x', '5.x'].each do |major_release|
          context "version #{major_release}" do
            let(:repo_version) { major_release }
            let(:version) { "#{major_release[0]}.0.0" }
            let(:post_5?) do
              Gem::Version.new(major_release[0]) >= Gem::Version.new('5.0')
            end
            let(:repo_base) do
              if post_5?
                "https://artifacts.elastic.co/packages/#{repo_version}"
              else
                "http://packages.elastic.co/elasticsearch/#{repo_version}"
              end
            end

            case facts[:os]['family']
            when 'Debian'
              it { should contain_apt__source('elasticsearch')
                .with_location("#{repo_base}/#{post_5? ? 'apt' : 'debian'}") }
            when 'RedHat'
              it { should contain_yumrepo('elasticsearch')
                .with_baseurl("#{repo_base}/#{post_5? ? 'yum' : 'centos'}") }
            when 'Suse'
              it { should contain_zypprepo('elasticsearch')
                .with_baseurl("#{repo_base}/#{post_5? ? 'yum' : 'centos'}") }
            end
          end
        end
      end

      describe 'repo_baseurl' do
        let(:params) { default_params.merge(:repo_baseurl => repo_baseurl) }

        context 'invalid parameter' do
          [true, [1], { :key => :val }].each do |invalid_value|
            describe invalid_value do
              let(:repo_baseurl) { invalid_value }

              it { should_not compile }
            end
          end
        end

        context 'local repository' do
          let(:repo_baseurl) { 'https://repo.local/path' }

          case facts[:os]['family']
          when 'Debian'
            it { should contain_apt__source('elasticsearch')
              .with_location(repo_baseurl) }
          when 'RedHat'
            it { should contain_yumrepo('elasticsearch')
              .with_baseurl(repo_baseurl) }
          when 'Suse'
            it { should contain_zypprepo('elasticsearch')
              .with_baseurl(repo_baseurl) }
          end
        end
      end
    end
  end
end
