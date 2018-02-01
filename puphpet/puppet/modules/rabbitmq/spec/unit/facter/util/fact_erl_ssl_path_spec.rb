require 'spec_helper'
RSpec.configure do |config|
  config.mock_with :rspec
end

describe Facter::Util::Fact do
  before { Facter.clear }

  describe 'erl_ssl_path' do
    context 'with valid value' do
      before do
        allow(Facter::Util::Resolution).to receive(:which).with('erl') { true }
        allow(Facter::Core::Execution).to receive(:execute).with("erl -eval 'io:format(\"~p\", [code:lib_dir(ssl, ebin)]),halt().' -noshell") { '"/usr/lib64/erlang/lib/ssl-5.3.3/ebin"' }
      end

      it do
        expect(Facter.fact(:erl_ssl_path).value).to eq('/usr/lib64/erlang/lib/ssl-5.3.3/ebin')
      end
    end

    context 'with error message' do
      before do
        allow(Facter::Util::Resolution).to receive(:which).with('erl') { true }
        allow(Facter::Core::Execution).to receive(:execute).with("erl -eval 'io:format(\"~p\", [code:lib_dir(ssl, ebin)]),halt().' -noshell") { '{error,bad_name}' }
      end

      it do
        expect(Facter.fact(:erl_ssl_path).value).to be_nil
      end
    end

    context 'with erl not present' do
      before do
        allow(Facter::Util::Resolution).to receive(:which).with('erl') { false }
      end

      it do
        expect(Facter.fact(:erl_ssl_path).value).to be_nil
      end
    end
  end
end
