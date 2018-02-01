require 'spec_helper'

describe Puppet::Type.type(:vcsrepo).provider(:bzr) do
  let(:resource) do
    Puppet::Type.type(:vcsrepo).new(name: 'test',
                                    ensure: :present,
                                    provider: :bzr,
                                    revision: '2634',
                                    source: 'lp:do',
                                    path: '/tmp/test')
  end

  let(:provider) { resource.provider }

  before :each do
    Puppet::Util.stubs(:which).with('bzr').returns('/usr/bin/bzr')
  end

  describe 'creating' do
    context 'with defaults' do
      it "executes 'bzr clone -r' with the revision" do
        provider.expects(:bzr).with('branch', '-r', resource.value(:revision), resource.value(:source), resource.value(:path))
        provider.create
      end
    end

    context 'without revision' do
      it "justs execute 'bzr clone' without a revision" do
        resource.delete(:revision)
        provider.expects(:bzr).with('branch', resource.value(:source), resource.value(:path))
        provider.create
      end
    end

    context 'without source' do
      it "executes 'bzr init'" do
        resource.delete(:source)
        provider.expects(:bzr).with('init', resource.value(:path))
        provider.create
      end
    end
  end

  describe 'destroying' do
    it 'removes the directory' do
      provider.destroy
    end
  end

  describe 'checking existence' do
    it 'executes bzr status on the path' do
      File.expects(:directory?).with(resource.value(:path)).returns(true)
      provider.expects(:bzr).with('status', resource[:path])
      provider.exists?
    end
  end

  describe 'checking the revision property' do
    before(:each) do
      expects_chdir
      provider.expects(:bzr).with('version-info').returns(File.read(fixtures('bzr_version_info.txt')))
    end
    let(:current_revid) { 'menesis@pov.lt-20100309191856-4wmfqzc803fj300x' }

    context 'when given a non-revid as the resource revision and its revid is not different than the current revid' do
      it 'returns the ref' do
        resource[:revision] = '2634'
        provider.expects(:bzr).with('revision-info', '2634').returns("2634 menesis@pov.lt-20100309191856-4wmfqzc803fj300x\n")
        expect(provider.revision).to eq(resource.value(:revision))
      end
    end

    context 'when given a non-revid as the resource revision and its revid is different than the current revid' do
      it 'returns the current revid' do
        resource[:revision] = '2636'
        provider.expects(:bzr).with('revision-info', resource.value(:revision)).returns("2635 foo\n")
        expect(provider.revision).to eq(current_revid)
      end
    end

    context 'when given a revid as the resource revision and it is the same as the current revid' do
      it 'returns it' do
        resource[:revision] = 'menesis@pov.lt-20100309191856-4wmfqzc803fj300x'
        provider.expects(:bzr).with('revision-info', resource.value(:revision)).returns("1234 #{resource.value(:revision)}\n")
        expect(provider.revision).to eq(resource.value(:revision))
      end
    end

    context 'when given a revid as the resource revision and it is not the same as the current revid' do
      it 'returns the current revid' do
        resource[:revision] = 'menesis@pov.lt-20100309191856-4wmfqzc803fj300y'
        provider.expects(:bzr).with('revision-info', resource.value(:revision)).returns("2636 foo\n")
        expect(provider.revision).to eq(current_revid)
      end
    end
  end

  describe 'setting the revision property' do
    it "uses 'bzr update -r' with the revision" do
      Dir.expects(:chdir).with('/tmp/test').at_least_once.yields
      provider.expects(:bzr).with('update', '-r', 'somerev')
      provider.revision = 'somerev'
    end
  end

  describe 'checking the source property' do
    it "uses 'bzr info'" do
      expects_chdir
      resource[:source] = 'http://bazaar.launchpad.net/~bzr-pqm/bzr/bzr.dev/'
      provider.expects(:bzr).with('info').returns(' parent branch: http://bazaar.launchpad.net/~bzr-pqm/bzr/bzr.dev/')
      expect(provider.source).to eq(resource.value(:source))
    end
  end
  describe 'setting the source property' do
    it "calls 'create'" do
      resource[:source] = 'http://bazaar.launchpad.net/~bzr-pqm/bzr/bzr.dev/'
      provider.expects(:create)
      provider.source = resource.value(:source)
    end
  end
end
