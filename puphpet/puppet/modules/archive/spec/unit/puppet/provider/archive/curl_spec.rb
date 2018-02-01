require 'spec_helper'

curl_provider = Puppet::Type.type(:archive).provider(:curl)

RSpec.describe curl_provider do
  it_behaves_like 'an archive provider', curl_provider

  describe '#download' do
    let(:name)      { '/tmp/example.zip' }
    let(:resource)  { Puppet::Type::Archive.new(resource_properties) }
    let(:provider)  { curl_provider.new(resource) }

    let(:default_options) do
      [
        'http://home.lan/example.zip',
        '-o',
        String,
        '-fsSL',
        '--max-redirs',
        5
      ]
    end

    before do
      allow(FileUtils).to receive(:mv)
      allow(provider).to receive(:curl)
    end

    context 'no extra properties specified' do
      let(:resource_properties) do
        {
          name: name,
          source: 'http://home.lan/example.zip'
        }
      end

      it 'calls curl with input, output and --max-redirects=5' do
        provider.download(name)
        expect(provider).to have_received(:curl).with(default_options)
      end
    end

    context 'username specified' do
      let(:resource_properties) do
        {
          name: name,
          source: 'http://home.lan/example.zip',
          username: 'foo'
        }
      end

      it 'calls curl with default options and username' do
        provider.download(name)
        expect(provider).to have_received(:curl).with(default_options << '--user' << 'foo')
      end
    end

    context 'username and password specified' do
      let(:resource_properties) do
        {
          name: name,
          source: 'http://home.lan/example.zip',
          username: 'foo',
          password: 'bar'
        }
      end

      it 'calls curl with default options and password' do
        provider.download(name)
        expect(provider).to have_received(:curl).with(default_options << '--user' << 'foo:bar')
      end
    end

    context 'allow_insecure true' do
      let(:resource_properties) do
        {
          name: name,
          source: 'http://home.lan/example.zip',
          allow_insecure: true
        }
      end

      it 'calls curl with default options and --insecure' do
        provider.download(name)
        expect(provider).to have_received(:curl).with(default_options << '--insecure')
      end
    end

    context 'cookie specified' do
      let(:resource_properties) do
        {
          name: name,
          source: 'http://home.lan/example.zip',
          cookie: 'foo=bar'
        }
      end

      it 'calls curl with default options cookie' do
        provider.download(name)
        expect(provider).to have_received(:curl).with(default_options << '--cookie' << 'foo=bar')
      end
    end

    context 'using proxy' do
      let(:resource_properties) do
        {
          name: name,
          source: 'http://home.lan/example.zip',
          proxy_server: 'https://home.lan:8080'
        }
      end

      it 'calls curl with proxy' do
        provider.download(name)
        expect(provider).to have_received(:curl).with(default_options << '--proxy' << 'https://home.lan:8080')
      end
    end

    describe '#checksum' do
      subject { provider.checksum }

      let(:url) { nil }
      let(:resource_properties) do
        {
          name: name,
          source: 'http://home.lan/example.zip'
        }
      end

      before do
        resource[:checksum_url] = url if url
      end

      context 'with a url' do
        let(:curl_params) do
          [
            'http://example.com/checksum',
            '-fsSL',
            '--max-redirs',
            5
          ]
        end

        let(:url) { 'http://example.com/checksum' }

        context 'responds with hash' do
          let(:remote_hash) { 'a0c38e1aeb175201b0dacd65e2f37e187657050a' }

          it 'parses checksum value' do
            allow(provider).to receive(:curl).with(curl_params).and_return("a0c38e1aeb175201b0dacd65e2f37e187657050a README.md\n")
            expect(provider.checksum).to eq('a0c38e1aeb175201b0dacd65e2f37e187657050a')
          end
        end
      end
    end

    describe 'custom options' do
      let(:resource_properties) do
        {
          name: name,
          source: 'http://home.lan/example.zip',
          download_options: ['--tlsv1']
        }
      end

      it 'calls curl with custom tls options' do
        provider.download(name)
        expect(provider).to have_received(:curl).with(default_options << '--tlsv1')
      end
    end
  end
end
