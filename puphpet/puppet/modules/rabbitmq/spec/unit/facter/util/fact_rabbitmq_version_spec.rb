require 'spec_helper'

RSpec.configure do |config|
  config.mock_with :rspec
end

describe Facter::Util::Fact do
  before do
    Facter.clear
  end

  describe 'rabbitmq_version' do
    context 'with value' do
      before do
        allow(Facter::Util::Resolution).to receive(:which).with('rabbitmqadmin') { true }
        allow(Facter::Core::Execution).to receive(:execute).with('rabbitmqadmin --version 2>&1') { 'rabbitmqadmin 3.6.0' }
      end
      it do
        expect(Facter.fact(:rabbitmq_version).value).to eq('3.6.0')
      end
    end
    context 'rabbitmqadmin is not in path' do
      before do
        allow(Facter::Util::Resolution).to receive(:which).with('rabbitmqadmin') { false }
      end
      it do
        expect(Facter.fact(:rabbitmq_version).value).to be_nil
      end
    end
  end
end
