require 'spec_helper_acceptance'

describe 'concat_file with quoted paths' do
  basedir = default.tmpdir('concat')

  before(:all) do
    pp = <<-EOS
      file { '#{basedir}':
        ensure => directory,
      }
      file { '#{basedir}/concat test':
        ensure => directory,
      }
    EOS
    apply_manifest(pp)
  end

  context 'path with blanks' do
    pp = <<-EOS
      concat_file { '#{basedir}/concat test/foo':
      }
      concat_fragment { '1':
        target  => '#{basedir}/concat test/foo',
        content => 'string1',
      }
      concat_fragment { '2':
        target  => '#{basedir}/concat test/foo',
        content => 'string2',
      }
    EOS

    it 'applies the manifest twice with no stderr' do
      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)
    end

    describe file("#{basedir}/concat test/foo") do
      it { is_expected.to be_file }
      its(:content) { is_expected.to match %r{string1string2} }
    end
  end
end
