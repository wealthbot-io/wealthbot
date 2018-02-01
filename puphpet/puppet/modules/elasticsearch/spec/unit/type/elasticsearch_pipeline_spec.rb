require_relative '../../helpers/unit/type/elasticsearch_rest_shared_examples'

describe Puppet::Type.type(:elasticsearch_pipeline) do
  let(:resource_name) { 'test_pipeline' }

  include_examples 'REST API types', 'pipeline', :content
end
