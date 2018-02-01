require 'spec_helper'
describe Puppet::Type.type(:rabbitmq_queue) do
  let(:queue) do
    Puppet::Type.type(:rabbitmq_queue).new(
      name: 'foo@bar',
      durable: :true,
      arguments: {
        'x-message-ttl' => 45,
        'x-dead-letter-exchange' => 'deadexchange'
      }
    )
  end

  it 'accepts an queue name' do
    queue[:name] = 'dan@pl'
    expect(queue[:name]).to eq('dan@pl')
  end
  it 'requires a name' do
    expect do
      Puppet::Type.type(:rabbitmq_queue).new({})
    end.to raise_error(Puppet::Error, 'Title or name must be provided')
  end
  it 'does not allow whitespace in the name' do
    expect do
      queue[:name] = 'b r'
    end.to raise_error(Puppet::Error, %r{Valid values match})
  end
  it 'does not allow names without @' do
    expect do
      queue[:name] = 'b_r'
    end.to raise_error(Puppet::Error, %r{Valid values match})
  end

  it 'accepts an arguments with numbers value' do
    queue[:arguments] = { 'x-message-ttl' => 30 }
    expect(queue[:arguments].to_json).to eq('{"x-message-ttl":30}')
  end

  it 'accepts an arguments with string value' do
    queue[:arguments] = { 'x-dead-letter-exchange' => 'catchallexchange' }
    expect(queue[:arguments].to_json).to eq('{"x-dead-letter-exchange":"catchallexchange"}')
  end

  it 'accepts an queue durable' do
    queue[:durable] = :true
    expect(queue[:durable]).to eq(:true)
  end

  it 'accepts a user' do
    queue[:user] = :root
    expect(queue[:user]).to eq(:root)
  end

  it 'accepts a password' do
    queue[:password] = :PaSsw0rD
    expect(queue[:password]).to eq(:PaSsw0rD)
  end
end
