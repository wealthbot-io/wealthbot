require 'spec_helper'

wget_provider = Puppet::Type.type(:archive).provider(:wget)

RSpec.describe wget_provider do
  it_behaves_like 'an archive provider', wget_provider

  describe '#download' do
    let(:name)      { '/tmp/example.zip' }
    let(:resource)  { Puppet::Type::Archive.new(resource_properties) }
    let(:provider)  { wget_provider.new(resource) }
    let(:execution) { Puppet::Util::Execution }

    let(:default_options) do
      [
        'wget',
        'http://home.lan/example.zip',
        '-O',
        '/tmp/example.zip',
        '--max-redirect=5'
      ]
    end

    before do
      allow(FileUtils).to receive(:mv)
      allow(execution).to receive(:execute)
    end

    context 'no extra properties specified' do
      let(:resource_properties) do
        {
          name: name,
          source: 'http://home.lan/example.zip'
        }
      end

      it 'calls wget with input, output and --max-redirects=5' do
        provider.download(name)
        expect(execution).to have_received(:execute).with(default_options.join(' '))
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

      it 'calls wget with default options and username' do
        provider.download(name)
        expect(execution).to have_received(:execute).with([default_options, '--user=foo'].join(' '))
      end
    end

    context 'password specified' do
      let(:resource_properties) do
        {
          name: name,
          source: 'http://home.lan/example.zip',
          password: 'foo'
        }
      end

      it 'calls wget with default options and password' do
        provider.download(name)
        expect(execution).to have_received(:execute).with([default_options, '--password=foo'].join(' '))
      end
    end

    context 'cookie specified' do
      let(:resource_properties) do
        {
          name: name,
          source: 'http://home.lan/example.zip',
          cookie: 'foo'
        }
      end

      it 'calls wget with default options and header containing cookie' do
        provider.download(name)
        expect(execution).to have_received(:execute).with([default_options, '--header="Cookie: foo"'].join(' '))
      end
    end

    context 'proxy specified' do
      let(:resource_properties) do
        {
          name: name,
          source: 'http://home.lan/example.zip',
          proxy_server: 'https://home.lan:8080'
        }
      end

      it 'calls wget with default options and header containing cookie' do
        provider.download(name)
        expect(execution).to have_received(:execute).with([default_options, '-e use_proxy=yes', '-e https_proxy=https://home.lan:8080'].join(' '))
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

      it 'calls wget with default options and --no-check-certificate' do
        provider.download(name)
        expect(execution).to have_received(:execute).with([default_options, '--no-check-certificate'].join(' '))
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
        let(:wget_params) do
          [
            'wget',
            '-qO-',
            'http://example.com/checksum',
            '--max-redirect=5'
          ]
        end

        let(:url) { 'http://example.com/checksum' }

        context 'responds with hash' do
          let(:remote_hash) { 'a0c38e1aeb175201b0dacd65e2f37e187657050a' }

          it 'parses checksum value' do
            allow(Puppet::Util::Execution).to receive(:execute).with(wget_params.join(' ')).and_return("a0c38e1aeb175201b0dacd65e2f37e187657050a README.md\n")
            expect(provider.checksum).to eq('a0c38e1aeb175201b0dacd65e2f37e187657050a')
          end
        end
      end
    end
  end
end
