require 'spec_helper'

describe Puppet::Type.type(:rabbitmq_parameter).provider(:rabbitmqctl) do
  let(:resource) do
    Puppet::Type.type(:rabbitmq_parameter).new(
      name: 'documentumShovel@/',
      component_name: 'shovel',
      value: {
        'src-uri'    => 'amqp://',
        'src-queue'  => 'my-queue',
        'dest-uri'   => 'amqp://remote-server',
        'dest-queue' => 'another-queue'
      },
      provider: described_class.name
    )
  end
  let(:provider) { resource.provider }

  after do
    described_class.instance_variable_set(:@parameters, nil)
  end

  context 'has "@" in parameter name' do
    let(:resource) do
      Puppet::Type.type(:rabbitmq_parameter).new(
        name: 'documentumShovel@/',
        component_name: 'shovel',
        value: {
          'src-uri'    => 'amqp://',
          'src-queue'  => 'my-queue',
          'dest-uri'   => 'amqp://remote-server',
          'dest-queue' => 'another-queue'
        },
        provider: described_class.name
      )
    end
    let(:provider) { described_class.new(resource) }

    it do
      expect(provider.should_parameter).to eq('documentumShovel')
    end

    it do
      expect(provider.should_vhost).to eq('/')
    end
  end

  it 'fails with invalid output from list' do
    provider.class.expects(:rabbitmqctl).with('list_parameters', '-q', '-p', '/').returns 'foobar'
    expect { provider.exists? }.to raise_error(Puppet::Error, %r{cannot parse line from list_parameter})
  end

  it 'matches parameters from list' do
    provider.class.expects(:rabbitmqctl).with('list_parameters', '-q', '-p', '/').returns <<-EOT
shovel  documentumShovel  {"src-uri":"amqp://","src-queue":"my-queue","dest-uri":"amqp://remote-server","dest-queue":"another-queue"}
EOT
    expect(provider.exists?).to eq(component_name: 'shovel',
                                   value: {
                                     'src-uri' => 'amqp://',
                                     'src-queue'  => 'my-queue',
                                     'dest-uri'   => 'amqp://remote-server',
                                     'dest-queue' => 'another-queue'
                                   })
  end

  it 'does not match an empty list' do
    provider.class.expects(:rabbitmqctl).with('list_parameters', '-q', '-p', '/').returns ''
    expect(provider.exists?).to eq(nil)
  end

  it 'destroys parameter' do
    provider.expects(:rabbitmqctl).with('clear_parameter', '-p', '/', 'shovel', 'documentumShovel')
    provider.destroy
  end

  it 'onlies call set_parameter once' do
    provider.expects(:rabbitmqctl).with('set_parameter',
                                        '-p', '/',
                                        'shovel',
                                        'documentumShovel',
                                        '{"src-uri":"amqp://","src-queue":"my-queue","dest-uri":"amqp://remote-server","dest-queue":"another-queue"}').once
    provider.create
  end
end
