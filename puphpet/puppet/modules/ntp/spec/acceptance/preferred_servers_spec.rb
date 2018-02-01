require 'spec_helper_acceptance'

config = if fact('osfamily') == 'Solaris'
           '/etc/inet/ntp.conf'
         else
           '/etc/ntp.conf'
         end

describe 'preferred servers', unless: UNSUPPORTED_PLATFORMS.include?(fact('osfamily')) do
  pp = <<-EOS
    class { '::ntp':
      servers           => ['a', 'b', 'c', 'd'],
      preferred_servers => ['c', 'd'],
    }
  EOS

  it 'applies cleanly' do
    apply_manifest(pp, catch_failures: true) do |r|
      expect(r.stderr).not_to match(%r{error}i)
    end
  end

  describe file(config.to_s) do
    it { is_expected.to be_file }
    its(:content) { is_expected.to match 'server a' }
    its(:content) { is_expected.to match 'server b' }
    its(:content) { is_expected.to match %r{server c (iburst\s|)prefer} }
    its(:content) { is_expected.to match %r{server d (iburst\s|)prefer} }
  end
end
