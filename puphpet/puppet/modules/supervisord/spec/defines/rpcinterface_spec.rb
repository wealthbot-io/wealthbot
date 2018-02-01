require 'spec_helper'

describe 'supervisord::rpcinterface', :type  => :define do
  let(:title) {'foo'}
  let(:facts) {{ :concat_basedir => '/var/lib/puppet/concat' }}
  let(:default_params) do
    { :rpcinterface_factory => 'bar:baz' }
  end

  context 'default' do
    let(:params) { default_params }
    it { should contain_supervisord__rpcinterface('foo') }
    it { should contain_file('/etc/supervisor.d/rpcinterface_foo.conf').with_content(/\[rpcinterface:foo\]/) }
    it { should contain_file('/etc/supervisor.d/rpcinterface_foo.conf').with_content(/supervisor\.rpcinterface_factory = bar:baz/) }
    it { should contain_file('/etc/supervisor.d/rpcinterface_foo.conf').without_content(/retries/) }
  end

  context 'retries' do
    let(:params) { { :retries => 2 }.merge(default_params) }
    it { should contain_supervisord__rpcinterface('foo') }
    it { should contain_file('/etc/supervisor.d/rpcinterface_foo.conf').with_content(/\[rpcinterface:foo\]/) }
    it { should contain_file('/etc/supervisor.d/rpcinterface_foo.conf').with_content(/supervisor\.rpcinterface_factory = bar:baz/) }
    it { should contain_file('/etc/supervisor.d/rpcinterface_foo.conf').with_content(/retries = 2/) }
  end

end
