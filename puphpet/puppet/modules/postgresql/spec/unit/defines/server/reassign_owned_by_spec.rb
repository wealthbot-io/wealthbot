require 'spec_helper'

describe 'postgresql::server::reassign_owned_by', :type => :define do
  let :facts do
    {
      :osfamily => 'Debian',
      :operatingsystem => 'Debian',
      :operatingsystemrelease => '6.0',
      :kernel => 'Linux',
      :concat_basedir => tmpfilename('reassign_owned_by'),
      :id => 'root',
      :path => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
    }
  end

  let :title do
    'test'
  end

  let :params do
    {
      :db => 'test',
      :old_role => 'test_old_role',
      :new_role => 'test_new_role',
    }
  end

  let :pre_condition do
    <<-EOS
      class {'postgresql::server':}
      postgresql::server::role{ ['test_old_role','test_new_role']: }
    EOS
  end

  it { is_expected.to contain_postgresql__server__reassign_owned_by('test') }

  it {
    is_expected.to contain_postgresql_psql('reassign_owned_by:test:REASSIGN OWNED BY "test_old_role" TO "test_new_role"').with({
      'command' => "REASSIGN OWNED BY \"test_old_role\" TO \"test_new_role\"",
      'onlyif'  => /SELECT tablename FROM pg_catalog.pg_tables WHERE\s*schemaname NOT IN \('pg_catalog', 'information_schema'\) AND\s*tableowner = 'test_old_role'.*/m,
    }).that_requires('Class[postgresql::server]')
  }

end
