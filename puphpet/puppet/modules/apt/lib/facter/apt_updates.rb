apt_package_updates = nil
apt_dist_updates = nil

def get_updates(upgrade_option)
  apt_updates = nil
  if File.executable?('/usr/bin/apt-get')
    apt_get_result = Facter::Util::Resolution.exec("/usr/bin/apt-get -s -o Debug::NoLocking=true #{upgrade_option} 2>&1")
    unless apt_get_result.nil?
      apt_updates = [[], []]
      apt_get_result.each_line do |line|
        next unless line =~ %r{^Inst\s}
        package = line.gsub(%r{^Inst\s([^\s]+)\s.*}, '\1').strip
        apt_updates[0].push(package)
        security_matches = [
          %r{ Debian-Security:},
          %r{ Ubuntu[^\s]+-security[, ]},
          %r{ gNewSense[^\s]+-security[, ]},
        ]
        re = Regexp.union(security_matches)
        if line.match(re)
          apt_updates[1].push(package)
        end
      end
    end
  end

  setcode do
    if !apt_updates.nil? && apt_updates.length == 2
      apt_updates != [[], []]
    end
  end
  apt_updates
end

Facter.add('apt_has_updates') do
  confine osfamily: 'Debian'
  apt_package_updates = get_updates('upgrade')
end

Facter.add('apt_has_dist_updates') do
  confine osfamily: 'Debian'
  apt_dist_updates = get_updates('dist-upgrade')
end

Facter.add('apt_package_updates') do
  confine apt_has_updates: true
  setcode do
    if Facter.version < '2.0.0'
      apt_package_updates[0].join(',')
    else
      apt_package_updates[0]
    end
  end
end

Facter.add('apt_package_dist_updates') do
  confine apt_has_dist_updates: true
  setcode do
    if Facter.version < '2.0.0'
      apt_dist_updates[0].join(',')
    else
      apt_dist_updates[0]
    end
  end
end

Facter.add('apt_package_security_updates') do
  confine apt_has_updates: true
  setcode do
    if Facter.version < '2.0.0'
      apt_package_updates[1].join(',')
    else
      apt_package_updates[1]
    end
  end
end

Facter.add('apt_package_security_dist_updates') do
  confine apt_has_dist_updates: true
  setcode do
    if Facter.version < '2.0.0'
      apt_dist_updates[1].join(',')
    else
      apt_dist_updates[1]
    end
  end
end

Facter.add('apt_updates') do
  confine apt_has_updates: true
  setcode do
    Integer(apt_package_updates[0].length)
  end
end

Facter.add('apt_dist_updates') do
  confine apt_has_dist_updates: true
  setcode do
    Integer(apt_dist_updates[0].length)
  end
end

Facter.add('apt_security_updates') do
  confine apt_has_updates: true
  setcode do
    Integer(apt_package_updates[1].length)
  end
end

Facter.add('apt_security_dist_updates') do
  confine apt_has_dist_updates: true
  setcode do
    Integer(apt_dist_updates[1].length)
  end
end
