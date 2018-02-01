require 'spec_helper_acceptance'
require 'specinfra'

case fact('osfamily')
when 'RedHat', 'FreeBSD', 'Linux', 'Gentoo'
  servicename = 'ntpd'
when 'Solaris'
  case fact('kernelrelease')
  when '5.10'
    servicename = 'network/ntp4'
  when '5.11'
    servicename = 'network/ntp'
  end
when 'AIX'
  servicename = 'xntpd'
else
  servicename = if fact('operatingsystem') == 'SLES' && fact('operatingsystemmajrelease') == '12'
                  'ntpd'
                else
                  'ntp'
                end
end
shared_examples 'running' do
  describe service(servicename) do
    if !(fact('operatingsystem') == 'SLES' && fact('operatingsystemmajrelease') == '12')
      it { is_expected.to be_running }
      if fact('operatingsystem') == 'Debian' && fact('operatingsystemmajrelease') == '8'
        pending 'Should be enabled - Bug 760616 on Debian 8'
      else
        it { is_expected.to be_enabled }
      end
    else
      # HACK: until we either update SpecInfra or come up with alternative
      shell('service ntpd start')
      output = shell('service ntpd status')
      it {
        expect(output.stdout) =~ %r{Active\:\s+active\s+\(running\)}
      }
      it {
        expect(output.stdout) =~ %r{Loaded.*enabled\)$}
      }
    end
  end
end
describe 'service tests' do
  describe 'ntp::service class', unless: UNSUPPORTED_PLATFORMS.include?(fact('osfamily')) do
    context 'basic test' do
      it 'sets up the service' do
        apply_manifest(%(
          class { 'ntp': }
        ), catch_failures: true)
      end

      it_behaves_like 'running'
    end

    describe 'service parameters' do
      pp = <<-EOS
      class { 'ntp':
        service_enable => true,
        service_ensure => running,
        service_manage => true,
        service_name   => '#{servicename}'
      }
      EOS
      it 'starts the service' do
        apply_manifest(pp, catch_failures: true)
      end
      it_behaves_like 'running'
    end
  end

  describe 'service is unmanaged' do
    pp = <<-EOS
      class { 'ntp':
        service_enable => false,
        service_ensure => stopped,
        service_manage => false,
        service_name   => '#{servicename}'
      }
    EOS
    it 'shouldnt stop the service' do
      apply_manifest(pp, catch_failures: true)
    end

    describe service(servicename) do
      if !(fact('operatingsystem') == 'SLES' && fact('operatingsystemmajrelease') == '12')
        it { is_expected.to be_running }
        if fact('operatingsystem') == 'Debian' && fact('operatingsystemmajrelease') == '8'
          pending 'Should be enabled - Bug 760616 on Debian 8'
        else
          it { is_expected.to be_enabled }
        end
      else
        # HACK: until we either update SpecInfra or come up with alternative
        let(:output) { shell('service ntpd status', acceptable_exit_codes: [0, 3]) }

        it 'is disabled' do
          expect(output.stdout) =~ %r{Loaded.*disabled\)$}
        end
        it 'is stopped' do
          expect(output.stdout) =~ %r{Active\:\s+inactive}
        end
      end
    end
  end
end
