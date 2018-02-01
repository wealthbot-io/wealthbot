require 'spec_helper'

describe Puppet::Type.type(:rabbitmq_policy).provider(:rabbitmqctl) do
  let(:resource) do
    Puppet::Type.type(:rabbitmq_policy).new(
      name: 'ha-all@/',
      pattern: '.*',
      definition: {
        'ha-mode' => 'all'
      }
    )
  end
  let(:provider) { described_class.new(resource) }

  after do
    described_class.instance_variable_set(:@policies, nil)
  end

  context 'has "@" in policy name' do
    let(:resource) do
      Puppet::Type.type(:rabbitmq_policy).new(
        name: 'ha@home@/',
        pattern: '.*',
        definition: {
          'ha-mode' => 'all'
        },
        provider: described_class.name
      )
    end
    let(:provider) { described_class.new(resource) }

    it do
      expect(provider.should_policy).to eq('ha@home')
    end

    it do
      expect(provider.should_vhost).to eq('/')
    end
  end

  it 'fails with invalid output from list' do
    provider.class.expects(:rabbitmqctl).with('-q', 'status').returns '{rabbit,"RabbitMQ","3.1.5"}'
    provider.class.expects(:rabbitmqctl).with('list_policies', '-q', '-p', '/').returns 'foobar'
    expect { provider.exists? }.to raise_error(Puppet::Error, %r{cannot parse line from list_policies})
  end

  context 'with RabbitMQ version >=3.7.0' do
    it 'matches policies from list' do
      provider.class.expects(:rabbitmq_version).returns '3.7.0'
      provider.class.expects(:rabbitmqctl).with('list_policies', '-q', '-p', '/').returns <<-EOT
/ ha-all .* all {"ha-mode":"all","ha-sync-mode":"automatic"} 0
/ test .* exchanges {"ha-mode":"all"} 0
EOT
      expect(provider.exists?).to eq(applyto: 'all',
                                     pattern: '.*',
                                     priority: '0',
                                     definition: {
                                       'ha-mode'      => 'all',
                                       'ha-sync-mode' => 'automatic'
                                     })
    end
  end

  context 'with RabbitMQ version >=3.2.0 and < 3.7.0' do
    it 'matches policies from list' do
      provider.class.expects(:rabbitmq_version).returns '3.6.9'
      provider.class.expects(:rabbitmqctl).with('list_policies', '-q', '-p', '/').returns <<-EOT
/ ha-all all .* {"ha-mode":"all","ha-sync-mode":"automatic"} 0
/ test exchanges .* {"ha-mode":"all"} 0
EOT
      expect(provider.exists?).to eq(applyto: 'all',
                                     pattern: '.*',
                                     priority: '0',
                                     definition: {
                                       'ha-mode'      => 'all',
                                       'ha-sync-mode' => 'automatic'
                                     })
    end
  end

  context 'with RabbitMQ version <3.2.0' do
    it 'matches policies from list (<3.2.0)' do
      provider.class.expects(:rabbitmq_version).returns '3.1.5'
      provider.class.expects(:rabbitmqctl).with('list_policies', '-q', '-p', '/').returns <<-EOT
/ ha-all .* {"ha-mode":"all","ha-sync-mode":"automatic"} 0
/ test .* {"ha-mode":"all"} 0
EOT
      expect(provider.exists?).to eq(applyto: 'all',
                                     pattern: '.*',
                                     priority: '0',
                                     definition: {
                                       'ha-mode'      => 'all',
                                       'ha-sync-mode' => 'automatic'
                                     })
    end
  end

  it 'does not match an empty list' do
    provider.class.expects(:rabbitmqctl).with('-q', 'status').returns '{rabbit,"RabbitMQ","3.1.5"}'
    provider.class.expects(:rabbitmqctl).with('list_policies', '-q', '-p', '/').returns ''
    expect(provider.exists?).to eq(nil)
  end

  it 'destroys policy' do
    provider.expects(:rabbitmqctl).with('clear_policy', '-p', '/', 'ha-all')
    provider.destroy
  end

  it 'onlies call set_policy once (<3.2.0)' do
    provider.class.expects(:rabbitmq_version).returns '3.1.0'
    provider.resource[:priority] = '10'
    provider.resource[:applyto] = 'exchanges'
    provider.expects(:rabbitmqctl).with('set_policy',
                                        '-p', '/',
                                        'ha-all',
                                        '.*',
                                        '{"ha-mode":"all"}',
                                        '10').once
    provider.priority = '10'
    provider.applyto = 'exchanges'
  end

  it 'onlies call set_policy once (>=3.2.0)' do
    provider.class.expects(:rabbitmq_version).returns '3.2.0'
    provider.resource[:priority] = '10'
    provider.resource[:applyto] = 'exchanges'
    provider.expects(:rabbitmqctl).with('set_policy',
                                        '-p', '/',
                                        '--priority', '10',
                                        '--apply-to', 'exchanges',
                                        'ha-all',
                                        '.*',
                                        '{"ha-mode":"all"}').once
    provider.priority = '10'
    provider.applyto = 'exchanges'
  end
end
