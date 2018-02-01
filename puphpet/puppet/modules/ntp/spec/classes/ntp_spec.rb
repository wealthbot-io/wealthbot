require 'spec_helper'

on_supported_os.reject { |_, f| f[:os]['family'] == 'Solaris' }.each do |os, f|
  describe 'ntp' do
    let(:facts) { { is_virtual: 'false' } }

    context "on #{os}" do
      let(:facts) do
        f.merge(super())
      end

      it { is_expected.to compile.with_all_deps }

      it { is_expected.to contain_class('ntp::install') }
      it { is_expected.to contain_class('ntp::config') }
      it { is_expected.to contain_class('ntp::service') }

      describe 'ntp::config' do
        it { is_expected.to contain_file('/etc/ntp.conf').with_owner('0') }
        it { is_expected.to contain_file('/etc/ntp.conf').with_group('0') }
        it { is_expected.to contain_file('/etc/ntp.conf').with_mode('0644') }

        if f[:os]['family'] == 'RedHat'
          it { is_expected.to contain_file('/etc/ntp/step-tickers').with_owner('0') }
          it { is_expected.to contain_file('/etc/ntp/step-tickers').with_group('0') }
          it { is_expected.to contain_file('/etc/ntp/step-tickers').with_mode('0644') }
        end

        if f[:os]['family'] == 'Suse' && f[:os]['release']['major'] == '12'
          it { is_expected.to contain_file('/var/run/ntp/servers-netconfig').with_ensure_absent }
        end

        describe 'allows template to be overridden with erb template' do
          let(:params) { { config_template: 'my_ntp/ntp.conf.erb' } }

          it { is_expected.to contain_file('/etc/ntp.conf').with_content(%r{erbserver1}) }
        end

        describe 'allows template to be overridden with epp template' do
          let(:params) { { config_epp: 'my_ntp/ntp.conf.epp' } }

          it { is_expected.to contain_file('/etc/ntp.conf').with_content(%r{eppserver1}) }
        end

        describe 'keys' do
          context 'when enabled' do
            let(:params) do
              {
                keys_enable: true,
                keys_trusted: [1, 2, 3],
                keys_controlkey: 2,
                keys_requestkey: 3,
              }
            end

            it {
              is_expected.to contain_file('/etc/ntp.conf').with('content' => %r{trustedkey 1 2 3})
            }
            it {
              is_expected.to contain_file('/etc/ntp.conf').with('content' => %r{controlkey 2})
            }
            it {
              is_expected.to contain_file('/etc/ntp.conf').with('content' => %r{requestkey 3})
            }
          end
        end

        context 'when disabled' do
          let(:params) do
            {
              keys_enable: false,
              keys_trusted: [1, 2, 3],
              keys_controlkey: 2,
              keys_requestkey: 3,
            }
          end

          it {
            is_expected.not_to contain_file('/etc/ntp.conf').with('content' => %r{trustedkey 1 2 3})
          }
          it {
            is_expected.not_to contain_file('/etc/ntp.conf').with('content' => %r{controlkey 2})
          }
          it {
            is_expected.not_to contain_file('/etc/ntp.conf').with('content' => %r{requestkey 3})
          }
        end

        describe 'preferred servers' do
          context 'when set' do
            let(:params) do
              {
                servers: %w[a b c d],
                preferred_servers: %w[a b],
                iburst_enable: false,
              }
            end

            it {
              is_expected.to contain_file('/etc/ntp.conf').with('content' => %r{server a prefer( maxpoll 9)?\nserver b prefer( maxpoll 9)?\nserver c( maxpoll 9)?\nserver d( maxpoll 9)?})
            }
          end
          context 'when not set' do
            let(:params) do
              {
                servers: %w[a b c d],
                preferred_servers: [],
              }
            end

            it {
              is_expected.not_to contain_file('/etc/ntp.conf').with('content' => %r{server a prefer})
            }
          end
        end

        describe 'noselect servers' do
          context 'when set' do
            let(:params) do
              {
                servers: %w[a b c d],
                noselect_servers: %w[a b],
                iburst_enable: false,
              }
            end

            it {
              is_expected.to contain_file('/etc/ntp.conf').with('content' => %r{server a (maxpoll 9 )?noselect\nserver b (maxpoll 9 )?noselect\nserver c( maxpoll 9)?\nserver d( maxpoll 9)?})
            }
          end
          context 'when not set' do
            let(:params) do
              {
                servers: %w[a b c d],
                noselect_servers: [],
              }
            end

            it {
              is_expected.not_to contain_file('/etc/ntp.conf').with('content' => %r{server a noselect})
            }
          end
        end
        describe 'specified interfaces' do
          context 'when set' do
            let(:params) do
              {
                servers: %w[a b c d],
                interfaces: ['127.0.0.1', 'a.b.c.d'],
              }
            end

            it {
              is_expected.to contain_file('/etc/ntp.conf').with('content' => %r{interface ignore wildcard\ninterface listen 127.0.0.1\ninterface listen a.b.c.d})
            }
          end
          context 'when not set' do
            let(:params) do
              {
                servers: %w[a b c d],
              }
            end

            it {
              is_expected.not_to contain_file('/etc/ntp.conf').with('content' => %r{interface ignore wildcard})
            }
          end
        end

        describe 'specified ignore interfaces' do
          context 'when set' do
            let(:params) do
              {
                interfaces: ['a.b.c.d'],
                interfaces_ignore: %w[wildcard ipv6],
              }
            end

            it {
              is_expected.to contain_file('/etc/ntp.conf').with('content' => %r{interface ignore wildcard\ninterface ignore ipv6\ninterface listen a.b.c.d})
            }
          end
          context 'when not set' do
            let(:params) do
              {
                interfaces: ['127.0.0.1'],
                servers: %w[a b c d],
              }
            end

            it {
              is_expected.to contain_file('/etc/ntp.conf').with('content' => %r{interface ignore wildcard\ninterface listen 127.0.0.1})
            }
          end
        end

        describe 'with parameter disable_auth' do
          context 'when set to true' do
            let(:params) do
              {
                disable_auth: true,
              }
            end

            it 'contains disable auth setting' do
              is_expected.to contain_file('/etc/ntp.conf').with('content' => %r{^disable auth\n})
            end
          end
          context 'when set to false' do
            let(:params) do
              {
                disable_auth: false,
              }
            end

            it 'does not contain disable auth setting' do
              is_expected.not_to contain_file('/etc/ntp.conf').with('content' => %r{^disable auth\n})
            end
          end
        end

        describe 'with parameter disable_dhclient' do
          context 'when set to true' do
            let(:params) do
              {
                disable_dhclient: true,
              }
            end

            it 'contains disable ntp-servers setting' do
              is_expected.to contain_augeas('disable ntp-servers in dhclient.conf')
            end
            it 'contains dhcp file' do
              is_expected.to contain_file('/var/lib/ntp/ntp.conf.dhcp').with_ensure('absent')
            end
          end
          context 'when set to false' do
            let(:params) do
              {
                disable_dhclient: false,
              }
            end

            it 'does not contain disable ntp-servers setting' do
              is_expected.not_to contain_augeas('disable ntp-servers in dhclient.conf')
            end
            it 'does not contain dhcp file' do
              is_expected.not_to contain_file('/var/lib/ntp/ntp.conf.dhcp').with_ensure('absent')
            end
          end
        end
        describe 'with parameter disable_kernel' do
          context 'when set to true' do
            let(:params) do
              {
                disable_kernel: true,
              }
            end

            it 'contains disable kernel setting' do
              is_expected.to contain_file('/etc/ntp.conf').with('content' => %r{^disable kernel\n})
            end
          end
          context 'when set to false' do
            let(:params) do
              {
                disable_kernel: false,
              }
            end

            it 'does not contain disable kernel setting' do
              is_expected.not_to contain_file('/etc/ntp.conf').with('content' => %r{^disable kernel\n})
            end
          end
        end
        describe 'with parameter disable_monitor' do
          context 'default' do
            let(:params) do
              {
              }
            end

            it 'contains disable monitor setting' do
              is_expected.to contain_file('/etc/ntp.conf').with('content' => %r{^disable monitor\n})
            end
          end
          context 'when set to true' do
            let(:params) do
              {
                disable_monitor: true,
              }
            end

            it 'contains disable monitor setting' do
              is_expected.to contain_file('/etc/ntp.conf').with('content' => %r{^disable monitor\n})
            end
          end
          context 'when set to false' do
            let(:params) do
              {
                disable_monitor: false,
              }
            end

            it 'does not contain disable monitor setting' do
              is_expected.not_to contain_file('/etc/ntp.conf').with('content' => %r{^disable monitor\n})
            end
          end
        end
        describe 'with parameter enable_mode7' do
          context 'default' do
            let(:params) do
              {
              }
            end

            it 'does not contain enable mode7 setting' do
              is_expected.not_to contain_file('/etc/ntp.conf').with('content' => %r{^enable mode7\n})
            end
          end
          context 'when set to true' do
            let(:params) do
              {
                enable_mode7: true,
              }
            end

            it 'contains enable mode7 setting' do
              is_expected.to contain_file('/etc/ntp.conf').with('content' => %r{^enable mode7\n})
            end
          end
          context 'when set to false' do
            let(:params) do
              {
                enable_mode7: false,
              }
            end

            it 'does not contain enable mode7 setting' do
              is_expected.not_to contain_file('/etc/ntp.conf').with('content' => %r{^enable mode7\n})
            end
          end
        end
        describe 'with parameter broadcastclient' do
          context 'when set to true' do
            let(:params) do
              {
                broadcastclient: true,
              }
            end

            it 'contains broadcastclient setting' do
              is_expected.to contain_file('/etc/ntp.conf').with('content' => %r{^broadcastclient\n})
            end
          end
          context 'when set to false' do
            let(:params) do
              {
                broadcastclient: false,
              }
            end

            it 'does not contain broadcastclient setting' do
              is_expected.not_to contain_file('/etc/ntp.conf').with('content' => %r{^broadcastclient\n})
            end
          end
          context 'when setting custom config_dir' do
            let(:params) do
              {
                keys_enable: true,
                config_dir: '/tmp/foo',
                keys_file: '/tmp/foo/ntp.keys',
              }
            end

            it 'contains custom config directory' do
              is_expected.to contain_file('/tmp/foo').with(
                'ensure' => 'directory', 'owner' => '0', 'group' => '0', 'mode' => '0775', 'recurse' => 'false',
              )
            end
          end
          context 'when manually setting conf file mode to 0777' do
            let(:params) do
              {
                config_file_mode: '0777',
              }
            end

            it 'contains file mode of 0777' do
              is_expected.to contain_file('/etc/ntp.conf').with_mode('0777')
            end
          end
        end

        context 'when choosing the default pool servers' do
          case f[:os]['family']
          when 'RedHat'
            if f[:os]['name'] == 'Fedora'
              it 'uses the fedora ntp servers' do
                is_expected.to contain_file('/etc/ntp.conf').with('content' => %r{server \d.fedora.pool.ntp.org})
              end
              it do
                is_expected.to contain_file('/etc/ntp/step-tickers').with('content' => %r{\d.fedora.pool.ntp.org})
              end
            else
              it 'uses the centos ntp servers' do
                is_expected.to contain_file('/etc/ntp.conf').with('content' => %r{server \d.centos.pool.ntp.org})
              end
              it do
                is_expected.to contain_file('/etc/ntp/step-tickers').with('content' => %r{\d.centos.pool.ntp.org})
              end
            end
          when 'Debian'
            it 'uses the debian ntp servers' do
              is_expected.to contain_file('/etc/ntp.conf').with('content' => %r{server \d.debian.pool.ntp.org iburst\n})
            end
          when 'Suse'
            it 'uses the opensuse ntp servers' do
              is_expected.to contain_file('/etc/ntp.conf').with('content' => %r{server \d.opensuse.pool.ntp.org})
            end
          when 'FreeBSD'
            it 'uses the freebsd ntp servers' do
              is_expected.to contain_file('/etc/ntp.conf').with('content' => %r{server \d.freebsd.pool.ntp.org iburst maxpoll 9})
            end
          when 'Archlinux'
            it 'uses the Archlinux NTP servers' do
              is_expected.to contain_file('/etc/ntp.conf').with('content' => %r{server \d.arch.pool.ntp.org})
            end
          when 'Solaris', 'Gentoo'
            it 'uses the generic NTP pool servers' do
              is_expected.to contain_file('/etc/inet/ntp.conf').with('content' => %r{server \d.pool.ntp.org})
            end
          else
            it {
              expect { catalogue }.to raise_error(
                %r{The ntp module is not supported on an unsupported based system.},
              )
            }
          end
        end
      end

      describe 'ntp::install' do
        let(:params) { { package_ensure: 'present', package_name: ['ntp'], package_manage: true } }

        it {
          is_expected.to contain_package('ntp').with(
            ensure: 'present',
          )
        }

        describe 'should allow package ensure to be overridden' do
          let(:params) { { package_ensure: 'latest', package_name: ['ntp'], package_manage: true } }

          it { is_expected.to contain_package('ntp').with_ensure('latest') }
        end

        describe 'should allow the package name to be overridden' do
          let(:params) { { package_ensure: 'present', package_name: ['hambaby'], package_manage: true } }

          it { is_expected.to contain_package('hambaby') }
        end

        describe 'should allow the package to be unmanaged' do
          let(:params) { { package_manage: false, package_name: ['ntp'] } }

          it { is_expected.not_to contain_package('ntp') }
        end
      end

      describe 'ntp::service' do
        let(:params) do
          {
            service_manage: true,
            service_enable: true,
            service_ensure: 'running',
            service_name: 'ntp',
          }
        end

        describe 'with defaults' do
          it {
            is_expected.to contain_service('ntp').with(
              enable: true,
              ensure: 'running',
              name: 'ntp',
            )
          }
        end

        describe 'service_ensure' do
          describe 'when overridden' do
            let(:params) { { service_name: 'ntp', service_ensure: 'stopped' } }

            it { is_expected.to contain_service('ntp').with_ensure('stopped') }
          end
        end

        describe 'service_manage' do
          let(:params) do
            {
              service_manage: false,
              service_enable: true,
              service_ensure: 'running',
              service_name: 'ntpd',
            }
          end

          it 'when set to false' do
            is_expected.not_to contain_service('ntp').with('enable' => true,
                                                           'ensure' => 'running',
                                                           'name'   => 'ntpd')
          end
        end
      end

      describe 'with parameter iburst_enable' do
        context 'when set to true' do
          let(:params) do
            {
              iburst_enable: true,
            }
          end

          it do
            is_expected.to contain_file('/etc/ntp.conf').with('content' => %r{iburst})
          end
        end

        context 'when set to false' do
          let(:params) do
            {
              iburst_enable: false,
            }
          end

          it do
            is_expected.not_to contain_file('/etc/ntp.conf').with('content' => %r{iburst\n})
          end
        end
      end

      describe 'with tinker parameter changed' do
        describe 'when set to false' do
          context 'when panic or stepout not overriden' do
            let(:params) do
              {
                tinker: false,
              }
            end

            it do
              is_expected.not_to contain_file('/etc/ntp.conf').with('content' => %r{^tinker })
            end
          end

          context 'when panic overriden' do
            let(:params) do
              {
                tinker: false,
                panic: 257,
              }
            end

            it do
              is_expected.not_to contain_file('/etc/ntp.conf').with('content' => %r{^tinker })
            end
          end

          context 'when stepout overriden' do
            let(:params) do
              {
                tinker: false,
                stepout: 5,
              }
            end

            it do
              is_expected.not_to contain_file('/etc/ntp.conf').with('content' => %r{^tinker })
            end
          end

          context 'when panic and stepout overriden' do
            let(:params) do
              {
                tinker: false,
                panic: 257,
                stepout: 5,
              }
            end

            it do
              is_expected.not_to contain_file('/etc/ntp.conf').with('content' => %r{^tinker })
            end
          end
        end
        describe 'when set to true' do
          context 'when only tinker set to true' do
            let(:params) do
              {
                tinker: true,
              }
            end

            it do
              is_expected.not_to contain_file('/etc/ntp.conf').with('content' => %r{^tinker })
            end
          end

          context 'when panic changed' do
            let(:params) do
              {
                tinker: true,
                panic: 257,
              }
            end

            it do
              is_expected.to contain_file('/etc/ntp.conf').with('content' => %r{^tinker panic 257\n})
            end
          end

          context 'when stepout changed' do
            let(:params) do
              {
                tinker: true,
                stepout: 5,
              }
            end

            it do
              is_expected.to contain_file('/etc/ntp.conf').with('content' => %r{^tinker stepout 5\n})
            end
          end

          context 'when panic and stepout changed' do
            let(:params) do
              {
                tinker: true,
                panic: 257,
                stepout: 5,
              }
            end

            it do
              is_expected.to contain_file('/etc/ntp.conf').with('content' => %r{^tinker panic 257 stepout 5\n})
            end
          end
        end
      end

      describe 'with parameters minpoll or maxpoll changed from default' do
        context 'when minpoll changed from default' do
          let(:params) do
            {
              minpoll: 6,
            }
          end

          it do
            is_expected.to contain_file('/etc/ntp.conf').with('content' => %r{minpoll 6})
          end
        end

        context 'when maxpoll changed from default' do
          let(:params) do
            {
              maxpoll: 12,
            }
          end

          it do
            is_expected.to contain_file('/etc/ntp.conf').with('content' => %r{maxpoll 12\n})
          end
        end

        context 'when minpoll and maxpoll changed from default simultaneously' do
          let(:params) do
            {
              minpoll: 6,
              maxpoll: 12,
            }
          end

          it do
            is_expected.to contain_file('/etc/ntp.conf').with('content' => %r{minpoll 6 maxpoll 12\n})
          end
        end
      end

      describe 'with parameter leapfile' do
        context 'when set to true' do
          let(:params) do
            {
              servers: %w[a b c d],
              leapfile: '/etc/leap-seconds.3629404800',
            }
          end

          it 'contains leapfile setting' do
            is_expected.to contain_file('/etc/ntp.conf').with('content' => %r{^leapfile \/etc\/leap-seconds\.3629404800\n})
          end
        end

        context 'when set to false' do
          let(:params) do
            {
              servers: %w[a b c d],
            }
          end

          it 'does not contain a leapfile line' do
            is_expected.not_to contain_file('/etc/ntp.conf').with('content' => %r{leapfile })
          end
        end
      end

      describe 'with parameter logfile' do
        context 'when set to true' do
          let(:params) do
            {
              servers: %w[a b c d],
              logfile: '/var/log/foobar.log',
            }
          end

          it 'contains logfile setting' do
            is_expected.to contain_file('/etc/ntp.conf').with('content' => %r{^logfile \/var\/log\/foobar\.log\n})
          end
        end

        context 'when set to false' do
          let(:params) do
            {
              servers: %w[a b c d],
            }
          end

          it 'does not contain a logfile line' do
            is_expected.not_to contain_file('/etc/ntp.conf').with('content' => %r{logfile })
          end
        end
      end

      describe 'with parameter ntpsigndsocket' do
        context 'when set to true' do
          let(:params) do
            {
              servers: %w[a b c d],
              ntpsigndsocket: '/usr/local/samba/var/lib/ntp_signd',
            }
          end

          it 'contains ntpsigndsocket setting' do
            is_expected.to contain_file('/etc/ntp.conf').with('content' => %r{^ntpsigndsocket /usr/local/samba/var/lib/ntp_signd\n})
          end
        end

        context 'when set to false' do
          let(:params) do
            {
              servers: %w[a b c d],
            }
          end

          it 'does not contain a ntpsigndsocket line' do
            is_expected.not_to contain_file('/etc/ntp.conf').with('content' => %r{ntpsigndsocket })
          end
        end
      end

      describe 'with parameter authprov' do
        context 'when set to true' do
          let(:params) do
            {
              servers: %w[a b c d],
              authprov: '/opt/novell/xad/lib64/libw32time.so 131072:4294967295 global',
            }
          end

          it 'contains authprov setting' do
            is_expected.to contain_file('/etc/ntp.conf').with('content' => %r{^authprov /opt/novell/xad/lib64/libw32time.so 131072:4294967295 global\n})
          end
        end

        context 'when set to false' do
          let(:params) do
            {
              servers: %w[a b c d],
            }
          end

          it 'does not contain a authprov line' do
            is_expected.not_to contain_file('/etc/ntp.conf').with('content' => %r{authprov })
          end
        end
      end

      describe 'with parameter tos' do
        context 'when set to true' do
          let(:params) do
            {
              tos: true,
            }
          end

          it 'contains tos setting' do
            is_expected.to contain_file('/etc/ntp.conf').with('content' => %r{^tos})
          end
        end

        context 'when set to false' do
          let(:params) do
            {
              tos: false,
            }
          end

          it 'does not contain tos setting' do
            is_expected.not_to contain_file('/etc/ntp.conf').with('content' => %r{^tos})
          end
        end
      end

      describe 'pool' do
        context 'when empty' do
          let(:params) do
            {
              pool: [],
            }
          end

          it 'does not contain a pool line' do
            is_expected.to contain_file('/etc/ntp.conf').without_content(%r{^pool})
          end
        end

        context 'set' do
          let(:params) do
            {
              pool: %w[foo bar],
            }
          end

          it 'contains the pool lines - expectation one' do
            is_expected.to contain_file('/etc/ntp.conf').with_content(%r{pool foo})
          end
          it 'contains the pool lines - expectation two' do
            is_expected.to contain_file('/etc/ntp.conf').with_content(%r{pool bar})
          end
        end
      end

      describe 'peers' do
        context 'when empty' do
          let(:params) do
            {
              peers: [],
            }
          end

          it 'does not contain a peer line' do
            is_expected.to contain_file('/etc/ntp.conf').without_content(%r{^peer})
          end
        end

        context 'set' do
          let(:params) do
            {
              peers: %w[foo bar],
            }
          end

          it 'contains the peer lines - expectation one' do
            is_expected.to contain_file('/etc/ntp.conf').with_content(%r{peer foo})
          end
          it 'contains the peer lines - expectation two' do
            is_expected.to contain_file('/etc/ntp.conf').with_content(%r{peer bar})
          end
        end
      end

      describe 'for virtual machines' do
        let :facts do
          super().merge(is_virtual: 'true')
        end

        it 'does not use local clock as a time source' do
          is_expected.not_to contain_file('/etc/ntp.conf').with('content' => %r{server.*127.127.1.0.*fudge.*127.127.1.0 stratum 10})
        end

        it 'allows large clock skews' do
          is_expected.to contain_file('/etc/ntp.conf').with('content' => %r{tinker panic 0})
        end
      end

      describe 'for physical machines' do
        let :facts do
          super().merge(is_virtual: 'false')
        end

        it 'disallows large clock skews' do
          is_expected.not_to contain_file('/etc/ntp.conf').with('content' => %r{tinker panic 0})
        end
      end
    end
  end
end
