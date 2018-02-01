require 'spec_helper'

RSpec.configure do |config|
  config.mock_with :rspec
end

describe Facter::Util::Fact do
  before { Facter.clear }

  describe 'rabbitmq_nodename' do
    context 'with value' do
      before do
        allow(Facter::Util::Resolution).to receive(:which).with('rabbitmqctl') { true }
        allow(Facter::Core::Execution).to receive(:execute).with('rabbitmqctl status 2>&1') { 'Status of node monty@rabbit1 ...' }
      end
      it do
        expect(Facter.fact(:rabbitmq_nodename).value).to eq('monty@rabbit1')
      end
    end

    context 'with dashes in hostname' do
      before do
        allow(Facter::Util::Resolution).to receive(:which).with('rabbitmqctl') { true }
        allow(Facter::Core::Execution).to receive(:execute).with('rabbitmqctl status 2>&1') { 'Status of node monty@rabbit-1 ...' }
      end
      it do
        expect(Facter.fact(:rabbitmq_nodename).value).to eq('monty@rabbit-1')
      end
    end

    context 'with quotes around node name' do
      before do
        allow(Facter::Util::Resolution).to receive(:which).with('rabbitmqctl') { true }
        allow(Facter::Core::Execution).to receive(:execute).with('rabbitmqctl status 2>&1') { 'Status of node \'monty@rabbit-1\' ...' }
      end
      it do
        expect(Facter.fact(:rabbitmq_nodename).value).to eq('monty@rabbit-1')
      end
    end

    context 'without trailing points' do
      before do
        allow(Facter::Util::Resolution).to receive(:which).with('rabbitmqctl') { true }
        allow(Facter::Core::Execution).to receive(:execute).with('rabbitmqctl status 2>&1') { 'Status of node monty@rabbit-1' }
      end
      it do
        expect(Facter.fact(:rabbitmq_nodename).value).to eq('monty@rabbit-1')
      end
    end

    context 'rabbitmq is not running' do
      before do
        error_string = <<-EOS
Status of node 'monty@rabbit-1' ...
Error: unable to connect to node 'monty@rabbit-1': nodedown

DIAGNOSTICS
===========

attempted to contact: ['monty@rabbit-1']

monty@rabbit-1:
  * connected to epmd (port 4369) on centos-7-x64
  * epmd reports: node 'rabbit' not running at all
                  no other nodes on centos-7-x64
  * suggestion: start the node

current node details:
- node name: 'rabbitmq-cli-73@centos-7-x64'
- home dir: /var/lib/rabbitmq
- cookie hash: 6WdP0nl6d3HYqA5vTKMkIg==

        EOS
        allow(Facter::Util::Resolution).to receive(:which).with('rabbitmqctl') { true }
        allow(Facter::Core::Execution).to receive(:execute).with('rabbitmqctl status 2>&1') { error_string }
      end
      it do
        expect(Facter.fact(:rabbitmq_nodename).value).to eq('monty@rabbit-1')
      end
    end

    context 'rabbitmqctl is not in path' do
      before do
        allow(Facter::Util::Resolution).to receive(:which).with('rabbitmqctl') { false }
      end
      it do
        expect(Facter.fact(:rabbitmq_nodename).value).to be_nil
      end
    end
  end
end
