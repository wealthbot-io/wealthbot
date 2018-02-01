require 'spec_helper_acceptance'

tmpdir = default.tmpdir('vcsrepo')

describe 'subversion tests' do
  before(:each) do
    shell("mkdir -p #{tmpdir}") # win test
  end

  context 'plain checkout' do
    pp = <<-EOS
      vcsrepo { "#{tmpdir}/svnrepo":
        ensure   => present,
        provider => svn,
        source   => "http://svn.apache.org/repos/asf/subversion/svn-logos",
      }
    EOS
    it 'can checkout svn' do
      # Run it twice and test for idempotency
      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)
    end

    describe file("#{tmpdir}/svnrepo/.svn") do
      it { is_expected.to be_directory }
    end
    describe file("#{tmpdir}/svnrepo/images/tyrus-svn2.png") do
      its(:md5sum) { is_expected.to eq '6b20cbc4a793913190d1548faad1ae80' }
    end

    after(:all) do
      shell("rm -rf #{tmpdir}/svnrepo")
    end
  end

  context 'handles revisions' do
    pp = <<-EOS
      vcsrepo { "#{tmpdir}/svnrepo":
        ensure   => present,
        provider => svn,
        source   => "http://svn.apache.org/repos/asf/subversion/developer-resources",
        revision => 1000000,
      }
    EOS
    it 'can checkout a specific revision of svn' do
      # Run it twice and test for idempotency
      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)
    end

    describe file("#{tmpdir}/svnrepo/.svn") do
      it { is_expected.to be_directory }
    end
    describe command("svn info #{tmpdir}/svnrepo") do
      its(:stdout) { is_expected.to match(%r{.*Revision: 1000000.*}) }
    end
    describe file("#{tmpdir}/svnrepo/difftools/README") do
      its(:md5sum) { is_expected.to eq '540241e9d5d4740d0ef3d27c3074cf93' }
    end
  end

  context 'handles revisions' do
    pp = <<-EOS
      vcsrepo { "#{tmpdir}/svnrepo":
        ensure   => present,
        provider => svn,
        source   => "http://svn.apache.org/repos/asf/subversion/developer-resources",
        revision => 1700000,
      }
    EOS
    it 'can switch revisions' do
      # Run it twice and test for idempotency
      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)
    end

    describe file("#{tmpdir}/svnrepo/.svn") do
      it { is_expected.to be_directory }
    end
    describe command("svn info #{tmpdir}/svnrepo") do
      its(:stdout) { is_expected.to match(%r{.*Revision: 1700000.*}) }
    end

    after(:all) do
      shell("rm -rf #{tmpdir}/svnrepo")
    end
  end

  context 'switching sources' do
    pp = <<-EOS
      vcsrepo { "#{tmpdir}/svnrepo":
        ensure   => present,
        provider => svn,
        source   => "http://svn.apache.org/repos/asf/subversion/tags/1.9.0",
      }
    EOS
    it 'can checkout tag=1.9.0' do
      # Run it twice and test for idempotency
      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)
    end
    describe file("#{tmpdir}/svnrepo/.svn") do
      it { is_expected.to be_directory }
    end
    describe file("#{tmpdir}/svnrepo/STATUS") do
      its(:md5sum) { is_expected.to eq '286708a30aea43d78bc2b11f3ac57fff' }
    end
  end

  context 'switching sources' do
    pp = <<-EOS
      vcsrepo { "#{tmpdir}/svnrepo":
        ensure   => present,
        provider => svn,
        source   => "http://svn.apache.org/repos/asf/subversion/tags/1.9.4",
      }
    EOS
    it 'can switch to tag=1.9.4' do
      # Run it twice and test for idempotency
      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)
    end

    describe file("#{tmpdir}/svnrepo/.svn") do
      it { is_expected.to be_directory }
    end
    describe file("#{tmpdir}/svnrepo/STATUS") do
      its(:md5sum) { is_expected.to eq '7f072a1c0e2ba37ca058f65e554de95e' }
    end
  end
end
