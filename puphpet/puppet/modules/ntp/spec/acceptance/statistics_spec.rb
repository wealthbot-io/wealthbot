require 'spec_helper_acceptance'

config = if fact('osfamily') == 'Solaris'
           '/etc/inet/ntp.conf'
         else
           '/etc/ntp.conf'
         end

describe 'ntp class with statistics:', unless: UNSUPPORTED_PLATFORMS.include?(fact('osfamily')) do
  context 'should run successfully' do
    let(:pp) { "class { 'ntp': statistics => ['loopstats'], disable_monitor => false}" }

    it 'runs twice' do
      2.times do
        apply_manifest(pp, catch_failures: true) do |r|
          expect(r.stderr).not_to match(%r{error}i)
        end
      end
    end
  end

  describe file(config.to_s) do
    its(:content) { is_expected.to match('filegen loopstats file loopstats type day enable') }
  end

  describe file(config.to_s) do
    its(:content) { is_expected.to match('statsdir /var/log/ntpstats') }
  end
end
