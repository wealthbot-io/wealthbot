require 'spec_helper_acceptance'

describe 'concat_file symbolic name' do
  basedir = default.tmpdir('concat')
  pp = <<-EOS
    concat_file { 'not_abs_path':
      path => '#{basedir}/file',
    }

    concat_fragment { '1':
      target  => 'not_abs_path',
      content => '1',
      order   => '01',
    }

    concat_fragment { '2':
      target  => 'not_abs_path',
      content => '2',
      order   => '02',
    }
  EOS

  it 'applies the manifest twice with no stderr' do
    apply_manifest(pp, catch_failures: true)
    apply_manifest(pp, catch_changes: true)
  end

  describe file("#{basedir}/file") do
    it { is_expected.to be_file }
    its(:content) do
      is_expected.to match '1'
    end
    its(:content) do
      is_expected.to match '2'
    end
  end
end
