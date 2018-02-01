require 'puppet'
require 'puppet/type/mongodb_database'

describe Puppet::Type.type(:mongodb_database) do
  let(:db) { Puppet::Type.type(:mongodb_database).new(name: 'test') }

  it 'accepts a database name' do
    expect(db[:name]).to eq('test')
  end

  it 'accepts a tries parameter' do
    db[:tries] = 5
    expect(db[:tries]).to eq(5)
  end

  it 'requires a name' do
    expect do
      Puppet::Type.type(:mongodb_database).new({})
    end.to raise_error(Puppet::Error, 'Title or name must be provided')
  end
end
