require 'spec_helper'
describe Puppet::Type.type(:rabbitmq_parameter) do
  let(:parameter) do
    Puppet::Type.type(:rabbitmq_parameter).new(
      name: 'documentumShovel@/',
      component_name: 'shovel',
      value: {
        'src-uri' => 'amqp://myremote-server',
        'src-queue' => 'queue.docs.outgoing',
        'dest-uri' => 'amqp://',
        'dest-queue' => 'queue.docs.incoming'
      }
    )
  end

  it 'accepts a valid name' do
    parameter[:name] = 'documentumShovel@/'
    expect(parameter[:name]).to eq('documentumShovel@/')
  end

  it 'requires a name' do
    expect do
      Puppet::Type.type(:rabbitmq_parameter).new({})
    end.to raise_error(Puppet::Error, 'Title or name must be provided')
  end

  it 'fails when name does not have a @' do
    expect do
      parameter[:name] = 'documentumShovel'
    end.to raise_error(Puppet::Error, %r{Valid values match})
  end

  it 'accepts a string' do
    parameter[:component_name] = 'mystring'
    expect(parameter[:component_name]).to eq('mystring')
  end

  it 'is not empty' do
    expect do
      parameter[:component_name] = ''
    end.to raise_error(Puppet::Error, %r{component_name must be defined})
  end

  it 'accepts a valid hash for value' do
    value = { 'message-ttl' => '1800000' }
    parameter[:value] = value
    expect(parameter[:value]).to eq(value)
  end

  it 'does not accept an empty string for definition' do
    expect do
      parameter[:value] = ''
    end.to raise_error(Puppet::Error, %r{Invalid value})
  end

  it 'does not accept a string for definition' do
    expect do
      parameter[:value] = 'guest'
    end.to raise_error(Puppet::Error, %r{Invalid value})
  end

  it 'does not accept an array for definition' do
    expect do
      parameter[:value] = { 'message-ttl' => %w[999 100] }
    end.to raise_error(Puppet::Error, %r{Invalid value})
  end

  it 'accepts string as myparameter' do
    value = { 'myparameter' => 'mystring' }
    parameter[:value] = value
    expect(parameter[:value]['myparameter']).to eq('mystring')
  end

  it 'converts to integer when string only contains numbers' do
    value = { 'myparameter' => '1800000' }
    parameter[:value] = value
    expect(parameter[:value]['myparameter']).to eq(1_800_000)
  end
end
