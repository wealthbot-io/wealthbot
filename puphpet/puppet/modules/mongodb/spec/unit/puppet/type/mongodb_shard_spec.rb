require 'puppet'
require 'puppet/type/mongodb_shard'

describe Puppet::Type.type(:mongodb_shard) do
  let(:shard) { Puppet::Type.type(:mongodb_shard).new(name: 'test') }

  it 'accepts a shard name' do
    expect(shard[:name]).to eq('test')
  end

  it 'accepts a member' do
    shard[:member] = 'rs_test/mongo1:27017'
    expect(shard[:member]).to eq('rs_test/mongo1:27017')
  end

  it 'accepts a keys array' do
    shard[:keys] = [{ 'foo.bar' => { 'name' => 1 } }]
    expect(shard[:keys]).to eq([{ 'foo.bar' => { 'name' => 1 } }])
  end

  it 'requires a name' do
    expect do
      Puppet::Type.type(:mongodb_shard).new({})
    end.to raise_error(Puppet::Error, 'Title or name must be provided')
  end
end
