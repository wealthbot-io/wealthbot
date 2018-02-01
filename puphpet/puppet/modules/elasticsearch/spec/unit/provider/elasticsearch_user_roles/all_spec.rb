require 'spec_helper_rspec'

[:shield, :xpack].each do |provider|
  describe Puppet::Type.type(:elasticsearch_user_roles)
    .provider(provider) do

    describe 'instances' do
      it 'should have an instance method' do
        expect(described_class).to respond_to :instances
      end

      context 'without roles' do
        it 'should return no resources' do
          expect(described_class.parse("\n")).to eq([])
        end
      end

      context 'with one user' do
        it 'should return one resource' do
          expect(described_class.parse(%q{
            admin:elastic
            power_user:elastic
          })[0]).to eq({
            :name => 'elastic',
            :roles => ['admin', 'power_user']
          })
        end
      end

      context 'with multiple users' do
        it 'should return three resources' do
          expect(described_class.parse(%q{
            admin:elastic
            logstash:user
            kibana:kibana
          }).length).to eq(3)
        end
      end
    end # of describe instances

    describe 'prefetch' do
      it 'should have a prefetch method' do
        expect(described_class).to respond_to :prefetch
      end
    end
  end # of describe puppet type
end
