require_relative 'shared_examples'

provider_class = Puppet::Type.type(:elasticsearch_plugin).provider(:plugin)

describe provider_class do
  let(:resource_name) { 'lmenezes/elasticsearch-kopf' }
  let(:resource) do
    Puppet::Type.type(:elasticsearch_plugin).new(
      :name     => resource_name,
      :ensure   => :present,
      :provider => 'plugin'
    )
  end
  let(:provider) do
    provider = provider_class.new
    provider.resource = resource
    provider
  end
  let(:klass) { provider_class }

  include_examples 'plugin provider', '1.7.0'
  include_examples 'plugin provider', '2.0.0'
end
