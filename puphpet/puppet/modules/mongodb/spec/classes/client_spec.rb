require 'spec_helper'

describe 'mongodb::client' do
  on_supported_os.each do |os, facts|
    context "on #{os}" do
      let(:facts) { facts }

      context 'with defaults' do
        it { is_expected.to compile.with_all_deps }
      end

      context 'with manage_package' do
        let(:pre_condition) do
          "class { 'mongodb::globals': manage_package => true }"
        end

        it { is_expected.to compile.with_all_deps }
        it { is_expected.to create_package('mongodb_client').with_ensure('present') }
      end
    end
  end
end
