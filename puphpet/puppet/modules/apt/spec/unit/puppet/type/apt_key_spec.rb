require 'spec_helper'
require 'puppet'

describe Puppet::Type.type(:apt_key) do
  context 'only namevar 32bit key id' do
    let(:resource) do
      Puppet::Type.type(:apt_key).new(
        id: 'EF8D349F',
      )
    end

    it 'id is set' do
      expect(resource[:id]).to eq 'EF8D349F'
    end

    it 'name is set to id' do
      expect(resource[:name]).to eq 'EF8D349F'
    end

    it 'keyserver is default' do
      expect(resource[:server]).to eq :'keyserver.ubuntu.com'
    end

    it 'source is not set' do
      expect(resource[:source]).to eq nil
    end

    it 'content is not set' do
      expect(resource[:content]).to eq nil
    end
  end

  context 'with a lowercase 32bit key id' do
    let(:resource) do
      Puppet::Type.type(:apt_key).new(
        id: 'ef8d349f',
      )
    end

    it 'id is set' do
      expect(resource[:id]).to eq 'EF8D349F'
    end
  end

  context 'with a 64bit key id' do
    let(:resource) do
      Puppet::Type.type(:apt_key).new(
        id: 'FFFFFFFFEF8D349F',
      )
    end

    it 'id is set' do
      expect(resource[:id]).to eq 'FFFFFFFFEF8D349F'
    end
  end

  context 'with a 0x formatted key id' do
    let(:resource) do
      Puppet::Type.type(:apt_key).new(
        id: '0xEF8D349F',
      )
    end

    it 'id is set' do
      expect(resource[:id]).to eq 'EF8D349F'
    end
  end

  context 'with a 0x formatted lowercase key id' do
    let(:resource) do
      Puppet::Type.type(:apt_key).new(
        id: '0xef8d349f',
      )
    end

    it 'id is set' do
      expect(resource[:id]).to eq 'EF8D349F'
    end
  end

  context 'with a 0x formatted 64bit key id' do
    let(:resource) do
      Puppet::Type.type(:apt_key).new(
        id: '0xFFFFFFFFEF8D349F',
      )
    end

    it 'id is set' do
      expect(resource[:id]).to eq 'FFFFFFFFEF8D349F'
    end
  end

  context 'with source' do
    let(:resource) do
      Puppet::Type.type(:apt_key).new(
        id: 'EF8D349F',
        source: 'http://apt.puppetlabs.com/pubkey.gpg',
      )
    end

    it 'source is set to the URL' do
      expect(resource[:source]).to eq 'http://apt.puppetlabs.com/pubkey.gpg'
    end
  end

  context 'with content' do
    let(:resource) do
      Puppet::Type.type(:apt_key).new(
        id: 'EF8D349F',
        content: 'http://apt.puppetlabs.com/pubkey.gpg',
      )
    end

    it 'content is set to the string' do
      expect(resource[:content]).to eq 'http://apt.puppetlabs.com/pubkey.gpg'
    end
  end

  context 'with keyserver' do
    let(:resource) do
      Puppet::Type.type(:apt_key).new(
        id: 'EF8D349F',
        server: 'http://keyring.debian.org',
      )
    end

    it 'keyserver is set to Debian' do
      expect(resource[:server]).to eq 'http://keyring.debian.org'
    end
  end

  context 'validation' do
    it 'raises an error if content and source are set' do
      expect {
        Puppet::Type.type(:apt_key).new(id: 'EF8D349F',
                                        source: 'http://apt.puppetlabs.com/pubkey.gpg',
                                        content: 'Completely invalid as a GPG key')
      }.to raise_error(%r{content and source are mutually exclusive})
    end

    it 'raises an error if a weird length key is used' do
      expect {
        Puppet::Type.type(:apt_key).new(id: 'FEF8D349F',
                                        source: 'http://apt.puppetlabs.com/pubkey.gpg',
                                        content: 'Completely invalid as a GPG key')
      }.to raise_error(%r{Valid values match})
    end

    it 'raises an error when an invalid URI scheme is used in source' do
      expect {
        Puppet::Type.type(:apt_key).new(id: 'EF8D349F',
                                        source: 'hkp://pgp.mit.edu')
      }.to raise_error(%r{Valid values match})
    end

    it 'allows the http URI scheme in source' do
      expect {
        Puppet::Type.type(:apt_key).new(id: 'EF8D349F',
                                        source: 'http://pgp.mit.edu')
      }.not_to raise_error
    end

    it 'allows the http URI with username and password' do
      expect {
        Puppet::Type.type(:apt_key).new(id: '4BD6EC30',
                                        source: 'http://testme:Password2@pgp.mit.edu')
      }.not_to raise_error
    end

    it 'allows the https URI scheme in source' do
      expect {
        Puppet::Type.type(:apt_key).new(id: 'EF8D349F',
                                        source: 'https://pgp.mit.edu')
      }.not_to raise_error
    end

    it 'allows the https URI with username and password' do
      expect {
        Puppet::Type.type(:apt_key).new(id: 'EF8D349F',
                                        source: 'https://testme:Password2@pgp.mit.edu')
      }.not_to raise_error
    end

    it 'allows the ftp URI scheme in source' do
      expect {
        Puppet::Type.type(:apt_key).new(id: 'EF8D349F',
                                        source: 'ftp://pgp.mit.edu')
      }.not_to raise_error
    end

    it 'allows an absolute path in source' do
      expect {
        Puppet::Type.type(:apt_key).new(id: 'EF8D349F',
                                        source: '/path/to/a/file')
      }.not_to raise_error
    end

    it 'allows 5-digit ports' do
      expect {
        Puppet::Type.type(:apt_key).new(id: 'EF8D349F',
                                        source: 'http://pgp.mit.edu:12345/key')
      }.not_to raise_error
    end

    it 'allows 5-digit ports when using key servers' do
      expect {
        Puppet::Type.type(:apt_key).new(id: 'EF8D349F',
                                        server: 'http://pgp.mit.edu:12345')
      }.not_to raise_error
    end
  end
end
