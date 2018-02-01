require 'spec_helper_acceptance'

# Different operating systems (and therefore different versions/forks
# of mysql) have varying levels of support for plugins and have
# different plugins available. Choose a plugin that works or don't try
# to test plugins if not available.
if fact('osfamily') =~ %r{RedHat}
  if fact('operatingsystemrelease') =~ %r{^5\.}
    plugin = nil # Plugins not supported on mysql on RHEL 5
  elsif fact('operatingsystemrelease') =~ %r{^6\.}
    plugin     = 'example'
    plugin_lib = 'ha_example.so'
  elsif fact('operatingsystemrelease') =~ %r{^7\.}
    plugin     = 'pam'
    plugin_lib = 'auth_pam.so'
  end
elsif fact('osfamily') =~ %r{Debian}
  if fact('operatingsystem') =~ %r{Debian}
    if fact('operatingsystemrelease') =~ %r{^6\.}
      # Only available plugin is innodb which is already loaded and not unload- or reload-able
      plugin = nil
    elsif fact('operatingsystemrelease') =~ %r{^7\.}
      plugin     = 'example'
      plugin_lib = 'ha_example.so'
    end
  elsif fact('operatingsystem') =~ %r{Ubuntu}
    if fact('operatingsystemrelease') =~ %r{^10\.04}
      # Only available plugin is innodb which is already loaded and not unload- or reload-able
      plugin = nil
    elsif fact('operatingsystemrelease') =~ %r{^16\.04}
      # On Xenial running 5.7.12, the example plugin does not appear to be available.
      plugin = 'validate_password'
      plugin_lib = 'validate_password.so'
    else
      plugin     = 'example'
      plugin_lib = 'ha_example.so'
    end
  end
elsif fact('osfamily') =~ %r{Suse}
  plugin = nil # Plugin library path is broken on Suse http://lists.opensuse.org/opensuse-bugs/2013-08/msg01123.html
end

describe 'mysql_plugin' do
  if plugin # if plugins are supported
    describe 'setup' do
      it 'works with no errors' do
        pp = <<-EOS
          class { 'mysql::server': }
        EOS

        apply_manifest(pp, catch_failures: true)
      end
    end

    describe 'load plugin' do
      it 'works without errors' do
        pp = <<-EOS
          mysql_plugin { #{plugin}:
            ensure => present,
            soname => '#{plugin_lib}',
          }
        EOS

        apply_manifest(pp, catch_failures: true)
      end

      it 'finds the plugin' do
        shell("mysql -NBe \"select plugin_name from information_schema.plugins where plugin_name='#{plugin}'\"") do |r|
          expect(r.stdout).to match(%r{^#{plugin}$}i)
          expect(r.stderr).to be_empty
        end
      end
    end
  end
end
