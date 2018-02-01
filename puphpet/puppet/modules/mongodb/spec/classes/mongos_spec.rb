require 'spec_helper'

describe 'mongodb::mongos' do
  on_supported_os.each do |os, facts|
    context "on #{os}" do
      let(:facts) { facts }
      let(:params) do
        {
          configdb: ['127.0.0.1:27019']
        }
      end

      context 'with defaults' do
        it { is_expected.to compile.with_all_deps }
        it { is_expected.to contain_class('mongodb::mongos::install') }
        it { is_expected.to contain_class('mongodb::mongos::config') }
        it { is_expected.to contain_class('mongodb::mongos::service') }
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
