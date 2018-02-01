require 'spec_helper_acceptance'

command = case fact('osfamily')
          when 'Windows'
            'cmd.exe /c echo triggered'
          else
            'echo triggered'
          end

describe 'concat_file with metaparameters with subscribed resources' do
  basedir = default.tmpdir('concat')

  context 'should trigger refresh' do
    pp = <<-EOS
        concat_file { "foobar":
          ensure => 'present',
          path   => '#{basedir}/foobar',
        }

        concat_fragment { 'foo':
          target => 'foobar',
          content => 'foo',
        }

        exec { 'trigger':
          path        => $::path,
          command     => "#{command}",
          subscribe   => Concat_file['foobar'],
          refreshonly => true,
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
