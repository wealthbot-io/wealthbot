require 'spec_helper'

describe 'mongodb::db', type: :define do
  context 'default' do
    let(:title) { 'testdb' }

    let(:params) do
      { 'user'     => 'testuser',
        'password' => 'testpass' }
    end

    it 'contains mongodb_database with mongodb::server requirement' do
      is_expected.to contain_mongodb_database('testdb')
    end

    it 'contains mongodb_user with mongodb_database requirement' do
      is_expected.to contain_mongodb_user('User testuser on db testdb'). \
        with_username('testuser'). \
        with_database('testdb'). \
        that_requires('Mongodb_database[testdb]')
    end

    it 'contains mongodb_user with proper roles' do
      params['roles'] = %w[testrole1 testrole2]
      is_expected.to contain_mongodb_user('User testuser on db testdb').\
        with_roles(%w[testrole1 testrole2])
    end

    it 'prefers password_hash instead of password' do
      params['password_hash'] = 'securehash'
      is_expected.to contain_mongodb_user('User testuser on db testdb').\
        with_password_hash('securehash')
    end

    it 'contains mongodb_database with proper tries param' do
      params['tries'] = 5
      is_expected.to contain_mongodb_database('testdb').with_tries(5)
    end
  end

  context 'with a db_name value' do
    let(:title) { 'testdb-title' }

    let(:params) do
      {
        'db_name'  => 'testdb',
        'user'     => 'testuser',
        'password' => 'testpass'
      }
    end

    it 'contains mongodb_database with mongodb::server requirement' do
      is_expected.to contain_mongodb_database('testdb')
    end

    it 'contains mongodb_user with mongodb_database requirement' do
      is_expected.to contain_mongodb_user('User testuser on db testdb'). \
        with_username('testuser'). \
        with_database('testdb'). \
        that_requires('Mongodb_database[testdb]')
    end

    it 'contains mongodb_user with proper roles' do
      params['roles'] = %w[testrole1 testrole2]
      is_expected.to contain_mongodb_user('User testuser on db testdb').\
        with_roles(%w[testrole1 testrole2])
    end

    it 'prefers password_hash instead of password' do
      params['password_hash'] = 'securehash'
      is_expected.to contain_mongodb_user('User testuser on db testdb').\
        with_password_hash('securehash')
    end

    it 'contains mongodb_database with proper tries param' do
      params['tries'] = 5
      is_expected.to contain_mongodb_database('testdb').with_tries(5)
    end
  end
end
