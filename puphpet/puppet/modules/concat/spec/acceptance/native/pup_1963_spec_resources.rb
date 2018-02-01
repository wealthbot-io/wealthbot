require 'spec_helper_acceptance'

command = case fact('osfamily')
          when 'Windows'
            'cmd.exe /c echo triggered'
          else
            'echo triggered'
          end

describe 'concat_file with metaparameters with resources to notify' do
  basedir = default.tmpdir('concat')
  context 'should notify' do
    pp = <<-EOS
        exec { 'trigger':
          path        => $::path,
          command     => "#{command}",
          refreshonly => true,
        }

        concat_file { "foobar":
          ensure => 'present',
          path   => '#{basedir}/foobar',
          notify => Exec['trigger'],
        }

        concat_fragment { 'foo':
          target => 'foobar',
          content => 'foo',
        }
    EOS

    it 'applies the manifest twice with stdout regex first' do
      expect(apply_manifest(pp, catch_failures: true).stdout).to match(%r{Triggered 'refresh'})
    end
    it 'applies the manifest twice with stdout regex second' do
      expect(apply_manifest(pp, catch_changes: true).stdout).not_to match(%r{Triggered 'refresh'})
    end
  end
end
