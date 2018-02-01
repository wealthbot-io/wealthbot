require 'puppet'
require 'puppet/type/mongodb_user'

describe Puppet::Type.type(:mongodb_user) do
  let(:user) do
    Puppet::Type.type(:mongodb_user).new(
      name: 'test',
      database: 'testdb',
      password_hash: 'pass'
    )
  end

  it 'accepts a user name' do
    expect(user[:name]).to eq('test')
  end

  it 'accepts a database name' do
    expect(user[:database]).to eq('testdb')
  end

  it 'accepts a tries parameter' do
    user[:tries] = 5
    expect(user[:tries]).to eq(5)
  end

  it 'accepts a password hash' do
    user[:password_hash] = 'foo'
    expect(user[:password_hash]).to eq('foo')
  end

  it 'accepts a plaintext password' do
    user[:password] = 'foo'
    expect(user[:password]).to eq('foo')
  end

  it 'uses default role' do
    expect(user[:roles]).to eq(['dbAdmin'])
  end

  it 'accepts a roles array' do
    user[:roles] = %w[role1 role2]
    expect(user[:roles]).to eq(%w[role1 role2])
  end

  it 'requires a name' do
    expect do
      Puppet::Type.type(:mongodb_user).new({})
    end.to raise_error(Puppet::Error, 'Title or name must be provided')
  end

  it 'requires a database' do
    expect do
      Puppet::Type.type(:mongodb_user).new(name: 'test', password_hash: 'pass')
    end.to raise_error(Puppet::Error, 'Parameter \'database\' must be set')
  end

  it 'requires a password_hash' do
    expect do
      Puppet::Type.type(:mongodb_user).new(name: 'test', database: 'testdb')
    end.to raise_error(Puppet::Error, 'Property \'password_hash\' must be set. Use mongodb_password() for creating hash.')
  end

  it 'sorts roles' do
    # Reinitialize type with explicit unsorted roles.
    user = Puppet::Type.type(:mongodb_user).new(
      name: 'test',
      database: 'testdb',
      password_hash: 'pass',
      roles: %w[b a]
    )

    expect(user[:roles]).to eq(%w[a b])
  end
end
