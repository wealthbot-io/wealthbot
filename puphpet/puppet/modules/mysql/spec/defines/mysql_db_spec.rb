require 'spec_helper'

describe 'mysql::db', type: :define do
  on_supported_os.each do |os, facts|
    context "on #{os}" do
      let(:facts) do
        facts.merge(root_home: '/root')
      end

      let(:title) { 'test_db' }

      let(:params) do
        { 'user'     => 'testuser',
          'password' => 'testpass' }
      end

      it 'does not notify the import sql exec if no sql script was provided' do
        is_expected.to contain_mysql_database('test_db').without_notify
      end

      it 'subscribes to database if sql script is given' do
        params['sql'] = 'test_sql'
        is_expected.to contain_exec('test_db-import').with_subscribe('Mysql_database[test_db]')
      end

      it 'onlies import sql script on creation if not enforcing' do
        params.merge!('sql' => 'test_sql', 'enforce_sql' => false)
        is_expected.to contain_exec('test_db-import').with_refreshonly(true)
      end

      it 'imports sql script on creation if enforcing' do
        params.merge!('sql' => 'test_sql', 'enforce_sql' => true)
        is_expected.to contain_exec('test_db-import').with_refreshonly(false)
        is_expected.to contain_exec('test_db-import').with_command('cat test_sql | mysql test_db')
      end

      it 'imports sql script with custom command on creation if enforcing' do
        params.merge!('sql' => 'test_sql', 'enforce_sql' => true, 'import_cat_cmd' => 'zcat')
        is_expected.to contain_exec('test_db-import').with_refreshonly(false)
        is_expected.to contain_exec('test_db-import').with_command('zcat test_sql | mysql test_db')
      end

      it 'imports sql scripts when more than one is specified' do
        params['sql'] = %w[test_sql test_2_sql]
        is_expected.to contain_exec('test_db-import').with_command('cat test_sql test_2_sql | mysql test_db')
      end

      it 'does not create database and database user' do
        params.merge!('ensure' => 'absent', 'host' => 'localhost')
        is_expected.to contain_mysql_database('test_db').with_ensure('absent')
        is_expected.to contain_mysql_user('testuser@localhost').with_ensure('absent')
      end

      it 'creates with an appropriate collate and charset' do
        params.merge!('charset' => 'utf8', 'collate' => 'utf8_danish_ci')
        is_expected.to contain_mysql_database('test_db').with('charset' => 'utf8',
                                                              'collate' => 'utf8_danish_ci')
      end

      it 'uses dbname parameter as database name instead of name' do
        params['dbname'] = 'real_db'
        is_expected.to contain_mysql_database('real_db')
      end
    end
  end
end
