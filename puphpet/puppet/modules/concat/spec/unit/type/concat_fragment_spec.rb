require 'spec_helper'

describe Puppet::Type.type(:concat_fragment) do
  let(:resource) do
    described_class.new(name: 'foo', target: 'bar', content: 'baz')
  end

  describe 'key attributes' do
    let(:subject) { described_class.key_attributes }

    it 'contain only :name' do
      is_expected.to eq([:name])
    end
  end

  describe 'parameter :target' do
    it_behaves_like 'a parameter that accepts only string values', :target
  end

  describe 'parameter :content' do
    it_behaves_like 'a parameter that accepts only string values', :content
  end

  describe 'parameter :source' do
    it 'accepts a string value' do
      resource[:source] = 'foo'
      expect(resource[:source]).to eq('foo')
    end

    it 'accepts an array value' do
      resource[:source] = %w[foo bar]
      expect(resource[:source]).to eq(%w[foo bar])
    end

    it 'does not accept a hash value' do
      expect { resource[:source] = { foo: 'bar' } }.to raise_error(%r{must be a String or Array})
    end

    it 'does not accept an integer value' do
      expect { resource[:source] = 9001 }.to raise_error(%r{must be a String or Array})
    end

    it 'does not accept a boolean true value' do
      expect { resource[:source] = true }.to raise_error(%r{must be a String or Array})
    end

    it 'does not accept a boolean false value' do
      expect { resource[:source] = false }.to raise_error(%r{must be a String or Array})
    end
  end

  describe 'parameter :order' do
    it 'accepts a string value' do
      resource[:order] = 'foo'
      expect(resource[:order]).to eq('foo')
    end

    it 'accepts an integer value' do
      resource[:order] = 9001
      expect(resource[:order]).to eq(9001)
    end

    it 'does not accept an array value' do
      expect { resource[:order] = %w[foo bar] }.to raise_error(%r{is not a string or integer})
    end

    it 'does not accept a hash value' do
      expect { resource[:order] = { foo: 'bar' } }.to raise_error(%r{is not a string or integer})
    end

    it 'does not accept a boolean true value' do
      expect { resource[:order] = true }.to raise_error(%r{is not a string or integer})
    end

    it 'does not accept a boolean false value' do
      expect { resource[:order] = false }.to raise_error(%r{is not a string or integer})
    end

    it 'does not accept a string with ":" in it/' do
      expect { resource[:order] = ':foo' }.to raise_error(%r{Order cannot contain})
    end

    it 'does not accept a string with "\n" in it/' do
      expect { resource[:order] = "\nfoo" }.to raise_error(%r{Order cannot contain})
    end

    it 'does not accept a string with "/" in it/' do
      expect { resource[:order] = '/foo' }.to raise_error(%r{Order cannot contain})
    end
  end

  context 'without a value set for :target or :tag' do
    it 'throws an error' do
      expect { described_class.new(name: 'foo', content: 'baz') }.to raise_error(%r{No 'target' or 'tag' set})
    end
  end

  context 'without a value set for both :content and :source' do
    it 'throws an error' do
      expect { described_class.new(name: 'foo', target: 'bar') }.to raise_error(%r{Set either 'source' or 'content'})
    end
  end

  context 'with a value set for both :content and :source' do
    it 'throws an error' do
      expect {
        described_class.new(name: 'foo', target: 'bar', content: 'baz', source: 'qux')
      }.to raise_error(%r{Can't use 'source' and 'content' at the same time})
    end
  end
end
