require 'spec_helper'

describe 'supervisord::supervisorctl', :type => :define do
  let(:default_params) {{ 
    :command => 'command'
  }}

  let(:facts) {{ :concat_basedir => '/var/lib/puppet/concat' }}

  context 'without process' do
    let(:title) {'command'}
    let(:params) { default_params }
    it { should contain_supervisord__supervisorctl('command') }
    it { should contain_exec('supervisorctl_command_command').with_command(/supervisorctl command/) }
  end

  context 'with process' do
    let(:title) {'command_foo'}
    let(:params) { default_params.merge({ :process => 'foo' }) }
    it { should contain_supervisord__supervisorctl('command_foo') }
    it { should contain_exec('supervisorctl_command_command_foo').with_command(/supervisorctl command foo/) }
  end

end
