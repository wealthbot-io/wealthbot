require_relative 'shared_examples'

provider_class = Puppet::Type.type(:elasticsearch_plugin).provider(:elasticsearch_plugin)

describe provider_class do
  let(:resource_name) { 'lmenezes/elasticsearch-kopf' }
  let(:resource) do
    Puppet::Type.type(:elasticsearch_plugin).new(
      :name     => resource_name,
      :ensure   => :present,
      :provider => 'elasticsearch_plugin'
    )
  end
  let(:provider) do
    provider = provider_class.new
    provider.resource = resource
    provider
  end
  let(:shortname) { provider.plugin_name(resource_name) }
  let(:klass) { provider_class }

  include_examples 'plugin provider', '5.0.1'
end
