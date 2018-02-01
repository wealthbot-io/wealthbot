require 'spec_helper_acceptance'

case fact('osfamily')
when 'FreeBSD'
  packagename = 'net/ntp'
when 'Gentoo'
  packagename = 'net-misc/ntp'
when 'Linux'
  case fact('operatingsystem')
  when 'ArchLinux'
    packagename = 'ntp'
  when 'Gentoo'
    packagename = 'net-misc/ntp'
  end
when 'AIX'
  packagename = 'bos.net.tcp.client'
when 'Solaris'
  case fact('kernelrelease')
  when '5.10'
    packagename = %w[SUNWntp4r SUNWntp4u]
  when '5.11'
    packagename = 'service/network/ntp'
  end
end

describe 'ntp::install class', unless: UNSUPPORTED_PLATFORMS.include?(fact('osfamily')) do
  it 'installs the package' do
    apply_manifest(%(
      class { 'ntp': }
    ), catch_failures: true)
  end

  Array(packagename).each do |package|
    describe package(package) do
      it { is_expected.to be_installed }
    end
  end
end
