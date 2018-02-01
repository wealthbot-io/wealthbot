require 'spec_helper_acceptance'

tmpdir = default.tmpdir('vcsrepo')

describe 'subversion :includes tests on SVN version >= 1.7', unless: ( # rubocop:disable RSpec/MultipleDescribes : The
    # test's on this page must be kept seperate as they are for different operating systems.
    (fact('osfamily') == 'RedHat' && fact('operatingsystemmajrelease') =~ %r{^(5|6)$}) ||
    (fact('osfamily') == 'Debian' && fact('operatingsystemmajrelease') =~ %r{^(6|7|10\.04|12\.04)$}) ||
    (fact('osfamily') == 'SLES')
) do

  before(:all) do
    shell("mkdir -p #{tmpdir}") # win test
  end

  after(:all) do
    shell("rm -rf #{tmpdir}/svnrepo")
  end

  context 'include paths' do
    pp = <<-EOS
        vcsrepo { "#{tmpdir}/svnrepo":
          ensure   => present,
          provider => svn,
          includes => ['difftools/README', 'obsolete-notes',],
          source   => "http://svn.apache.org/repos/asf/subversion/developer-resources",
          revision => 1000000,
        }
    EOS
    it 'can checkout specific paths from svn' do
      # Run it twice and test for idempotency
      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)
    end

    describe file("#{tmpdir}/svnrepo/difftools") do
      it { is_expected.to be_directory }
    end
    describe file("#{tmpdir}/svnrepo/difftools/README") do
      its(:md5sum) { is_expected.to eq '540241e9d5d4740d0ef3d27c3074cf93' }
    end
    describe file("#{tmpdir}/svnrepo/difftools/pics") do
      it { is_expected.not_to exist }
    end
    describe file("#{tmpdir}/svnrepo/obsolete-notes") do
      it { is_expected.to be_directory }
    end
    describe file("#{tmpdir}/svnrepo/obsolete-notes/draft-korn-vcdiff-01.txt") do
      its(:md5sum) { is_expected.to eq '37019f808e1af64864853a67526cfe19' }
    end
    describe file("#{tmpdir}/svnrepo/obsolete-notes/vcdiff-karlnotes") do
      its(:md5sum) { is_expected.to eq '26e23ff6a156de14aebd1099e23ac2d8' }
    end
    describe file("#{tmpdir}/svnrepo/guis") do
      it { is_expected.not_to exist }
    end
  end

  context 'add include paths' do
    pp = <<-EOS
        vcsrepo { "#{tmpdir}/svnrepo":
          ensure   => present,
          provider => svn,
          includes => ['difftools/README', 'obsolete-notes', 'guis/pics', 'difftools/pics/README'],
          source   => "http://svn.apache.org/repos/asf/subversion/developer-resources",
          revision => 1000000,
        }
    EOS
    it 'can add paths to includes' do
      # Run it twice and test for idempotency
      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)
    end

    describe file("#{tmpdir}/svnrepo/guis/pics/README") do
      its(:md5sum) { is_expected.to eq '62bdc9180684042fe764d89c9beda40f' }
    end
    describe file("#{tmpdir}/svnrepo/difftools/pics/README") do
      its(:md5sum) { is_expected.to eq 'bad02dfc3cb96bf5cadd59bf4fe3e00e' }
    end
  end

  context 'remove include paths' do
    pp = <<-EOS
        vcsrepo { "#{tmpdir}/svnrepo":
          ensure   => present,
          provider => svn,
          includes => ['difftools/pics/README', 'obsolete-notes',],
          source   => "http://svn.apache.org/repos/asf/subversion/developer-resources",
          revision => 1000000,
        }
    EOS
    it 'can remove paths (and empty parent directories) from includes' do
      # Run it twice and test for idempotency
      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)
    end

    describe file("#{tmpdir}/svnrepo/guis/pics/README") do
      it { is_expected.not_to exist }
    end
    describe file("#{tmpdir}/svnrepo/guis/pics") do
      it { is_expected.not_to exist }
    end
    describe file("#{tmpdir}/svnrepo/guis") do
      it { is_expected.not_to exist }
    end
    describe file("#{tmpdir}/svnrepo/difftools/pics/README") do
      its(:md5sum) { is_expected.to eq 'bad02dfc3cb96bf5cadd59bf4fe3e00e' }
    end
    describe file("#{tmpdir}/svnrepo/difftools/README") do
      it { is_expected.not_to exist }
    end
  end

  context 'changing revisions' do
    pp = <<-EOS
        vcsrepo { "#{tmpdir}/svnrepo":
          ensure   => present,
          provider => svn,
          includes => ['difftools/README', 'obsolete-notes',],
          source   => "http://svn.apache.org/repos/asf/subversion/developer-resources",
          revision => 1700000,
        }
    EOS
    it 'can change revisions' do
      # Run it twice and test for idempotency
      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)
    end

    describe command("svn info #{tmpdir}/svnrepo") do
      its(:stdout) { is_expected.to match(%r{.*Revision: 1700000.*}) }
    end
    describe command("svn info #{tmpdir}/svnrepo/difftools/README") do
      its(:stdout) { is_expected.to match(%r{.*Revision: 1700000.*}) }
    end
  end
end

