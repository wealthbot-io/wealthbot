require 'spec_helper'

describe 'apt::setting' do
  let(:pre_condition) { 'class { "apt": }' }
  let :facts do
    {
      os: { distro: { codename: 'wheezy' }, family: 'Debian', name: 'Debian', release: { major: '7', full: '7.0' } },
      lsbdistrelease: '7.0',
      lsbdistcodename: 'wheezy',
      operatingsystem: 'Debian',
      osfamily: 'Debian',
      lsbdistid: 'Debian',
      puppetversion: Puppet.version,
    }
  end
  let(:title) { 'conf-teddybear' }

  let(:default_params) { { content: 'di' } }

  describe 'when using the defaults' do
    context 'without source or content' do
      it do
        is_expected.to raise_error(Puppet::Error, %r{needs either of })
      end
    end

    context 'with title=conf-teddybear ' do
      let(:params) { default_params }

      it { is_expected.to contain_file('/etc/apt/apt.conf.d/50teddybear').that_notifies('Class[Apt::Update]') }
    end

    context 'with title=pref-teddybear' do
      let(:title) { 'pref-teddybear' }
      let(:params) { default_params }

      it { is_expected.to contain_file('/etc/apt/preferences.d/teddybear.pref').that_notifies('Class[Apt::Update]') }
    end

    context 'with title=list-teddybear' do
      let(:title) { 'list-teddybear' }
      let(:params) { default_params }

      it { is_expected.to contain_file('/etc/apt/sources.list.d/teddybear.list').that_notifies('Class[Apt::Update]') }
    end

    context 'with source' do
      let(:params) { { source: 'puppet:///la/die/dah' } }

      it {
        is_expected.to contain_file('/etc/apt/apt.conf.d/50teddybear').that_notifies('Class[Apt::Update]').with(ensure: 'file',
                                                                                                                owner: 'root',
                                                                                                                group: 'root',
                                                                                                                mode: '0644',
                                                                                                                source: params[:source].to_s)
      }
    end

    context 'with content' do
      let(:params) { default_params }

      it {
        is_expected.to contain_file('/etc/apt/apt.conf.d/50teddybear').that_notifies('Class[Apt::Update]').with(ensure: 'file',
                                                                                                                owner: 'root',
                                                                                                                group: 'root',
                                                                                                                mode: '0644',
                                                                                                                content: params[:content].to_s)
      }
    end
  end

  describe 'settings requiring settings, MODULES-769' do
    let(:pre_condition) do
      'class { "apt": }
      apt::setting { "list-teddybear": content => "foo" }
      '
    end
    let(:facts) do
      {
        os: { family: 'Debian', name: 'Debian', release: { major: '7', full: '7.0' } },
        lsbdistid: 'Debian',
        osfamily: 'Debian',
        lsbdistcodename: 'wheezy',
        puppetversion: Puppet.version,
      }
    end
    let(:title) { 'conf-teddybear' }
    let(:default_params) { { content: 'di' } }

    let(:params) { default_params.merge(require: 'Apt::Setting[list-teddybear]') }

    it { is_expected.to compile.with_all_deps }
  end

  describe 'when trying to pull one over' do
    context 'with source and content' do
      let(:params) { default_params.merge(source: 'la') }

      it do
        is_expected.to raise_error(Puppet::Error, %r{cannot have both })
      end
    end

    context 'with title=ext-teddybear' do
      let(:title) { 'ext-teddybear' }
      let(:params) { default_params }

      it do
        is_expected.to raise_error(Puppet::Error, %r{must start with either})
      end
    end

    context 'with ensure=banana' do
      let(:params) { default_params.merge(ensure: 'banana') }

      it do
        is_expected.to raise_error(Puppet::Error, %r{Enum\['absent', 'file', 'present'\]})
      end
    end

    context 'with priority=1.2' do
      let(:params) { default_params.merge(priority: 1.2) }

      if Puppet::Util::Package.versioncmp(Puppet.version, '4.0') >= 0 || ENV['FUTURE_PARSER'] == 'yes'
        it { is_expected.to compile.and_raise_error(%r{expects a value of type}) }
      else
        it { is_expected.to compile.and_raise_error(%r{priority must be an integer or a zero-padded integer}) }
      end
    end
  end

  describe 'with priority=100' do
    let(:params) { default_params.merge(priority: 100) }

    it { is_expected.to contain_file('/etc/apt/apt.conf.d/100teddybear').that_notifies('Class[Apt::Update]') }
  end

  describe 'with ensure=absent' do
    let(:params) { default_params.merge(ensure: 'absent') }

    it {
      is_expected.to contain_file('/etc/apt/apt.conf.d/50teddybear').that_notifies('Class[Apt::Update]').with(ensure: 'absent')
    }
  end
end
