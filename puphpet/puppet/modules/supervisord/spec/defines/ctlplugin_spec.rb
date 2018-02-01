require 'spec_helper'

describe 'supervisord::ctlplugin', :type => :define do
  let(:title) {'foo'}
  let (:facts) {{ :concat_basedir => '/var/lib/puppet/concat' }}

  context 'default' do
    let(:params) { { :ctl_factory => 'bar.baz:make_bat' } }
    it { should contain_supervisord__ctlplugin('foo') }
    it { should contain_concat__fragment('ctlplugin:foo') \
      .with_content(/supervisor\.ctl_factory = bar\.baz:make_bat/) }
  end
end
