#! /usr/bin/env ruby # rubocop:disable Lint/ScriptPermission : Rubocop error?
require 'spec_helper'

ini_subsetting = Puppet::Type.type(:ini_subsetting)

describe ini_subsetting do
  [true, false].product([true, false, :md5]).each do |cfg, param|
    describe "when Puppet[:show_diff] is #{cfg} and show_diff => #{param}" do
      before(:each) do
        Puppet[:show_diff] = cfg
      end
      let(:value) { described_class.new(name: 'foo', value: 'whatever', show_diff: param).property(:value) }

      if cfg && param == true
        it 'displays diff' do
          expect(value.change_to_s('not_secret', 'at_all')).to include('not_secret', 'at_all')
        end

        it 'tells current value' do
          expect(value.is_to_s('not_secret_at_all')).to eq('not_secret_at_all')
        end

        it 'tells new value' do
          expect(value.should_to_s('not_secret_at_all')).to eq('not_secret_at_all')
        end
      elsif cfg && param == :md5
        it 'tells correct md5 hashes for multiple values' do
          expect(value.change_to_s('not_secret', 'at_all')).to include('e9e8db547f8960ef32dbc34029735564', '46cd73a9509ba78c39f05faf078a8cbe')
        end
        it 'does not tell singular value one' do
          expect(value.change_to_s('not_secret', 'at_all')).not_to include('not_secret')
        end
        it 'does not tell singular value two' do
          expect(value.change_to_s('not_secret', 'at_all')).not_to include('at_all')
        end

        it 'tells md5 of current value' do
          expect(value.is_to_s('not_secret_at_all')).to eq('{md5}218fde79f501b8ab8d212f1059bb857f')
        end
        it 'does not tell the current value' do
          expect(value.is_to_s('not_secret_at_all')).not_to include('not_secret_at_all')
        end

        it 'tells md5 of new value' do
          expect(value.should_to_s('not_secret_at_all')).not_to include('not_secret_at_all')
        end
        it 'does not tell the new value' do
          expect(value.should_to_s('not_secret_at_all')).to eq('{md5}218fde79f501b8ab8d212f1059bb857f')
        end
      else
        it 'tells redaction warning in place of actual values' do
          expect(value.change_to_s('not_at', 'all_secret')).to include('[redacted sensitive information]')
        end
        it 'does not tell actual value one' do
          expect(value.change_to_s('not_at', 'all_secret')).not_to include('not_at')
        end
        it 'does not tell actual value two' do
          expect(value.change_to_s('not_at', 'all_secret')).not_to include('all_secret')
        end

        it 'tells redaction warning in place of current value' do
          expect(value.is_to_s('not_at_all_secret')).to eq('[redacted sensitive information]')
        end
        it 'does not tell current value' do
          expect(value.is_to_s('not_at_all_secret')).not_to include('not_at_all_secret')
        end

        it 'tells redaction warning in place of new value' do
          expect(value.should_to_s('not_at_all_secret')).to eq('[redacted sensitive information]')
        end
        it 'does not tell new value' do
          expect(value.should_to_s('not_at_all_secret')).not_to include('not_at_all_secret')
        end
      end
    end
  end
end
