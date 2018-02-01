require 'spec_helper'
describe 'staging::file', type: :define do
  # forcing a more sane caller_module_name to match real usage.
  let(:facts) do
    {
      osfamily: 'RedHat',
      staging_http_get: 'curl',
      puppetversion: Puppet.version
    }
  end

  describe 'when deploying via puppet' do
    let(:title) { 'sample.tar.gz' }
    let(:params) { { source: 'puppet:///modules/staging/sample.tar.gz' } }

    it { is_expected.to compile.with_all_deps }
    it do
      is_expected.to contain_file('/opt/staging')
      is_expected.to contain_file('/opt/staging/sample.tar.gz')
      is_expected.not_to contain_exec('/opt/staging/sample.tar.gz')
    end
  end

  describe 'when deploying via local' do
    let(:title) { 'sample.tar.gz' }
    let :params do
      {
        source: '/nfs/sample.tar.gz',
        target: '/usr/local/sample.tar.gz'
      }
    end
    it { is_expected.to compile.with_all_deps }
    it do
      is_expected.to contain_file('/opt/staging')
      is_expected.to contain_file('/usr/local/sample.tar.gz')
      is_expected.not_to contain_exec('/opt/staging/sample.tar.gz')
    end
  end

  describe 'when deploying via Windows local' do
    let(:title) { 'sample.tar.gz' }
    let(:params) do
      {
        source: 'S:/nfs/sample.tar.gz',
        target: '/usr/local/sample.tar.gz'
      }
    end

    # it { is_expected.to compile.with_all_deps } # This test will fail unless we're running the tests on Windows.
    it do
      is_expected.to contain_file('/opt/staging')
      is_expected.to contain_file('/usr/local/sample.tar.gz')
      is_expected.not_to contain_exec('/opt/staging/sample.tar.gz')
    end
  end

  describe 'when deploying via http' do
    let(:title) { 'sample.tar.gz' }
    let(:params) { { source: 'http://webserver/sample.tar.gz' } }

    it { is_expected.to compile.with_all_deps }
    it do
      is_expected.to contain_file('/opt/staging')
      is_expected.to contain_exec('/opt/staging/sample.tar.gz').with(command: 'curl  -f -L -o /opt/staging/sample.tar.gz http://webserver/sample.tar.gz',
                                                                     path: '/usr/local/bin:/usr/bin:/bin',
                                                                     environment: nil,
                                                                     cwd: '/opt/staging',
                                                                     creates: '/opt/staging/sample.tar.gz',
                                                                     logoutput: 'on_failure')
      is_expected.to contain_file('/opt/staging/sample.tar.gz').with(owner: nil,
                                                                     group: nil,
                                                                     mode: nil)
    end
  end

  describe 'when deploying via http with file parameters' do
    let(:title) { 'sample.tar.gz' }
    let(:params) do
      {
        source: 'http://webserver/sample.tar.gz',
        owner: 'root',
        group: 'root',
        mode: '0644'
      }
    end

    it { is_expected.to compile.with_all_deps }
    it do
      is_expected.to contain_file('/opt/staging')
      is_expected.to contain_exec('/opt/staging/sample.tar.gz').with(command: 'curl  -f -L -o /opt/staging/sample.tar.gz http://webserver/sample.tar.gz',
                                                                     path: '/usr/local/bin:/usr/bin:/bin',
                                                                     environment: nil,
                                                                     cwd: '/opt/staging',
                                                                     creates: '/opt/staging/sample.tar.gz',
                                                                     logoutput: 'on_failure')
      is_expected.to contain_file('/opt/staging/sample.tar.gz').with(owner: 'root',
                                                                     group: 'root',
                                                                     mode: '0644')
    end
  end

  describe 'when deploying via http with custom curl options' do
    let(:title) { 'sample.tar.gz' }
    let(:params) do
      {
        source: 'http://webserver/sample.tar.gz',
        curl_option: '-b'
      }
    end

    it { is_expected.to compile.with_all_deps }
    it do
      is_expected.to contain_file('/opt/staging')
      is_expected.to contain_exec('/opt/staging/sample.tar.gz').with(command: 'curl -b -f -L -o /opt/staging/sample.tar.gz http://webserver/sample.tar.gz',
                                                                     path: '/usr/local/bin:/usr/bin:/bin',
                                                                     environment: nil,
                                                                     cwd: '/opt/staging',
                                                                     creates: '/opt/staging/sample.tar.gz',
                                                                     logoutput: 'on_failure')
    end
  end

  describe 'when deploying via http with parameters' do
    let(:title) { 'sample.tar.gz' }
    let :params do
      {
        source: 'http://webserver/sample.tar.gz',
        target: '/usr/local/sample.tar.gz',
        tries: '10',
        try_sleep: '6'
      }
    end
    it { is_expected.to compile.with_all_deps }
    it do
      is_expected.to contain_file('/opt/staging')
      is_expected.to contain_exec('/usr/local/sample.tar.gz').with(command: 'curl  -f -L -o /usr/local/sample.tar.gz http://webserver/sample.tar.gz',
                                                                   path: '/usr/local/bin:/usr/bin:/bin',
                                                                   environment: nil,
                                                                   cwd: '/usr/local',
                                                                   creates: '/usr/local/sample.tar.gz',
                                                                   tries: '10',
                                                                   try_sleep: '6')
    end
  end

  describe 'when deploying via https' do
    let(:title) { 'sample.tar.gz' }
    let(:params) { { source: 'https://webserver/sample.tar.gz' } }

    it { is_expected.to compile.with_all_deps }
    it { is_expected.to contain_file('/opt/staging') }
    it do
      is_expected.to contain_exec('/opt/staging/sample.tar.gz').with(command: 'curl  -f -L -o /opt/staging/sample.tar.gz https://webserver/sample.tar.gz',
                                                                     path: '/usr/local/bin:/usr/bin:/bin',
                                                                     environment: nil,
                                                                     cwd: '/opt/staging',
                                                                     creates: '/opt/staging/sample.tar.gz',
                                                                     logoutput: 'on_failure')
    end
  end

  describe 'when deploying via https with parameters' do
    let(:title) { 'sample.tar.gz' }
    let :params do
      {
        source: 'https://webserver/sample.tar.gz',
        username: 'puppet',
        password: 'puppet'
      }
    end
    it { is_expected.to compile.with_all_deps }
    it do
      is_expected.to contain_file('/opt/staging')
      is_expected.to contain_exec('/opt/staging/sample.tar.gz').with(command: 'curl  -f -L -o /opt/staging/sample.tar.gz -u puppet:puppet https://webserver/sample.tar.gz',
                                                                     path: '/usr/local/bin:/usr/bin:/bin',
                                                                     environment: nil,
                                                                     cwd: '/opt/staging',
                                                                     creates: '/opt/staging/sample.tar.gz',
                                                                     logoutput: 'on_failure')
    end
  end

  describe 'when deploying via ftp' do
    let(:title) { 'sample.tar.gz' }
    let(:params) { { source: 'ftp://webserver/sample.tar.gz' } }

    it { is_expected.to compile.with_all_deps }
    it do
      is_expected.to contain_file('/opt/staging')
      is_expected.to contain_exec('/opt/staging/sample.tar.gz').with(command: 'curl  -o /opt/staging/sample.tar.gz ftp://webserver/sample.tar.gz',
                                                                     path: '/usr/local/bin:/usr/bin:/bin',
                                                                     environment: nil,
                                                                     cwd: '/opt/staging',
                                                                     creates: '/opt/staging/sample.tar.gz',
                                                                     logoutput: 'on_failure')
    end
  end

  describe 'when deploying via ftp with parameters' do
    let(:title) { 'sample.tar.gz' }
    let :params do
      {
        source: 'ftp://webserver/sample.tar.gz',
        username: 'puppet',
        password: 'puppet'
      }
    end
    it { is_expected.to compile.with_all_deps }
    it do
      is_expected.to contain_file('/opt/staging')
      is_expected.to contain_exec('/opt/staging/sample.tar.gz').with(command: 'curl  -o /opt/staging/sample.tar.gz -u puppet:puppet ftp://webserver/sample.tar.gz',
                                                                     path: '/usr/local/bin:/usr/bin:/bin',
                                                                     environment: nil,
                                                                     cwd: '/opt/staging',
                                                                     creates: '/opt/staging/sample.tar.gz',
                                                                     logoutput: 'on_failure')
    end
  end
end
