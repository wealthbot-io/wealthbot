require 'spec_helper_acceptance'

config = if fact('osfamily') == 'Solaris'
           '/etc/inet/ntp.conf'
         else
           '/etc/ntp.conf'
         end

describe 'ntp class with enable_mode7:', unless: UNSUPPORTED_PLATFORMS.include?(fact('osfamily')) do
  context 'should enable' do
    let(:pp) { "class { 'ntp': enable_mode7 => true }" }

    it 'runs twice' do
      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)
    end

    describe file(config.to_s) do
      its(:content) { is_expected.to match('enable mode7') }
    end
  end

  context 'should not enable' do
    let(:pp) { "class { 'ntp': enable_mode7 => false }" }

    it 'runs twice' do
      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)
    end

    describe file(config.to_s) do
      its(:content) { is_expected.not_to match('enable mode7') }
    end
  end
end
