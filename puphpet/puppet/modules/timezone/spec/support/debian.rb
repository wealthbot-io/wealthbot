shared_examples 'Debian' do
  let(:facts) do
    {
      :os => {
        :name => 'Debian',
        :family => 'Debian',
        :release => { :major => '8' }
      }
    }
  end

  describe 'when using default class parameters' do
    let(:params) { {} }

    it { is_expected.to create_class('timezone') }
    it { is_expected.to contain_file('/etc/timezone') }
    it { is_expected.to contain_file('/etc/timezone').with_ensure('file') }
    it { is_expected.to contain_file('/etc/timezone').with_content(%r{Etc/UTC}) }
    it { is_expected.to contain_exec('update_timezone').with_command(%r{^dpkg-reconfigure -f noninteractive tzdata$}) }

    it do
      is_expected.to contain_package('tzdata').with(:ensure => 'present',
                                                    :before => 'File[/etc/localtime]')
    end
    it do
      is_expected.to contain_file('/etc/localtime').with(:ensure => 'file',
                                                         :source => 'file:///usr/share/zoneinfo/Etc/UTC')
    end

    context 'when timezone => "Europe/Berlin"' do
      let(:params) { { :timezone => 'Europe/Berlin' } }

      it { is_expected.to contain_file('/etc/timezone').with_content(%r{^Europe/Berlin$}) }
      it { is_expected.to contain_file('/etc/localtime').with_source('file:///usr/share/zoneinfo/Europe/Berlin') }
    end

    context 'when autoupgrade => true' do
      let(:params) { { :autoupgrade => true } }

      it { is_expected.to contain_package('tzdata').with_ensure('latest') }
    end

    context 'when ensure => absent' do
      let(:params) { { :ensure => 'absent' } }

      it { is_expected.to contain_package('tzdata').with_ensure('present') }
      it { is_expected.to contain_file('/etc/timezone').with_ensure('absent') }
      it { is_expected.to contain_file('/etc/localtime').with_ensure('absent') }
    end

    include_examples 'validate parameters'
  end
end
