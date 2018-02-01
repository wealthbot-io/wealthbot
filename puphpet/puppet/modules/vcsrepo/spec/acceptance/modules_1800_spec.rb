require 'spec_helper_acceptance'

tmpdir = default.tmpdir('vcsrepo')

describe 'clones a remote repo' do
  before(:all) do
    File.expand_path(File.join(File.dirname(__FILE__), '..'))
    shell("mkdir -p #{tmpdir}") # win test
  end

  after(:all) do
    shell("rm -rf #{tmpdir}/vcsrepo")
  end

  context 'ensure latest with no revision' do
    pp = <<-EOS
      vcsrepo { "#{tmpdir}/vcsrepo":
          ensure   => present,
          provider => git,
          source   => "https://github.com/puppetlabs/puppetlabs-vcsrepo.git",
      }
    EOS
    it 'clones from default remote' do
      apply_manifest(pp, catch_failures: true)
      shell("cd #{tmpdir}/vcsrepo; /usr/bin/git reset --hard HEAD~2")
    end

    pp = <<-EOS
      vcsrepo { "#{tmpdir}/vcsrepo":
          ensure   => latest,
          provider => git,
          source   => "https://github.com/puppetlabs/puppetlabs-vcsrepo.git",
      }
    EOS
    it 'updates' do
      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)
    end
  end
end
