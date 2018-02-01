require 'spec_helper_acceptance'

config = if fact('osfamily') == 'Solaris'
           '/etc/inet/ntp.conf'
         else
           '/etc/ntp.conf'
         end

describe 'ntp class with disable_monitor:', unless: UNSUPPORTED_PLATFORMS.include?(fact('osfamily')) do
  context 'should disable' do
    let(:pp) { "class { 'ntp': disable_monitor => true }" }

    it 'runs twice' do
      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)
    end

    describe file(config.to_s) do
      its(:content) { is_expected.to match('disable monitor') }
    end
  end

  context 'should not disable' do
    let(:pp) { "class { 'ntp': disable_monitor => false }" }

    it 'runs twice' do
      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)
    end

    describe file(config.to_s) do
      its(:content) { is_expected.not_to match('disable monitor') }
    end
  end
end
