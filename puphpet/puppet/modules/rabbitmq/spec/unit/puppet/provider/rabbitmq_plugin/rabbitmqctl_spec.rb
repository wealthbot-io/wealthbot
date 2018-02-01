require 'spec_helper'

provider_class = Puppet::Type.type(:rabbitmq_plugin).provider(:rabbitmqplugins)
describe provider_class do
  let(:resource) do
    Puppet::Type::Rabbitmq_plugin.new(
      name: 'foo'
    )
  end
  let(:provider) { provider_class.new(resource) }

  it 'matches plugins' do
    provider.expects(:rabbitmqplugins).with('list', '-E', '-m').returns("foo\n")
    expect(provider.exists?).to eq(true)
  end
  it 'calls rabbitmqplugins to enable' do
    provider.expects(:rabbitmqplugins).with('enable', 'foo')
    provider.create
  end
  it 'calls rabbitmqplugins to disable' do
    provider.expects(:rabbitmqplugins).with('disable', 'foo')
    provider.destroy
  end
end
