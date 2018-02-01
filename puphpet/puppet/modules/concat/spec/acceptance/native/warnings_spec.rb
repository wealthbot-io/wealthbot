require 'spec_helper_acceptance'

describe 'concat_fragment warnings' do
  basedir = default.tmpdir('concat')

  shared_examples 'has_warning' do |pp, w|
    it 'applies the manifest twice with a stderr regex first' do
      expect(apply_manifest(pp, catch_failures: true).stderr).to match(%r{#{Regexp.escape(w)}}m)
    end
    it 'applies the manifest twice with a stderr regex second' do
      expect(apply_manifest(pp, catch_changes: true).stderr).to match(%r{#{Regexp.escape(w)}}m)
    end
  end

  context 'concat_fragment target not found' do
    context 'target not found' do
      pp = <<-EOS
      concat_file { 'file':
        path => '#{basedir}/file',
      }
      concat_fragment { 'foo':
        target  => '#{basedir}/bar',
        content => 'bar',
      }
    EOS
      w = 'not found in the catalog'

      it_behaves_like 'has_warning', pp, w
    end
  end
end
