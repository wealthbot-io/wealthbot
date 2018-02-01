require 'spec_helper'

describe 'nodejs::instance::download', :type => :define do
  let(:title) { 'nodejs::instance::download' }

  describe 'wget download' do
    let(:params) {{
      :source      => 'https://test.dev/foo',
      :destination => '/usr/local/bin/foo'
    }}

    it { should contain_exec('nodejs-wget-download-https://test.dev/foo-/usr/local/bin/foo') \
      .with_command('/usr/bin/wget --output-document /usr/local/bin/foo https://test.dev/foo') \
      .with_creates('/usr/local/bin/foo') \
      .with_timeout(0)
    }
  end

  describe 'wget download with given timeout' do
    let(:params) {{
      :source      => 'https://test.dev/foo',
      :destination => '/usr/local/bin/foo',
      :timeout     => 25
    }}

    it { should contain_exec('nodejs-wget-download-https://test.dev/foo-/usr/local/bin/foo') \
      .with_timeout(25) \
    }
  end
end
