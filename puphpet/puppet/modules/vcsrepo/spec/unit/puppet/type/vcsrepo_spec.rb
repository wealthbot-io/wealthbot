#! /usr/bin/env ruby
require 'spec_helper'

describe Puppet::Type.type(:vcsrepo) do
  before :each do
    Puppet::Type.type(:vcsrepo).stubs(:defaultprovider).returns(providerclass)
  end

  let(:providerclass) do
    described_class.provide(:fake_vcsrepo_provider) do
      attr_accessor :property_hash
      def create; end

      def destroy; end

      def exists?
        get(:ensure) != :absent
      end
      mk_resource_methods
      has_features :include_paths
    end
  end

  let(:provider) do
    providerclass.new(name: 'fake-vcs')
  end

  let(:resource) do
    described_class.new(name: '/repo',
                        ensure: :present,
                        source: 'http://example.com/repo/',
                        provider: provider)
  end

  let(:ensureprop) do
    resource.property(:ensure)
  end

  let(:sourceprop) do
    resource.property(:source)
  end

  properties = [:ensure, :source]

  properties.each do |property|
    it "should have a #{property} property" do
      expect(described_class.attrclass(property).ancestors).to be_include(Puppet::Property)
    end
  end

  parameters = [:ensure]

  parameters.each do |parameter|
    it "should have a #{parameter} parameter" do
      expect(described_class.attrclass(parameter).ancestors).to be_include(Puppet::Parameter)
    end
  end

  describe "with an include path that starts with a '/'" do
    it 'raises a Puppet::ResourceError error' do
      expect {
        resource[:includes] = ['/path1/file1', '/path2/file2']
      }.to raise_error(Puppet::ResourceError, %r{Include path '.*' starts with a '/'})
    end
  end

  describe 'when using a provider that adds/removes a trailing / to the source' do
    it 'stays in sync when it leaves it as-is' do
      sourceprop.should = 'http://example.com/repo/'
      expect(sourceprop.safe_insync?('http://example.com/repo/')).to eq(true)
    end
    it 'stays in sync when it adds a slash' do
      sourceprop.should = 'http://example.com/repo'
      expect(sourceprop.safe_insync?('http://example.com/repo/')).to eq(true)
    end
    it 'stays in sync when it removes a slash' do
      sourceprop.should = 'http://example.com/repo/'
      expect(sourceprop.safe_insync?('http://example.com/repo')).to eq(true)
    end
    it 'is out of sync with a different source' do
      sourceprop.should = 'http://example.com/repo/asdf'
      expect(sourceprop.safe_insync?('http://example.com/repo')).to eq(false)
    end
  end

  describe 'default resource with required params' do
    it 'has a valid name parameter' do
      expect(resource[:name]).to eq('/repo')
    end

    it 'has ensure set to present' do
      expect(resource[:ensure]).to eq(:present)
    end

    it 'has path set to /repo' do
      expect(resource[:path]).to eq('/repo')
    end

    defaults = {
      owner: nil,
      group: nil,
      user: nil,
      revision: nil,
    }

    defaults.each_pair do |param, value|
      it "should have #{param} parameter set to #{value}" do
        expect(resource[param]).to eq(value)
      end
    end
  end

  describe 'when changing the ensure' do
    it 'is in sync if it is :absent and should be :absent' do
      ensureprop.should = :absent
      expect(ensureprop.safe_insync?(:absent)).to eq(true)
    end

    it 'is in sync if it is :present and should be :present' do
      ensureprop.should = :present
      expect(ensureprop.safe_insync?(:present)).to eq(true)
    end

    it 'is out of sync if it is :absent and should be :present' do
      ensureprop.should = :present
      expect(ensureprop.safe_insync?(:absent)).not_to eq(true)
    end

    it 'is out of sync if it is :present and should be :absent' do
      ensureprop.should = :absent
      expect(ensureprop.safe_insync?(:present)).not_to eq(true)
    end
  end

  describe 'when running the type it should autorequire packages' do
    let(:catalog) { Puppet::Resource::Catalog.new }
    let(:resource) { described_class.new(name: '/foo', provider: provider) }

    before :each do
      ['git', 'git-core', 'mercurial', 'subversion'].each do |pkg|
        catalog.add_resource(Puppet::Type.type(:package).new(name: pkg))
      end
    end

    it 'requires package packages' do
      catalog.add_resource(resource)
      req = resource.autorequire
      expect(req.size).to eq(4)
    end
  end
end
