#
# Author: Emilien Macchi <emilien.macchi@enovance.com>
#

require 'puppet'
require 'puppet/type/mongodb_replset'

describe Puppet::Type.type(:mongodb_replset) do
  let(:replset) { Puppet::Type.type(:mongodb_replset).new(name: 'test') }

  it 'accepts a replica set name' do
    expect(replset[:name]).to eq('test')
  end

  it 'accepts a members array' do
    replset[:members] = ['mongo1:27017', 'mongo2:27017']
    expect(replset[:members]).to eq(['mongo1:27017', 'mongo2:27017'])
  end

  it 'requires a name' do
    expect do
      Puppet::Type.type(:mongodb_replset).new({})
    end.to raise_error(Puppet::Error, 'Title or name must be provided')
  end
end
