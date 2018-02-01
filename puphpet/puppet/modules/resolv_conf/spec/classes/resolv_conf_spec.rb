require 'spec_helper'

on_supported_os.each_value do |f|
  describe 'resolv_conf' do
    let(:facts) { { domain: 'example.com' } }

    let :default_params do
      {
        nameservers: ['192.168.0.1', '192.168.1.1', '192.168.2.1']
      }
    end

    [
      {
        searchpath: 'example.com',
        nameservers: ['192.168.0.1', '192.168.1.1', '192.168.2.1'],
        domainname: 'example.com'
      },
      {
        searchpath: ['example.com', 'example.de'],
        nameservers: ['192.168.0.1', '192.168.1.1', '192.168.2.1'],
        domainname: 'example.com',
        options: ['timeout:2', 'attempts:3']
      },
      {
        searchpath: 'example.com',
        nameservers: ['192.168.0.1', '192.168.1.1', '192.168.2.1'],
        domainname: 'example.com',
        options: ['timeout:2', 'attempts:3']
      }
    ].each do |param_set|
      describe 'when setting searchpath and domainname' do
        let :param_hash do
          default_params.merge(param_set)
        end

        let :params do
          param_set
        end

        let(:facts) do
          f.merge(super())
        end

        describe "on #{f[:os]}" do
          it 'fails to compile' do
            expect { is_expected.to compile }.to raise_error(%r{domainname and searchpath are mutually exclusive parameters})
          end
        end
      end
    end

    [
      {
        searchpath: 'example.com',
        nameservers: ['192.168.0.1', '192.168.1.1', '192.168.2.1']
      },
      {
        searchpath: ['example.com', 'example.de'],
        nameservers: ['192.168.0.1', '192.168.1.1', '192.168.2.1']
      },
      {
        searchpath: 'example.com',
        nameservers: ['192.168.0.1', '192.168.1.1', '192.168.2.1'],
        options: ['timeout:2', 'attempts:3']
      }
    ].each do |param_set|
      describe 'when setting searchpath without domainname' do
        let :param_hash do
          default_params.merge(param_set)
        end

        let :params do
          param_set
        end

        let(:facts) do
          f.merge(super())
        end

        describe "on #{f[:os]}" do
          it do
            is_expected.to contain_file('/etc/resolv.conf').with(
              'ensure'  => 'file',
              'owner'   => 'root',
              'group'   => 0,
              'mode'    => '0644'
            )
          end

          it 'compiles the template based on the class parameters' do
            content = param_value(
              catalogue,
              'file',
              '/etc/resolv.conf',
              'content'
            )

            expected_lines = []
            if param_hash[:searchpath].empty?
              expected_lines.push("domain #{param_hash[:domainname]}")
            elsif param_hash[:searchpath].is_a?(Array)
              expected_lines.push('search ' + param_hash[:searchpath].join(' '))
            else
              expected_lines.push("search #{param_hash[:searchpath]}")
            end

            param_hash[:nameservers].each do |ns|
              expected_lines.push("nameserver #{ns}")
            end

            if param_hash[:options] && !param_hash[:options].empty?
              param_hash[:options].each do |option|
                expected_lines.push("options #{option}")
              end
            end
            (content.split("\n") & expected_lines).should =~ expected_lines
          end
        end
      end
    end

    [
      {
        nameservers: ['192.168.0.1', '192.168.1.1', '192.168.2.1'],
        domainname: 'example.com'
      },
      {
        nameservers: ['192.168.0.1', '192.168.1.1', '192.168.2.1'],
        domainname: 'example.com',
        options: ['timeout:2', 'attempts:3']
      }
    ].each do |param_set|
      describe 'when setting domainname without searchpath' do
        let :param_hash do
          default_params.merge(param_set)
        end

        let :params do
          param_set
        end

        let(:facts) do
          f.merge(super())
        end

        describe "on #{f[:os]}" do
          it do
            is_expected.to contain_file('/etc/resolv.conf').with(
              'ensure'  => 'file',
              'owner'   => 'root',
              'group'   => 0,
              'mode'    => '0644'
            )
          end

          it 'compiles the template based on the class parameters' do
            content = param_value(
              catalogue,
              'file',
              '/etc/resolv.conf',
              'content'
            )
            expected_lines = [
              "domain #{param_hash[:domainname]}"
            ]

            param_hash[:nameservers].each do |ns|
              expected_lines.push("nameserver #{ns}")
            end

            if param_hash[:options] && !param_hash[:options].empty?
              param_hash[:options].each do |option|
                expected_lines.push("options #{option}")
              end
            end
            (content.split("\n") & expected_lines).should =~ expected_lines
          end
        end
      end
    end

    [
      {
        nameservers: ['192.168.0.1', '192.168.1.1', '192.168.2.1']
      },
      {
        nameservers: ['192.168.0.1', '192.168.1.1', '192.168.2.1'],
        options: ['timeout:2', 'attempts:3']
      },
      {
        nameservers: ['192.168.0.1', '192.168.1.1', '192.168.2.1'],
        options: ['timeout:2', 'attempts:3']
      }
    ].each do |param_set|
      describe 'when setting neither searchpath nor domainname' do
        let :param_hash do
          default_params.merge(param_set)
        end

        let :params do
          param_set
        end

        let(:facts) do
          f.merge(super())
        end

        describe "on #{f[:os]}" do
          it do
            is_expected.to contain_file('/etc/resolv.conf').with(
              'ensure'  => 'file',
              'owner'   => 'root',
              'group'   => 0,
              'mode'    => '0644'
            )
          end

          it 'compiles the template based on the class parameters' do
            content = param_value(
              catalogue,
              'file',
              '/etc/resolv.conf',
              'content'
            )
            expected_lines = [
              "domain #{facts[:domain]}"
            ]

            param_hash[:nameservers].each do |ns|
              expected_lines.push("nameserver #{ns}")
            end

            if param_hash[:options] && !param_hash[:options].empty?
              param_hash[:options].each do |option|
                expected_lines.push("options #{option}")
              end
            end
            (content.split("\n") & expected_lines).should =~ expected_lines
          end
        end
      end
    end

    [
      {
        nameservers: ['192.168.0.1', '192.168.1.1', '192.168.2.1'],
        use_resolvconf: true
      }
    ].each do |param_set|
      describe 'when setting neither searchpath nor domainname' do
        let :param_hash do
          default_params.merge(param_set)
        end

        let :params do
          param_set
        end

        let(:facts) do
          f.merge(super())
        end

        describe "on #{f[:os]}" do
          it do
            is_expected.to contain_file('/etc/resolv.conf').with(
              'ensure' => 'link',
              'target' => '/run/resolvconf/resolv.conf'
            )
          end

          it do
            is_expected.to contain_file('/run/resolvconf/resolv.conf').with(
              'ensure'  => 'file',
              'owner'   => 'root',
              'group'   => 0,
              'mode'    => '0644'
            )
          end

          it 'compiles the template based on the class parameters' do
            content = param_value(
              catalogue,
              'file',
              '/run/resolvconf/resolv.conf',
              'content'
            )
            expected_lines = [
              "domain #{facts[:domain]}"
            ]

            param_hash[:nameservers].each do |ns|
              expected_lines.push("nameserver #{ns}")
            end

            if param_hash[:options] && !param_hash[:options].empty?
              param_hash[:options].each do |option|
                expected_lines.push("options #{option}")
              end
            end
            (content.split("\n") & expected_lines).should =~ expected_lines
          end
        end
      end
    end
  end
end
