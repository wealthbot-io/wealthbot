require 'spec_helper_acceptance'

tmpdir = default.tmpdir('vcsrepo')

describe 'remove a repo' do
  pp = <<-EOS
    vcsrepo { "#{tmpdir}/testrepo_deleted":
      ensure => present,
      provider => git,
    }
  EOS
  it 'creates a blank repo' do # rubocop:disable RSpec/RepeatedExample : Examples are not the same, difference comes from the pp variable
    apply_manifest(pp, catch_failures: true)
  end

  pp = <<-EOS
    vcsrepo { "#{tmpdir}/testrepo_deleted":
      ensure => absent,
      provider => git,
    }
  EOS
  it 'removes a repo' do # rubocop:disable RSpec/RepeatedExample : Examples are not the same, difference comes from the pp variable
    apply_manifest(pp, catch_failures: true)
  end

  describe file("#{tmpdir}/testrepo_deleted") do
    it { is_expected.not_to be_directory }
  end
end
