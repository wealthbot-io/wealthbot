require 'spec_helper'
describe Puppet::Type.type(:rabbitmq_policy) do
  let(:policy) do
    Puppet::Type.type(:rabbitmq_policy).new(
      name: 'ha-all@/',
      pattern: '.*',
      definition: {
        'ha-mode' => 'all'
      }
    )
  end

  it 'accepts a valid name' do
    policy[:name] = 'ha-all@/'
    expect(policy[:name]).to eq('ha-all@/')
  end

  it 'requires a name' do
    expect do
      Puppet::Type.type(:rabbitmq_policy).new({})
    end.to raise_error(Puppet::Error, 'Title or name must be provided')
  end

  it 'fails when name does not have a @' do
    expect do
      policy[:name] = 'ha-all'
    end.to raise_error(Puppet::Error, %r{Valid values match})
  end

  it 'accepts a valid regex for pattern' do
    policy[:pattern] = '.*?'
    expect(policy[:pattern]).to eq('.*?')
  end

  it 'accepts an empty string for pattern' do
    policy[:pattern] = ''
    expect(policy[:pattern]).to eq('')
  end

  it 'does not accept invalid regex for pattern' do
    expect do
      policy[:pattern] = '*'
    end.to raise_error(Puppet::Error, %r{Invalid regexp})
  end

  it 'accepts valid value for applyto' do
    [:all, :exchanges, :queues].each do |v|
      policy[:applyto] = v
      expect(policy[:applyto]).to eq(v)
    end
  end

  it 'does not accept invalid value for applyto' do
    expect do
      policy[:applyto] = 'me'
    end.to raise_error(Puppet::Error, %r{Invalid value})
  end

  it 'accepts a valid hash for definition' do
    definition = { 'ha-mode' => 'all', 'ha-sync-mode' => 'automatic' }
    policy[:definition] = definition
    expect(policy[:definition]).to eq(definition)
  end

  it 'does not accept a string for definition' do
    expect do
      policy[:definition] = 'ha-mode'
    end.to raise_error(Puppet::Error, %r{Invalid definition})
  end

  it 'does not accept invalid hash for definition' do
    expect do
      policy[:definition] = { 'ha-mode' => %w[a b] }
    end.to raise_error(Puppet::Error, %r{Invalid definition})
  end

  it 'accepts valid value for priority' do
    [0, 10, '0', '10'].each do |v|
      policy[:priority] = v
      expect(policy[:priority]).to eq(v)
    end
  end

  it 'does not accept invalid value for priority' do
    ['-1', -1, '1.0', 1.0, 'abc', ''].each do |v|
      expect do
        policy[:priority] = v
      end.to raise_error(Puppet::Error, %r{Invalid value})
    end
  end

  it 'accepts and convert ha-params for ha-mode exactly' do
    definition = { 'ha-mode' => 'exactly', 'ha-params' => '2' }
    policy[:definition] = definition
    expect(policy[:definition]['ha-params']).to eq(2)
  end

  it 'does not accept non-numeric ha-params for ha-mode exactly' do
    definition = { 'ha-mode' => 'exactly', 'ha-params' => 'nonnumeric' }
    expect do
      policy[:definition] = definition
    end.to raise_error(Puppet::Error, %r{Invalid ha-params.*nonnumeric.*exactly})
  end

  it 'accepts and convert the expires value' do
    definition = { 'expires' => '1800000' }
    policy[:definition] = definition
    expect(policy[:definition]['expires']).to eq(1_800_000)
  end

  it 'does not accept non-numeric expires value' do
    definition = { 'expires' => 'future' }
    expect do
      policy[:definition] = definition
    end.to raise_error(Puppet::Error, %r{Invalid expires value.*future})
  end

  it 'accepts and convert the message-ttl value' do
    definition = { 'message-ttl' => '1800000' }
    policy[:definition] = definition
    expect(policy[:definition]['message-ttl']).to eq(1_800_000)
  end

  it 'does not accept non-numeric message-ttl value' do
    definition = { 'message-ttl' => 'future' }
    expect do
      policy[:definition] = definition
    end.to raise_error(Puppet::Error, %r{Invalid message-ttl value.*future})
  end

  it 'accepts and convert the max-length value' do
    definition = { 'max-length' => '1800000' }
    policy[:definition] = definition
    expect(policy[:definition]['max-length']).to eq(1_800_000)
  end

  it 'does not accept non-numeric max-length value' do
    definition = { 'max-length' => 'future' }
    expect do
      policy[:definition] = definition
    end.to raise_error(Puppet::Error, %r{Invalid max-length value.*future})
  end

  it 'accepts and convert the max-length-bytes value' do
    definition = { 'max-length-bytes' => '1800000' }
    policy[:definition] = definition
    expect(policy[:definition]['max-length-bytes']).to eq(1_800_000)
  end

  it 'does not accept non-numeric max-length-bytes value' do
    definition = { 'max-length-bytes' => 'future' }
    expect do
      policy[:definition] = definition
    end.to raise_error(Puppet::Error, %r{Invalid max-length-bytes value.*future})
  end

  it 'accepts and convert the shards-per-node value' do
    definition = { 'shards-per-node' => '1800000' }
    policy[:definition] = definition
    expect(policy[:definition]['shards-per-node']).to eq(1_800_000)
  end

  it 'does not accept non-numeric shards-per-node value' do
    definition = { 'shards-per-node' => 'future' }
    expect do
      policy[:definition] = definition
    end.to raise_error(Puppet::Error, %r{Invalid shards-per-node value.*future})
  end

  it 'accepts and convert the ha-sync-batch-size value' do
    definition = { 'ha-sync-batch-size' => '1800000' }
    policy[:definition] = definition
    expect(policy[:definition]['ha-sync-batch-size']).to eq(1_800_000)
  end

  it 'does not accept non-numeric ha-sync-batch-size value' do
    definition = { 'ha-sync-batch-size' => 'future' }
    expect do
      policy[:definition] = definition
    end.to raise_error(Puppet::Error, %r{Invalid ha-sync-batch-size value.*future})
  end

  context 'accepts list value in ha-params when ha-mode = nodes' do
    before do
      policy[:definition] = definition
    end

    let(:definition) { { 'ha-mode' => 'nodes', 'ha-params' => ['rabbit@rabbit-01', 'rabbit@rabbit-02'] } }

    it { expect(policy[:definition]['ha-mode']).to eq('nodes') }
    it { expect(policy[:definition]['ha-params']).to be_a(Array) }
    it { expect(policy[:definition]['ha-params'][0]).to eq('rabbit@rabbit-01') }
    it { expect(policy[:definition]['ha-params'][1]).to eq('rabbit@rabbit-02') }
  end

  it 'does not accept non-list value in ha-params when ha-mode = nodes' do
    definition = { 'ha-mode' => 'nodes', 'ha-params' => 'this-will-fail' }
    expect do
      policy[:definition] = definition
    end.to raise_error(Puppet::Error, %r{Invalid definition, value this-will-fail for key ha-params is not an array})
  end
end
