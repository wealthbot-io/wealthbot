require 'spec_helper'
require 'puppet'

describe Puppet::Type.type(:archive) do
  let(:resource) do
    Puppet::Type.type(:archive).new(
      path: '/tmp/example.zip',
      source: 'http://home.lan/example.zip'
    )
  end

  context 'resource defaults' do
    it { expect(resource[:path]).to eq '/tmp/example.zip' }
    it { expect(resource[:name]).to eq '/tmp/example.zip' }
    it { expect(resource[:filename]).to eq 'example.zip' }
    it { expect(resource[:extract]).to eq :false }
    it { expect(resource[:cleanup]).to eq :true }
    it { expect(resource[:checksum_type]).to eq :none }
    it { expect(resource[:digest_type]).to eq nil }
    it { expect(resource[:checksum_verify]).to eq :true }
    it { expect(resource[:extract_flags]).to eq :undef }
    it { expect(resource[:allow_insecure]).to eq false }
    it { expect(resource[:download_options]).to eq nil }
    it { expect(resource[:temp_dir]).to eq nil }
  end

  it 'verify resource[:path] is absolute filepath' do
    expect do
      resource[:path] = 'relative/file'
    end.to raise_error(Puppet::Error, %r{archive path must be absolute: })
  end

  it 'verify resource[:temp_dir] is absolute filetemp_dir' do
    expect do
      resource[:temp_dir] = 'relative/file'
    end.to raise_error(Puppet::Error, %r{Invalid temp_dir})
  end

  describe 'on posix', if: Puppet.features.posix? do
    it 'accepts valid resource[:source]' do
      expect do
        resource[:source] = 'http://home.lan/example.zip'
        resource[:source] = 'https://home.lan/example.zip'
        resource[:source] = 'ftp://home.lan/example.zip'
        resource[:source] = 's3://home.lan/example.zip'
        resource[:source] = '/tmp/example.zip'
      end.not_to raise_error
    end

    %w[
      afp://home.lan/example.zip
      \tmp
      D:/example.zip
    ].each do |s|
      it 'rejects invalid resource[:source]' do
        expect do
          resource[:source] = s
        end.to raise_error(Puppet::Error, %r{invalid source url: })
      end
    end
  end

  describe 'on windows', if: Puppet.features.microsoft_windows? do
    it 'accepts valid windows resource[:source]' do
      expect do
        resource[:source] = 'D:/example.zip'
      end.not_to raise_error
    end

    %w[
      /tmp/example.zip
      \Z:
    ].each do |s|
      it 'rejects invalid windows resource[:source]' do
        expect do
          resource[:source] = s
        end.to raise_error(Puppet::Error, %r{invalid source url: })
      end
    end
  end

  %w[
    557e2ebb67b35d1fddff18090b6bc26b
    557e2ebb67b35d1fddff18090b6bc26557e2ebb67b35d1fddff18090b6bc26bb
  ].each do |cs|
    it 'accepts valid resource[:checksum]' do
      expect do
        resource[:checksum] = cs
      end.not_to raise_error
    end
  end

  %w[
    z57e2ebb67b35d1fddff18090b6bc26b
    557e
  ].each do |cs|
    it 'rejects bad checksum' do
      expect do
        resource[:checksum] = cs
      end.to raise_error(Puppet::Error, %r{Invalid value})
    end
  end

  it 'accepts valid resource[:checksum_type]' do
    expect do
      [:none, :md5, :sha1, :sha2, :sha256, :sha384, :sha512].each do |type|
        resource[:checksum_type] = type
      end
    end.not_to raise_error
  end

  it 'rejects invalid resource[:checksum_type]' do
    expect do
      resource[:checksum_type] = :crc32
    end.to raise_error(Puppet::Error, %r{Invalid value})
  end

  it 'verify resource[:allow_insecure] is valid' do
    expect do
      [:true, :false, :yes, :no].each do |type|
        resource[:allow_insecure] = type
      end
    end.not_to raise_error
  end

  it 'verify resource[:download_options] is valid' do
    expect do
      ['--tlsv1', ['--region', 'eu-central-1']].each do |type|
        resource[:download_options] = type
      end
    end.not_to raise_error
  end

  describe 'archive autorequire' do
    let(:file_resource) { Puppet::Type.type(:file).new(name: '/tmp') }
    let(:archive_resource) do
      described_class.new(
        path: '/tmp/example.zip',
        source: 'http://home.lan/example.zip'
      )
    end

    let(:auto_req) do
      catalog = Puppet::Resource::Catalog.new
      catalog.add_resource file_resource
      catalog.add_resource archive_resource

      archive_resource.autorequire
    end

    it 'creates relationship' do
      expect(auto_req.size).to be 1
    end
    it 'links to archive resource' do
      expect(auto_req[0].target).to eql archive_resource
    end
    it 'autorequires parent directory' do
      expect(auto_req[0].source).to eql file_resource
    end
  end
end
