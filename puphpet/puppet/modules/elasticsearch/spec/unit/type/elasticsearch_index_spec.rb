require_relative '../../helpers/unit/type/elasticsearch_rest_shared_examples'

# rubocop:disable Metrics/BlockLength
describe Puppet::Type.type(:elasticsearch_index) do
  let(:resource_name) { 'test-index' }

  include_examples 'REST API types', 'index', :settings

  describe 'settings' do
    let(:resource) do
      described_class.new(
        :name => resource_name,
        :ensure => 'present',
        :settings => {
          'index' => {
            'number_of_replicas' => 0
          }
        }
      )
    end

    let(:settings) { resource.property(:settings) }

    describe 'insync?' do
      describe 'synced properties' do
        let(:is_settings) do
          {
            'index' => {
              'creation_date' => '1487354196301',
              'number_of_replicas' => 0,
              'number_of_shards' => 5,
              'provided_name' => 'a',
              'uuid' => 'vtjrcgyerviqllrakslrsw',
              'version' => {
                'created' => '5020199'
              }
            }
          }
        end

        it 'only enforces defined settings' do
          expect(settings.insync?(is_settings)).to be_truthy
        end
      end

      describe 'out-of-sync properties' do
        let(:is_settings) do
          {
            'index' => {
              'creation_date' => '1487354196301',
              'number_of_replicas' => 1,
              'number_of_shards' => 5,
              'provided_name' => 'a',
              'uuid' => 'vtjrcgyerviqllrakslrsw',
              'version' => {
                'created' => '5020199'
              }
            }
          }
        end

        it 'detects out-of-sync nested values' do
          expect(settings.insync?(is_settings)).to be_falsy
        end
      end
    end
  end
end
