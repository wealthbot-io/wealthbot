require 'spec_helper_acceptance'

tmpdir = default.tmpdir('vcsrepo')

describe 'does not remove a repo if noop' do
  pp = <<-EOS
    vcsrepo { "#{tmpdir}/testrepo_noop_deleted":
      ensure   => present,
      provider => git,
    }
  EOS
  it 'creates a blank repo' do
    apply_manifest(pp, catch_failures: true)
  end

  pp = <<-EOS
    vcsrepo { "#{tmpdir}/testrepo_noop_deleted":
      ensure   => absent,
      provider => git,
      force    => true,
    }
  EOS
  it 'does not remove a repo if noop' do
    apply_manifest(pp, catch_failures: true, noop: true, verbose: false)
  end

  describe file("#{tmpdir}/testrepo_noop_deleted") do
    it { is_expected.to be_directory }
  end
end
