Facter.add(:iptables_persistent_version) do
  confine operatingsystem: %w[Debian Ubuntu]
  setcode do
    # Throw away STDERR because dpkg >= 1.16.7 will make some noise if the
    # package isn't currently installed.
    os = Facter.value(:operatingsystem)
    os_release = Facter.value(:operatingsystemrelease)
    cmd = if (os == 'Debian' && (Puppet::Util::Package.versioncmp(os_release, '8.0') >= 0)) ||
             (os == 'Ubuntu' && (Puppet::Util::Package.versioncmp(os_release, '14.10') >= 0))
            "dpkg-query -Wf '${Version}' netfilter-persistent 2>/dev/null"
          else
            "dpkg-query -Wf '${Version}' iptables-persistent 2>/dev/null"
          end
    version = Facter::Util::Resolution.exec(cmd)

    if version.nil? || !version.match(%r{\d+\.\d+})
      nil
    else
      version
    end
  end
end
