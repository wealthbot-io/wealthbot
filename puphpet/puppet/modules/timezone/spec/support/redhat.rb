shared_examples 'RedHat' do
  let(:facts) do
    {
      :os => {
        :name => 'CentOS',
        :family => 'RedHat',
        :release => { :major => '6' }
      }
    }
  end

  describe 'when using default class parameters' do
    let(:params) { {} }

    it { is_expected.to create_class('timezone') }

    it do
      is_expected.to contain_package('tzdata').with(:ensure => 'present',
                                                    :before => 'File[/etc/localtime]')
    end

    it { is_expected.to contain_file('/etc/sysconfig/clock').with_ensure('file') }
    it { is_expected.to contain_file('/etc/sysconfig/clock').with_content(%r{^ZONE="Etc/UTC"$}) }
    it { is_expected.not_to contain_exec('update_timezone') }

    it do
      is_expected.to contain_file('/etc/localtime').with(:ensure => 'file',
                                                         :source => 'file:///usr/share/zoneinfo/Etc/UTC')
    end

    context 'when timezone => "Europe/Berlin"' do
      let(:params) { { :timezone => 'Europe/Berlin' } }

      it { is_expected.to contain_file('/etc/sysconfig/clock').with_content(%r{^ZONE="Europe/Berlin"$}) }
      it { is_expected.to contain_file('/etc/localtime').with_source('file:///usr/share/zoneinfo/Europe/Berlin') }
    end

    context 'when autoupgrade => true' do
      let(:params) { { :autoupgrade => true } }

      it { is_expected.to contain_package('tzdata').with_ensure('latest') }
    end

    context 'when ensure => absent' do
      let(:params) { { :ensure => 'absent' } }

      it { is_expected.to contain_package('tzdata').with_ensure('present') }
      it { is_expected.to contain_file('/etc/sysconfig/clock').with_ensure('absent') }
      it { is_expected.to contain_file('/etc/localtime').with_ensure('absent') }
    end

    context 'when RHEL 7' do
      let(:facts) { { :os => { :release => { :major => '7' } } } }

      it { is_expected.not_to contain_file('/etc/sysconfig/clock').with_ensure('file') }
    end

    include_examples 'validate parameters'
  end
end
