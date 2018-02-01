require 'spec_helper'

provider_class = Puppet::Type.type(:rabbitmq_vhost).provider(:rabbitmqctl)
describe provider_class do
  let(:resource) do
    Puppet::Type::Rabbitmq_vhost.new(
      name: 'foo'
    )
  end
  let(:provider) { provider_class.new(resource) }

  it 'matches vhost names' do
    provider.expects(:rabbitmqctl).with('-q', 'list_vhosts').returns <<-EOT
Listing vhosts ...
foo
...done.
EOT
    expect(provider.exists?).to eq(true)
  end
  it 'does not match if no vhosts on system' do
    provider.expects(:rabbitmqctl).with('-q', 'list_vhosts').returns <<-EOT
Listing vhosts ...
...done.
EOT
    expect(provider.exists?).to eq(false)
  end
  it 'does not match if no matching vhosts on system' do
    provider.expects(:rabbitmqctl).with('-q', 'list_vhosts').returns <<-EOT
Listing vhosts ...
fooey
...done.
EOT
    expect(provider.exists?).to eq(false)
  end
  it 'calls rabbitmqctl to create' do
    provider.expects(:rabbitmqctl).with('add_vhost', 'foo')
    provider.create
  end
  it 'calls rabbitmqctl to create' do
    provider.expects(:rabbitmqctl).with('delete_vhost', 'foo')
    provider.destroy
  end
end
