Facter.add(:ip6tables_version) do
  confine kernel: :Linux
  setcode do
    version = Facter::Util::Resolution.exec('ip6tables --version')
    if version
      version.match(%r{\d+\.\d+\.\d+}).to_s
    else
      nil
    end
  end
end
