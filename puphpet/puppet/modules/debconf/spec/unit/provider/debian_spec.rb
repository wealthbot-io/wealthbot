require 'spec_helper'

provider_class = Puppet::Type.type(:debconf).provider(:debian)

describe provider_class do
  let(:name) { 'foo' }

  let(:resource) do
    Puppet::Type.type(:debconf).new(
      :name     => name,
      :provider => 'debian',
    )
  end

  let(:provider) do
    provider = provider_class.new
    provider.resource = resource
    provider
  end

  it "should be the default provider on :osfamily => Debian" do
    Facter.expects(:value).with(:osfamily).returns("Debian")
    expect(described_class.default?).to be_truthy
  end
end
