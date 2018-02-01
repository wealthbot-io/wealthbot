require 'spec_helper'

describe 'mongodb::server' do
  on_supported_os.each do |os, facts|
    context "on #{os}" do
      let(:facts) { facts }

      describe 'with defaults' do
        it { is_expected.to compile.with_all_deps }
        it {
          is_expected.to contain_class('mongodb::server::install').
            that_comes_before('Class[mongodb::server::config]')
        }
        it {
          is_expected.to contain_class('mongodb::server::config').
            that_notifies('Class[mongodb::server::service]')
        }
        it { is_expected.to contain_class('mongodb::server::service') }
      end

      describe 'with manage_package => true' do
        let(:pre_condition) do
          'class { mongodb::globals:
            manage_package => true
          }'
        end

        it { is_expected.to compile.with_all_deps }
      end

      describe 'with create_admin => true' do
        let(:params) do
          {
            create_admin: true,
            admin_username: 'admin',
            admin_password: 'password'
          }
        end

        it { is_expected.to compile.with_all_deps }
        it {
          is_expected.to contain_class('mongodb::server::install').
            that_comes_before('Class[mongodb::server::config]')
        }
        it {
          is_expected.to contain_class('mongodb::server::config').
            that_notifies('Class[mongodb::server::service]')
        }
        it { is_expected.to contain_class('mongodb::server::service') }

        it {
          is_expected.to contain_mongodb__db('admin').with('user' => 'admin',
                                                           'password' => 'password',
                                                           'roles'    => %w[userAdmin readWrite dbAdmin dbAdminAnyDatabase
                                                                            readAnyDatabase readWriteAnyDatabase userAdminAnyDatabase
                                                                            clusterAdmin clusterManager clusterMonitor hostManager
                                                                            root restore])
        }
        it { is_expected.to contain_mongodb_database('admin').that_requires('Service[mongodb]') }
      end

      context 'setting nohttpinterface' do
        case facts[:os]['family']
        when 'RedHat'
          case facts[:os]['release']['major']
          when '7'
            let(:config_file) { '/etc/mongod.conf' }
          else
            let(:config_file) { '/etc/mongodb.conf' }
          end
        when 'Debian'
          let(:config_file) { '/etc/mongodb.conf' }
        end

        it "isn't set when undef" do
          is_expected.not_to contain_file(config_file).with_content(%r{nohttpinterface})
        end

        describe 'sets nohttpinterface to true when true' do
          let(:params) do
            { nohttpinterface: true }
          end

          it { is_expected.to contain_file(config_file).with_content(%r{nohttpinterface = true}) }
        end
        describe 'sets nohttpinterface to false when false' do
          let(:params) do
            { nohttpinterface: false }
          end

          it { is_expected.to contain_file(config_file).with_content(%r{nohttpinterface = false}) }
        end

        context 'on >= 2.6' do
          let(:pre_condition) do
            "class { 'mongodb::globals': version => '2.6.6', }"
          end

          it "isn't set when undef" do
            is_expected.not_to contain_file(config_file).with_content(%r{net\.http\.enabled})
          end

          describe 'sets net.http.enabled false when true' do
            let(:params) do
              { nohttpinterface: true }
            end

            it { is_expected.to contain_file(config_file).with_content(%r{net\.http\.enabled: false}) }
          end

          describe 'sets net.http.enabled true when false' do
            let(:params) do
              { nohttpinterface: false }
            end

            it { is_expected.to contain_file(config_file).with_content(%r{net\.http\.enabled: true}) }
          end
        end
      end

      context 'when setting up replicasets' do
        describe 'should fail if providing both replica_sets and replset_members' do
          let(:params) do
            {
              replset: 'rsTest',
              replset_members: [
                'mongo1:27017',
                'mongo2:27017',
                'mongo3:27017'
              ],
              replica_sets: {}
            }
          end

          it { expect { is_expected.to raise_error(%r{Puppet::Error: You can provide either replset_members or replica_sets, not both}) } }
        end

        describe 'should setup using replica_sets hash' do
          let(:rsConf) do
            {
              'rsTest' => {
                'members' => [
                  'mongo1:27017',
                  'mongo2:27017',
                  'mongo3:27017'
                ],
                'arbiter' => 'mongo3:27017'
              }
            }
          end

          let(:params) do
            {
              replset: 'rsTest',
              replset_config: rsConf
            }
          end

          it { is_expected.to contain_class('mongodb::replset').with_sets(rsConf) }
        end

        describe 'should setup using replset_members' do
          let(:rsConf) do
            {
              'rsTest' => {
                'ensure'  => 'present',
                'members' => [
                  'mongo1:27017',
                  'mongo2:27017',
                  'mongo3:27017'
                ]
              }
            }
          end

          let(:params) do
            {
              replset: 'rsTest',
              replset_members: [
                'mongo1:27017',
                'mongo2:27017',
                'mongo3:27017'
              ]
            }
          end

          it { is_expected.to contain_class('mongodb::replset').with_sets(rsConf) }
        end
      end
    end
  end

  context 'when deploying on Solaris' do
    let :facts do
      { osfamily: 'Solaris' }
    end

    it { expect { is_expected.to raise_error(Puppet::Error) } }
  end
end
