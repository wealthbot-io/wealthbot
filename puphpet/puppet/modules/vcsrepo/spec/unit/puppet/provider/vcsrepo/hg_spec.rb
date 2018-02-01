require 'spec_helper'

describe Puppet::Type.type(:vcsrepo).provider(:hg) do
  let(:resource) do
    Puppet::Type.type(:vcsrepo).new(name: 'test',
                                    ensure: :present,
                                    provider: :hg,
                                    path: '/tmp/vcsrepo')
  end

  let(:provider) { resource.provider }

  before :each do
    Puppet::Util.stubs(:which).with('hg').returns('/usr/bin/hg')
  end

  describe 'creating' do
    context 'with source and revision' do
      it "executes 'hg clone -u' with the revision" do
        resource[:source] = 'something'
        resource[:revision] = '1'
        provider.expects(:hg).with('clone', '-u', resource.value(:revision),
                                   resource.value(:source), resource.value(:path))
        provider.create
      end
    end

    context 'without revision' do
      it "justs execute 'hg clone' without a revision" do
        resource[:source] = 'something'
        provider.expects(:hg).with('clone', resource.value(:source), resource.value(:path))
        provider.create
      end
    end

    context 'when a source is not given' do
      it "executes 'hg init'" do
        provider.expects(:hg).with('init', resource.value(:path))
        provider.create
      end
    end

    context 'when basic auth is used' do
      it "executes 'hg clone'" do # rubocop:disable RSpec/ExampleLength: unable to shrink further without violating another rule
        resource[:source] = 'something'
        resource[:basic_auth_username] = 'user'
        resource[:basic_auth_password] = 'pass'
        provider.expects(:hg).with('clone', resource.value(:source), resource.value(:path), '--config',
                                   'auth.x.prefix=' + resource.value(:source), '--config', 'auth.x.username=' + resource.value(:basic_auth_username),
                                   '--config', 'auth.x.password=' + resource.value(:basic_auth_password), '--config', 'auth.x.schemes=http https')
        provider.create
      end
    end
  end

  describe 'destroying' do
    it 'removes the directory' do
      expects_rm_rf
      provider.destroy
    end
  end

  describe 'checking existence' do
    it 'checks for the directory' do
      expects_directory?(true, resource.value(:path))
      provider.expects(:hg).with('status', resource.value(:path))
      provider.exists?
    end
  end

  describe 'checking the revision property' do
    before(:each) do
      expects_chdir
    end

    context 'when given a non-SHA as the resource revision' do
      before(:each) do
        provider.expects(:hg).with('parents').returns(fixture(:hg_parents))
        provider.expects(:hg).with('tags').returns(fixture(:hg_tags))
      end

      it 'when its sha is not different from the current SHA it returns the ref' do
        resource[:revision] = '0.6'
        expect(provider.revision).to eq('0.6')
      end

      it 'when its SHA is different than the current SHA it returns the current SHA' do
        resource[:revision] = '0.5.3'
        expect(provider.revision).to eq('34e6012c783a')
      end
    end
    context 'when given a SHA as the resource revision' do
      before(:each) do
        provider.expects(:hg).with('parents').returns(fixture(:hg_parents))
      end

      it 'when it is the same as the current SHA it returns it' do
        resource[:revision] = '34e6012c783a'
        provider.expects(:hg).with('tags').returns(fixture(:hg_tags))
        expect(provider.revision).to eq(resource.value(:revision))
      end

      it 'when it is not the same as the current SHA it returns the current SHA' do
        resource[:revision] = 'not-the-same'
        provider.expects(:hg).with('tags').returns(fixture(:hg_tags))
        expect(provider.revision).to eq('34e6012c783a')
      end
    end
  end

  describe 'setting the revision property' do
    let(:revision) { '6aa99e9b3ab1' }

    it "uses 'hg update ---clean -r'" do
      expects_chdir
      provider.expects(:hg).with('pull')
      provider.expects(:hg).with('merge')
      provider.expects(:hg).with('update', '--clean', '-r', revision)
      provider.revision = revision
    end
  end

  describe 'checking the source property' do
    it 'returns the default path' do
      resource[:source] = 'http://selenic.com/hg'
      expects_chdir
      provider.expects(:hg_wrapper).with('paths').returns('default = http://selenic.com/hg')
      expect(provider.source).to eq(resource.value(:source))
    end
  end

  describe 'setting the source property' do
    it "calls 'create'" do
      resource[:source] = 'some-example'
      provider.expects(:create)
      provider.source = resource.value(:source)
    end
  end
end
