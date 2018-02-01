require 'spec_helper'
describe Puppet::Type.type(:rabbitmq_user_permissions) do
  let(:perms) do
    Puppet::Type.type(:rabbitmq_user_permissions).new(name: 'foo@bar')
  end

  it 'accepts a valid hostname name' do
    perms[:name] = 'dan@bar'
    expect(perms[:name]).to eq('dan@bar')
  end
  it 'requires a name' do
    expect do
      Puppet::Type.type(:rabbitmq_user_permissions).new({})
    end.to raise_error(Puppet::Error, 'Title or name must be provided')
  end
  it 'fails when names dont have a @' do
    expect do
      perms[:name] = 'bar'
    end.to raise_error(Puppet::Error, %r{Valid values match})
  end
  [:configure_permission, :read_permission, :write_permission].each do |param|
    it 'does not default to anything' do
      expect(perms[param]).to eq(nil)
    end
    it "should accept a valid regex for #{param}" do
      perms[param] = '.*?'
      expect(perms[param]).to eq('.*?')
    end
    it "should accept an empty string for #{param}" do
      perms[param] = ''
      expect(perms[param]).to eq('')
    end
    it "should not accept invalid regex for #{param}" do
      expect do
        perms[param] = '*'
      end.to raise_error(Puppet::Error, %r{Invalid regexp})
    end
  end
  # rubocop:disable RSpec/MultipleExpectations
  { rabbitmq_vhost: 'dan@test', rabbitmq_user: 'test@dan' }.each do |k, v|
    it "should autorequire #{k}" do
      vhost = if k == :rabbitmq_vhost
                Puppet::Type.type(k).new(name: 'test')
              else
                Puppet::Type.type(k).new(name: 'test', password: 'pass')
              end
      perm = Puppet::Type.type(:rabbitmq_user_permissions).new(name: v)
      Puppet::Resource::Catalog.new :testing do |conf|
        [vhost, perm].each { |resource| conf.add_resource resource }
      end
      rel = perm.autorequire[0]
      expect(rel.source.ref).to eq(vhost.ref)
      expect(rel.target.ref).to eq(perm.ref)
    end
  end
  # rubocop:enable RSpec/MultipleExpectations
end
