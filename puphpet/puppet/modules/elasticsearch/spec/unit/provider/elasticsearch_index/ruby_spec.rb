require_relative '../../../helpers/unit/provider/elasticsearch_rest_shared_examples'

describe Puppet::Type.type(:elasticsearch_index).provider(:ruby) do
  let(:name) { 'test-index' }

  let(:example_1) do
    {
      :name => 'index-one',
      :ensure => :present,
      :provider => :ruby,
      :settings => {
        'index' => {
          'creation_date' => 1_487_354_196_301,
          'number_of_replicas' => 1,
          'number_of_shards' => 5,
          'provided_name' => 'a',
          'routing' => {
            'allocation' => {
              'include' => {
                'size' => 'big'
              }
            }
          },
          'store' => {
            'type' => 'niofs'
          },
          'uuid' => 'vtJrcgyeRviqllRakSlrSw',
          'version' => {
            'created' => 5_020_199
          }
        }
      }
    }
  end

  let(:json_1) do
    {
      'index-one' => {
        'settings' => {
          'index' => {
            'creation_date' => 1_487_354_196_301,
            'number_of_replicas' => 1,
            'number_of_shards' => 5,
            'provided_name' => 'a',
            'routing' => {
              'allocation' => {
                'include' => {
                  'size' => 'big'
                }
              }
            },
            'store' => {
              'type' => 'niofs'
            },
            'uuid' => 'vtJrcgyeRviqllRakSlrSw',
            'version' => {
              'created' => 5_020_199
            }
          }
        }
      }
    }
  end

  let(:example_2) do
    {
      :name => 'index-two',
      :ensure => :present,
      :provider => :ruby,
      :settings => {
        'index' => {
          'creation_date' => 1_487_354_196_301,
          'number_of_replicas' => 1,
          'number_of_shards' => 5,
          'provided_name' => 'a',
          'uuid' => 'vtJrcgyeRviqllRakSlrSw',
          'version' => {
            'created' => 5_020_199
          }
        }
      }
    }
  end

  let(:json_2) do
    {
      'index-two' => {
        'settings' => {
          'index' => {
            'creation_date' => '1487354196301',
            'number_of_replicas' => 1,
            'number_of_shards' => 5,
            'provided_name' => 'a',
            'uuid' => 'vtJrcgyeRviqllRakSlrSw',
            'version' => {
              'created' => '5020199'
            }
          }
        }
      }
    }
  end

  let(:bare_resource) do
    JSON.dump(
      'index' => {
        'number_of_replicas' => 0
      }
    )
  end

  let(:resource) { Puppet::Type::Elasticsearch_index.new props }
  let(:provider) { described_class.new resource }
  let(:props) do
    {
      :name => name,
      :settings => {
        'index' => {
          'number_of_replicas' => 0
        }
      }
    }
  end

  include_examples 'REST API', 'all/_settings', 'test-index/_settings'
end
