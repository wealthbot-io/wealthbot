require 'spec_helper_rspec'

describe Puppet::Type.type(:elasticsearch_keystore) do
  let(:resource_name) { 'es-01' }

  describe 'validating attributes' do
    %i[configdir instance purge].each do |param|
      it "should have a `#{param}` parameter" do
        expect(described_class.attrtype(param)).to eq(:param)
      end
    end

    %i[ensure settings].each do |prop|
      it "should have a #{prop} property" do
        expect(described_class.attrtype(prop)).to eq(:property)
      end
    end

    describe 'namevar validation' do
      it 'should have :instance as its namevar' do
        expect(described_class.key_attributes).to eq([:instance])
      end
    end
  end # of describe validating attributes

  describe 'when validating values' do
    describe 'ensure' do
      it 'should support present as a value for ensure' do
        expect { described_class.new(
          :name => resource_name,
          :ensure => :present
        ) }.to_not raise_error
      end

      it 'should support absent as a value for ensure' do
        expect { described_class.new(
          :name => resource_name,
          :ensure => :absent
        ) }.to_not raise_error
      end

      it 'should not support other values' do
        expect { described_class.new(
          :name => resource_name,
          :ensure => :foo
        ) }.to raise_error(Puppet::Error, /Invalid value/)
      end
    end

    describe 'settings' do
      [{ 'node.name' => 'foo' }, ['node.name', 'node.data']].each do |setting|
        it "accepts #{setting.class}s" do
          expect { described_class.new(
            :name => resource_name,
            :settings => setting
          ) }.to_not raise_error
        end
      end

      describe 'insync' do
        it 'only checks lists or hash key membership' do
          expect(described_class.new(
            :name => resource_name,
            :settings => { 'node.name' => 'foo', 'node.data' => true }
          ).property(:settings).insync?(
            %w[node.name node.data]
          )).to be true
        end

        context 'purge' do
          it 'defaults to not purge values' do
            expect(described_class.new(
              :name => resource_name,
              :settings => { 'node.name' => 'foo', 'node.data' => true }
            ).property(:settings).insync?(
              %w[node.name node.data node.attr.rack]
            )).to be true
          end

          it 'respects the purge parameter' do
            expect(described_class.new(
              :name => resource_name,
              :settings => { 'node.name' => 'foo', 'node.data' => true },
              :purge => true
            ).property(:settings).insync?(
              %w[node.name node.data node.attr.rack]
            )).to be false
          end
        end
      end
    end
  end # of describing when validating values
end # of describe Puppet::Type
