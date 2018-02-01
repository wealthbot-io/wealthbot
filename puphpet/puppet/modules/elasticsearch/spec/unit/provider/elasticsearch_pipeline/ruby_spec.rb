require_relative '../../../helpers/unit/provider/elasticsearch_rest_shared_examples'

describe Puppet::Type.type(:elasticsearch_pipeline).provider(:ruby) do
  let(:example_1) do
    {
      :name => 'foo',
      :ensure => :present,
      :provider => :ruby,
      :content => {
        'description' => 'Sets the foo field to "bar"',
        'processors' => [{
          'set' => {
            'field' => 'foo',
            'value' => 'bar'
          }
        }]
      }
    }
  end

  let(:json_1) do
    {
      'foo' => {
        'description' => 'Sets the foo field to "bar"',
        'processors' => [{
          'set' => {
            'field' => 'foo',
            'value' => 'bar'
          }
        }]
      }
    }
  end

  let(:example_2) do
    {
      :name => 'baz',
      :ensure => :present,
      :provider => :ruby,
      :content => {
        'description' => 'A pipeline that never gives you up',
        'processors' => [{
          'set' => {
            'field' => 'firstname',
            'value' => 'rick'
          }
        }, {
          'set' => {
            'field' => 'lastname',
            'value' => 'astley'
          }
        }]
      }
    }
  end

  let(:json_2) do
    {
      'baz' => {
        'description' => 'A pipeline that never gives you up',
        'processors' => [{
          'set' => {
            'field' => 'firstname',
            'value' => 'rick'
          }
        }, {
          'set' => {
            'field' => 'lastname',
            'value' => 'astley'
          }
        }]
      }
    }
  end

  let(:bare_resource) do
    JSON.dump(
      'description' => 'Empty pipeline',
      'processors' => []
    )
  end

  let(:resource) { Puppet::Type::Elasticsearch_pipeline.new props }
  let(:provider) { described_class.new resource }
  let(:props) do
    {
      :name => 'foo',
      :content => {
        'description' => 'Empty pipeline',
        'processors' => []
      }
    }
  end

  include_examples 'REST API', 'ingest/pipeline', '_ingest/pipeline/foo'
end