describe 'subversion :includes tests on SVN version == 1.6', if: (
    (fact('osfamily') == 'RedHat' && fact('operatingsystemmajrelease') =~ %r{^(5|6)$}) ||
    (fact('osfamily') == 'Debian' && fact('operatingsystemmajrelease') =~ %r{^(6|7|10\.04|12\.04)$})
) do

  after(:all) do
    shell("rm -rf #{tmpdir}/svnrepo")
  end

  context 'include paths' do
    pp = <<-EOS
        vcsrepo { "#{tmpdir}/svnrepo":
          ensure   => present,
          provider => svn,
          includes => ['difftools/README', 'obsolete-notes',],
          source   => "http://svn.apache.org/repos/asf/subversion/developer-resources",
          revision => 1000000,
        }
    EOS
    it 'can checkout specific paths from svn' do
      # Run it twice and test for idempotency
      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)
    end

    describe file("#{tmpdir}/svnrepo/difftools") do
      it { is_expected.to be_directory }
    end
    describe file("#{tmpdir}/svnrepo/difftools/README") do
      its(:md5sum) { is_expected.to eq '540241e9d5d4740d0ef3d27c3074cf93' }
    end
    describe file("#{tmpdir}/svnrepo/difftools/pics") do
      it { is_expected.not_to exist }
    end
    describe file("#{tmpdir}/svnrepo/obsolete-notes") do
      it { is_expected.to be_directory }
    end
    describe file("#{tmpdir}/svnrepo/obsolete-notes/draft-korn-vcdiff-01.txt") do
      its(:md5sum) { is_expected.to eq '37019f808e1af64864853a67526cfe19' }
    end
    describe file("#{tmpdir}/svnrepo/obsolete-notes/vcdiff-karlnotes") do
      its(:md5sum) { is_expected.to eq '26e23ff6a156de14aebd1099e23ac2d8' }
    end
    describe file("#{tmpdir}/svnrepo/guis") do
      it { is_expected.not_to exist }
    end
  end

  context 'add include paths' do
    pp = <<-EOS
        vcsrepo { "#{tmpdir}/svnrepo":
          ensure   => present,
          provider => svn,
          includes => ['difftools/README', 'obsolete-notes', 'guis/pics', 'difftools/pics/README'],
          source   => "http://svn.apache.org/repos/asf/subversion/developer-resources",
          revision => 1000000,
        }
    EOS
    it 'can add paths to includes' do
      # Run it twice and test for idempotency
      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)
    end

    describe file("#{tmpdir}/svnrepo/guis/pics/README") do
      its(:md5sum) { is_expected.to eq '62bdc9180684042fe764d89c9beda40f' }
    end
    describe file("#{tmpdir}/svnrepo/difftools/pics/README") do
      its(:md5sum) { is_expected.to eq 'bad02dfc3cb96bf5cadd59bf4fe3e00e' }
    end
  end

  context 'remove include paths' do
    pp = <<-EOS
        vcsrepo { "#{tmpdir}/svnrepo":
          ensure   => present,
          provider => svn,
          includes => ['difftools/pics/README', 'obsolete-notes',],
          source   => "http://svn.apache.org/repos/asf/subversion/developer-resources",
          revision => 1000000,
        }
    EOS
    it 'can remove directory paths (and empty parent directories) from includes, but not files with siblings' do
      # Run it twice and test for idempotency
      apply_manifest(pp, catch_failures: true)
    end

    describe file("#{tmpdir}/svnrepo/guis/pics/README") do
      it { is_expected.not_to exist }
    end
    describe file("#{tmpdir}/svnrepo/guis/pics") do
      it { is_expected.not_to exist }
    end
    describe file("#{tmpdir}/svnrepo/guis") do
      it { is_expected.not_to exist }
    end
    describe file("#{tmpdir}/svnrepo/difftools/pics/README") do
      its(:md5sum) { is_expected.to eq 'bad02dfc3cb96bf5cadd59bf4fe3e00e' }
    end
    describe file("#{tmpdir}/svnrepo/difftools/README") do
      its(:md5sum) { is_expected.to eq '540241e9d5d4740d0ef3d27c3074cf93' }
    end
  end

  context 'changing revisions' do
    pp = <<-EOS
        vcsrepo { "#{tmpdir}/svnrepo":
          ensure   => present,
          provider => svn,
          includes => ['difftools/README', 'obsolete-notes',],
          source   => "http://svn.apache.org/repos/asf/subversion/developer-resources",
          revision => 1700000,
        }
    EOS
    it 'can change revisions' do
      # Run it twice and test for idempotency
      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)
    end

    describe command("svn info #{tmpdir}/svnrepo") do
      its(:stdout) { is_expected.to match(%r{.*Revision: 1700000.*}) }
    end
    describe command("svn info #{tmpdir}/svnrepo/difftools/README") do
      its(:stdout) { is_expected.to match(%r{.*Revision: 1700000.*}) }
    end
  end
end

describe 'subversion :includes tests on SVN version < 1.6', if: (fact('osfamily') == 'SLES') do
  context 'include paths' do
    pp = <<-EOS
        vcsrepo { "#{tmpdir}/svnrepo":
          ensure   => present,
          provider => svn,
          includes => ['difftools/README', 'obsolete-notes',],
          source   => "http://svn.apache.org/repos/asf/subversion/developer-resources",
          revision => 1000000,
        }
    EOS
    it 'fails when SVN version < 1.6' do
      # Expect error when svn < 1.6 and includes is used
      apply_manifest(pp, expect_failures: true) do |r|
        expect(r.stderr).to match(%r{Includes option is not available for SVN versions < 1.6. Version installed:})
      end
    end
  end
end
