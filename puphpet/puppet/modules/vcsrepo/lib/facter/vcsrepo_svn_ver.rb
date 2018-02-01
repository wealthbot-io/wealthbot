Facter.add(:vcsrepo_svn_ver) do
  setcode do
    begin
      version = Facter::Core::Execution.execute('svn --version --quiet')
      if Gem::Version.new(version) > Gem::Version.new('0.0.1')
        version
      else
        ''
      end
    rescue
      ''
    end
  end
end
