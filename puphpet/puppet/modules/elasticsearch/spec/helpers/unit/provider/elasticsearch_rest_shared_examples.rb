require 'json'
require 'spec_helper_rspec'
require 'webmock/rspec'

shared_examples 'REST API' do |resource_type, create_uri|
  describe 'instances' do
    context "with no #{resource_type}s" do
      it 'returns an empty list' do
        stub_request(:get, "http://localhost:9200/_#{resource_type}")
          .with(:headers => { 'Accept' => 'application/json' })
          .to_return(
            :status => 200,
            :body => '{}'
          )

        expect(described_class.instances).to eq([])
      end
    end
  end

  describe "multiple #{resource_type}s" do
    it "returns two #{resource_type}s" do
      stub_request(:get, "http://localhost:9200/_#{resource_type}")
        .with(:headers => { 'Accept' => 'application/json' })
        .to_return(
          :status => 200,
          :body => JSON.dump(json_1.merge(json_2))
        )

      expect(described_class.instances.map do |provider|
        provider.instance_variable_get(:@property_hash)
      end).to contain_exactly(example_1, example_2)
    end
  end

  describe 'basic authentication' do
    it 'authenticates' do
      stub_request(:get, "http://localhost:9200/_#{resource_type}")
        .with(
          :basic_auth => %w[elastic password],
          :headers => { 'Accept' => 'application/json' }
        )
        .to_return(
          :status => 200,
          :body => JSON.dump(json_1)
        )

      expect(described_class.api_objects(
        'http', true, 'localhost', '9200', 10, 'elastic', 'password'
      ).map do |provider|
        described_class.new(
          provider
        ).instance_variable_get(:@property_hash)
      end).to contain_exactly(example_1)
    end
  end

  describe 'https' do
    it 'uses ssl' do
      stub_request(:get, "https://localhost:9200/_#{resource_type}")
        .with(:headers => { 'Accept' => 'application/json' })
        .to_return(
          :status => 200,
          :body => JSON.dump(json_2)
        )

      expect(described_class.api_objects(
        'https', true, 'localhost', '9200', 10
      ).map do |provider|
        described_class.new(
          provider
        ).instance_variable_get(:@property_hash)
      end).to contain_exactly(example_2)
    end
  end

  describe 'flush' do
    it "creates #{resource_type}s" do
      stub_request(:put, "http://localhost:9200/#{create_uri}")
        .with(
          :headers => {
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
          },
          :body => bare_resource
        )
      stub_request(:get, "http://localhost:9200/_#{resource_type}")
        .with(:headers => { 'Accept' => 'application/json' })
        .to_return(:status => 200, :body => '{}')

      provider.flush
    end
  end
end # of describe puppet type
