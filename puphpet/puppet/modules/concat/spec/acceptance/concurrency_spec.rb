require 'spec_helper_acceptance'

describe 'with file recursive purge' do
  basedir = default.tmpdir('concat')
  context 'should still create concat file' do
    pp = <<-EOS
      file { '#{basedir}/bar':
        ensure => directory,
        purge  => true,
        recurse => true,
      }

      concat { "foobar":
        ensure => 'present',
        path   => '#{basedir}/bar/foobar',
      }

      concat::fragment { 'foo':
        target => 'foobar',
        content => 'foo',
      }
    EOS

    it 'applies the manifest twice with no stderr' do
      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)
    end

    describe file("#{basedir}/bar/foobar") do
      it { is_expected.to be_file }
      its(:content) do
        is_expected.to match 'foo'
      end
    end
  end
end
