require 'spec_helper'
describe Puppet::Type.type(:rabbitmq_user) do
  let(:user) do
    Puppet::Type.type(:rabbitmq_user).new(name: 'foo', password: 'pass')
  end

  it 'accepts a user name' do
    user[:name] = 'dan'
    expect(user[:name]).to eq('dan')
  end
  it 'admin is false when :admin is not set' do
    user[:name] = 'dan'
    expect(user[:admin]).to eq(:false)
  end
  it 'accepts a password' do
    user[:password] = 'foo'
    expect(user[:password]).to eq('foo')
  end
  it 'requires a name' do
    expect do
      Puppet::Type.type(:rabbitmq_user).new({})
    end.to raise_error(Puppet::Error, 'Title or name must be provided')
  end
  it 'does not allow whitespace in the name' do
    expect do
      user[:name] = 'b r'
    end.to raise_error(Puppet::Error, %r{Valid values match})
  end
  [true, false, 'true', 'false'].each do |val|
    it "admin property should accept #{val}" do
      user[:admin] = val
      expect(user[:admin]).to eq(val.to_s.to_sym)
    end
  end
  it 'does not accept non-boolean values for admin' do
    expect do
      user[:admin] = 'yes'
    end.to raise_error(Puppet::Error, %r{Invalid value})
  end
  it 'does not accept tags with spaces' do
    expect do
      user[:tags] = ['policy maker']
    end.to raise_error(Puppet::Error, %r{Invalid tag})
  end
  it 'does not accept the administrator tag' do
    expect do
      user[:tags] = ['administrator']
    end.to raise_error(Puppet::Error, %r{must use admin property})
  end
end
