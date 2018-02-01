#! /usr/bin/env ruby # rubocop:disable Lint/ScriptPermission : Rubocop error?
require 'spec_helper'

ini_setting = Puppet::Type.type(:ini_setting)

describe ini_setting do
  describe 'path validation' do
    # rubocop:disable RSpec/NestedGroups : Reducing the level of nesting would reduce the quality of the code
    subject { -> { described_class.new(name: 'foo', path: path) } }

    context 'on posix platforms' do
      before(:each) do
        Puppet.features.stub(:posix?) { true }
        Puppet.features.stub(:microsoft_windows?) { false }
        Puppet::Util::Platform.stub(:windows?) { false }
      end
      context 'with an absolute path' do
        let(:path) { '/absolute/path' }

        it { is_expected.not_to raise_exception }
      end

      context 'with a relative path' do
        let(:path) { 'relative/path' }

        it { is_expected.to raise_exception }
      end
    end

    context 'on windows platforms' do
      before(:each) do
        Puppet.features.stub(:posix?) { false }
        Puppet.features.stub(:microsoft_windows?) { true }
        Puppet::Util::Platform.stub(:windows?) { true }
      end

      context 'with an absolute path with front slashes' do
        let(:path) { 'c:/absolute/path' }

        it { is_expected.not_to raise_exception }
      end

      context 'with an absolute path with backslashes' do
        let(:path) { 'c:\absolute\path' }

        it { is_expected.not_to raise_exception }
      end

      context 'with an absolute path with mixed slashes' do
        let(:path) { 'c:/absolute\path' }

        it { is_expected.not_to raise_exception }
      end

      context 'with a relative path with front slashes' do
        let(:path) { 'relative/path' }

        it { is_expected.to raise_exception }
      end

      context 'with a relative path with back slashes' do
        let(:path) { 'relative\path' }

        it { is_expected.to raise_exception }
      end
    end
    # rubocop:enable RSpec/NestedGroups
  end

  [true, false].product([true, false, 'true', 'false', 'md5', :md5]).each do |cfg, param|
    describe "when Puppet[:show_diff] is #{cfg} and show_diff => #{param}" do
      before(:each) do
        Puppet[:show_diff] = cfg
      end
      let(:value) { described_class.new(name: 'foo', value: 'whatever', show_diff: param).property(:value) }

      if cfg && [true, 'true'].include?(param)
        it 'displays diff' do
          expect(value.change_to_s('not_secret', 'at_all')).to include('not_secret', 'at_all')
        end

        it 'tells current value' do
          expect(value.is_to_s('not_secret_at_all')).to eq('not_secret_at_all')
        end

        it 'tells new value' do
          expect(value.should_to_s('not_secret_at_all')).to eq('not_secret_at_all')
        end
      elsif cfg && ['md5', :md5].include?(param)
        it 'tells correct md5 hashes for multiple values' do
          expect(value.change_to_s('not_at', 'all_secret')).to include('6edef0c4f5ec664feff6ca6fbc290970', '1660308ab156754fa09af0e8dc2c6629')
        end
        it 'does not tell singular value one' do
          expect(value.change_to_s('not_at #', 'all_secret')).not_to include('not_at')
        end
        it 'does not tell singular value two' do
          expect(value.change_to_s('not_at', 'all_secret')).not_to include('all_secret')
        end

        it 'tells md5 of current value' do
          expect(value.is_to_s('not_at_all_secret')).to eq('{md5}858b46aee11b780b8f5c8853668efc05')
        end
        it 'does not tell the current value' do
          expect(value.is_to_s('not_at_all_secret')).not_to include('not_secret_at_all')
        end

        it 'tells md5 of new value' do
          expect(value.should_to_s('not_at_all_secret')).to eq('{md5}858b46aee11b780b8f5c8853668efc05')
        end
        it 'does not tell the new value' do
          expect(value.should_to_s('not_at_all_secret')).not_to include('not_secret_at_all')
        end
      else
        it 'tells redaction warning in place of actual values' do
          expect(value.change_to_s('at_all', 'not_secret')).to include('[redacted sensitive information]')
        end
        it 'does not tell actual value one' do
          expect(value.change_to_s('at_all', 'not_secret')).not_to include('not_secret')
        end
        it 'does not tell actual value two' do
          expect(value.change_to_s('at_all', 'not_secret')).not_to include('at_all')
        end

        it 'tells redaction warning in place of current value' do
          expect(value.is_to_s('not_secret_at_all')).to eq('[redacted sensitive information]')
        end
        it 'does not tell current value' do
          expect(value.is_to_s('not_secret_at_all')).not_to include('not_secret_at_all')
        end

        it 'tells redaction warning in place of new value' do
          expect(value.should_to_s('not_secret_at_all')).to eq('[redacted sensitive information]')
        end
        it 'does not tell new value' do
          expect(value.should_to_s('not_secret_at_all')).not_to include('not_secret_at_all')
        end
      end
    end
  end
end
