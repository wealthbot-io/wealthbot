require 'spec_helper'

describe Puppet::Type.type(:postgresql_conn_validator).provider(:ruby) do

  let(:resource) { Puppet::Type.type(:postgresql_conn_validator).new({
                                                                   :name => "testname"
                                                                  }.merge attributes) }
  let(:provider) { resource.provider }

  let(:attributes) do
    {
      :psql_path   => '/usr/bin/psql',
      :host        => 'db.test.com',
      :port        => 4444,
      :db_username => 'testuser',
      :db_password => 'testpass'
    }
  end

  describe '#build_psql_cmd' do
    it 'contains expected commandline options' do
      expect(provider.validator.build_psql_cmd).to match /\/usr\/bin\/psql.*--host.*--port.*--username.*/
    end
  end

  describe '#parse_connect_settings' do
    it 'returns array if password is present' do
      expect(provider.validator.parse_connect_settings).to eq(['PGPASSWORD=testpass'])
    end

    it 'returns an empty array if password is nil' do
      attributes.delete(:db_password)
      expect(provider.validator.parse_connect_settings).to eq([])
    end

    let(:connect_settings) do
      {
        :connect_settings => {
          :PGPASSWORD => 'testpass',
          :PGHOST     => 'db.test.com',
          :PGPORT     => '1234'
        }
      }
    end
    it 'returns an array of settings' do
      attributes.delete(:db_password)
      attributes.merge! connect_settings
      expect(provider.validator.parse_connect_settings).to eq(['PGPASSWORD=testpass','PGHOST=db.test.com','PGPORT=1234'])
    end
  end

  describe '#attempt_connection' do
    let(:sleep_length) {1}
    let(:tries) {3}
    let(:exec) {
      provider.validator.stub(:execute_command).and_return(true)
    }

    it 'tries the correct number of times' do
      expect(provider.validator).to receive(:execute_command).exactly(3).times

      provider.validator.attempt_connection(sleep_length,tries)

    end
  end
end
