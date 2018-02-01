Facter.add(:iptables_version) do
  confine kernel: :Linux
  setcode do
    version = Facter::Util::Resolution.exec('iptables --version')
    if version
      version.match(%r{\d+\.\d+\.\d+}).to_s
    else
      nil
    end
  end
end
