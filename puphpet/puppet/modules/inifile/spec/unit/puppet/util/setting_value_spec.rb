require 'spec_helper'
require 'puppet/util/setting_value'

describe Puppet::Util::SettingValue do
  describe 'space subsetting separator' do
    INIT_VALUE_SPACE = '"-Xmx192m -XX:+HeapDumpOnOutOfMemoryError -XX:HeapDumpPath=/var/log/pe-puppetdb/puppetdb-oom.hprof"'.freeze

    let(:setting_value) { described_class.new(INIT_VALUE_SPACE, ' ') }

    it 'gets the original value' do
      expect(setting_value.get_value).to eq(INIT_VALUE_SPACE)
    end

    it 'gets the correct value' do
      expect(setting_value.get_subsetting_value('-Xmx')).to eq('192m')
    end

    it 'adds a new value #correct' do
      setting_value.add_subsetting('-Xms', '256m')
      expect(setting_value.get_subsetting_value('-Xms')).to eq('256m')
    end

    it 'adds a new value #original' do
      setting_value.add_subsetting('-Xms', '256m')
      expect(setting_value.get_value).to eq(INIT_VALUE_SPACE[0, INIT_VALUE_SPACE.length - 1] + ' -Xms256m"')
    end

    it 'changes existing value' do
      setting_value.add_subsetting('-Xmx', '512m')
      expect(setting_value.get_subsetting_value('-Xmx')).to eq('512m')
    end

    it 'removes existing value' do
      setting_value.remove_subsetting('-Xmx')
      expect(setting_value.get_subsetting_value('-Xmx')).to eq(nil)
    end
  end

  describe 'comma subsetting separator' do
    INIT_VALUE_COMMA = '"-Xmx192m,-XX:+HeapDumpOnOutOfMemoryError,-XX:HeapDumpPath=/var/log/pe-puppetdb/puppetdb-oom.hprof"'.freeze

    let(:setting_value) { described_class.new(INIT_VALUE_COMMA, ',') }

    it 'gets the original value' do
      expect(setting_value.get_value).to eq(INIT_VALUE_COMMA)
    end

    it 'gets the correct value' do
      expect(setting_value.get_subsetting_value('-Xmx')).to eq('192m')
    end

    it 'adds a new value #actual' do
      setting_value.add_subsetting('-Xms', '256m')
      expect(setting_value.get_subsetting_value('-Xms')).to eq('256m')
    end
    it 'adds a new value #original' do
      setting_value.add_subsetting('-Xms', '256m')
      expect(setting_value.get_value).to eq(INIT_VALUE_COMMA[0, INIT_VALUE_COMMA.length - 1] + ',-Xms256m"')
    end

    it 'changes existing value' do
      setting_value.add_subsetting('-Xmx', '512m')
      expect(setting_value.get_subsetting_value('-Xmx')).to eq('512m')
    end

    it 'removes existing value' do
      setting_value.remove_subsetting('-Xmx')
      expect(setting_value.get_subsetting_value('-Xmx')).to eq(nil)
    end
  end

  describe 'quote_char parameter' do
    QUOTE_CHAR = '"'.freeze
    INIT_VALUE_UNQUOTED = '-Xmx192m -XX:+HeapDumpOnOutOfMemoryError -XX:HeapDumpPath=/var/log/pe-puppetdb/puppetdb-oom.hprof'.freeze

    it 'gets quoted empty string if original value was empty' do
      setting_value = described_class.new(nil, ' ', QUOTE_CHAR)
      expect(setting_value.get_value).to eq(QUOTE_CHAR * 2)
    end

    it 'quotes the setting when adding a value #actual' do
      setting_value = described_class.new(INIT_VALUE_UNQUOTED, ' ', QUOTE_CHAR)
      setting_value.add_subsetting('-Xms', '256m')

      expect(setting_value.get_subsetting_value('-Xms')).to eq('256m')
    end
    it 'quotes the setting when adding a value #original' do
      setting_value = described_class.new(INIT_VALUE_UNQUOTED, ' ', QUOTE_CHAR)
      setting_value.add_subsetting('-Xms', '256m')

      expect(setting_value.get_value).to eq(QUOTE_CHAR + INIT_VALUE_UNQUOTED + ' -Xms256m' + QUOTE_CHAR)
    end

    it 'quotes the setting when changing an existing value #value' do
      setting_value = described_class.new(INIT_VALUE_UNQUOTED, ' ', QUOTE_CHAR)
      setting_value.add_subsetting('-Xmx', '512m')

      expect(setting_value.get_subsetting_value('-Xmx')).to eq('512m')
    end
    it 'quotes the setting when changing an existing value #quotes' do
      setting_value = described_class.new(INIT_VALUE_UNQUOTED, ' ', QUOTE_CHAR)
      setting_value.add_subsetting('-Xmx', '512m')

      expect(setting_value.get_value).to match(%r{^#{Regexp.quote(QUOTE_CHAR)}.*#{Regexp.quote(QUOTE_CHAR)}$})
    end

    it 'quotes the setting when removing an existing value #value' do
      setting_value = described_class.new(INIT_VALUE_UNQUOTED, ' ', QUOTE_CHAR)
      setting_value.remove_subsetting('-Xmx')

      expect(setting_value.get_subsetting_value('-Xmx')).to eq(nil)
    end
  end
  it 'quotes the setting when removing an existing value #quotes' do
    setting_value = described_class.new(INIT_VALUE_UNQUOTED, ' ', QUOTE_CHAR)
    setting_value.remove_subsetting('-Xmx')

    expect(setting_value.get_value).to match(%r{^#{Regexp.quote(QUOTE_CHAR)}.*#{Regexp.quote(QUOTE_CHAR)}$})
  end
end
