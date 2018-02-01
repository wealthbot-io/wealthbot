require 'spec_helper'

describe Facter::Util::Fact do # rubocop:disable RSpec/FilePath
  before(:each) do
    Facter.clear
  end

  describe 'vcsrepo_svn_ver' do
    context 'with valid value' do
      before :each do
        Facter::Core::Execution
          .stubs(:execute)
          .with('svn --version --quiet')
          .returns('1.7.23')
      end
      it {
        expect(Facter.fact(:vcsrepo_svn_ver).value).to eq('1.7.23')
      }
    end
  end
end
