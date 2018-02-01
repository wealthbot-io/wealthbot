require 'spec_helper_rspec'

describe Puppet::Type.type(:elasticsearch_service_file) do

  let(:resource_name) { '/usr/lib/systemd/system/elasticsearch-es-01.service' }

  describe 'attribute validation' do
    [
      :name,
      :defaults_location,
      :group,
      :instance,
      :homedir,
      :memlock,
      :nofile,
      :nproc,
      :package_name,
      :pid_dir,
      :user
    ].each do |param|
      it "should have a #{param} parameter" do
        expect(described_class.attrtype(param)).to eq(:param)
      end
    end

    [:content, :ensure].each do |prop|
      it "should have a #{prop} property" do
        expect(described_class.attrtype(prop)).to eq(:property)
      end
    end

    describe 'namevar validation' do
      it 'should have :name as its namevar' do
        expect(described_class.key_attributes).to eq([:name])
      end
    end

    describe 'content' do
      it 'should accept simple strings' do
        expect(described_class.new(
          :name => resource_name,
          :content => "Test\n"
        )[:content]).to eq(
          "Test\n"
        )
      end
    end

    describe 'ensure' do
      it 'should support present as a value for ensure' do
        expect { described_class.new(
          :name => resource_name,
          :ensure => :present,
          :content => {}
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
          :ensure => :foo,
          :content => {}
        ) }.to raise_error(Puppet::Error, /Invalid value/)
      end
    end
  end # of describing when validing values
end # of describe Puppet::Type
