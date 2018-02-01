require 'spec_helper'

describe 'archive' do
  context 'RHEL' do
    let(:facts) do
      {
        os: { family: 'RedHat' },
        operatingsystem: 'RedHat',
        puppetversion: '4.4.0'
      }
    end

    context 'default' do
      it { is_expected.not_to contain_package('7zip') }
      it { is_expected.not_to contain_file('/opt/awscli-bundle') }
      it { is_expected.not_to contain_archive('awscli-bundle.zip') }
      it { is_expected.not_to contain_exec('install_aws_cli') }
      it { is_expected.to compile.with_all_deps }
      it { is_expected.to contain_class('archive::params') }
    end

    context 'with aws_cli' do
      let(:params) do
        {
          aws_cli_install: true
        }
      end

      it { is_expected.to contain_file('/opt/awscli-bundle') }
      it { is_expected.to contain_archive('awscli-bundle.zip') }
      it { is_expected.to contain_exec('install_aws_cli') }
    end
  end

  describe 'Windows' do
    let(:default_facts) do
      {
        os: { family: 'Windows' },
        operatingsystem: 'Windows',
        archive_windir: 'C:/staging'
      }
    end

    context 'default 7zip chcolatey package' do
      let(:facts) do
        {
          puppetversion: '4.4.0'
        }.merge(default_facts)
      end

      it do
        is_expected.to contain_package('7zip').with(
          name: '7zip',
          provider: 'chocolatey'
        )
      end
      it { is_expected.not_to contain_archive('awscli-bundle.zip') }
    end

    context 'with 7zip msi package' do
      let(:facts) do
        {
          puppetversion: '3.4.3 (Puppet Enterprise 3.2.3)'
        }.merge(default_facts)
      end

      let(:params) do
        {
          seven_zip_name: '7-Zip 9.20 (x64 edition)',
          seven_zip_source: 'C:/Windows/Temp/7z920-x64.msi',
          seven_zip_provider: 'windows'
        }
      end

      it do
        is_expected.to contain_package('7zip').with(
          name: '7-Zip 9.20 (x64 edition)',
          source: 'C:/Windows/Temp/7z920-x64.msi',
          provider: 'windows'
        )
      end
    end

    context 'without 7zip' do
      let(:facts) do
        {
          puppetversion: '3.4.3 (Puppet Enterprise 3.2.3)'
        }.merge(default_facts)
      end

      let(:params) do
        {
          seven_zip_provider: ''
        }
      end

      it { is_expected.not_to contain_package('7zip') }
    end
  end
end
