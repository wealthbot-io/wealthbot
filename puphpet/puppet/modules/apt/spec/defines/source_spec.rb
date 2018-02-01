require 'spec_helper'

describe 'apt::source' do
  GPG_KEY_ID = '6F6B15509CF8E59E6E469F327F438280EF8D349F'.freeze

  let :pre_condition do
    'class { "apt": }'
  end

  let :title do
    'my_source'
  end

  context 'defaults' do
    context 'without location' do
      let :facts do
        {
          os: { family: 'Debian', name: 'Debian', release: { major: '7', full: '7.0' } },
          osfamily: 'Debian',
          lsbdistcodename: 'wheezy',
          puppetversion: Puppet.version,
        }
      end

      it do
        is_expected.to raise_error(Puppet::Error, %r{source entry without specifying a location})
      end
    end
    context 'with location' do
      let :facts do
        {
          os: { family: 'Debian', name: 'Debian', release: { major: '7', full: '7.0' } },
          lsbdistid: 'Debian',
          lsbdistcodename: 'wheezy',
          osfamily: 'Debian',
          puppetversion: Puppet.version,
        }
      end
      let(:params) { { location: 'hello.there' } }

      it {
        is_expected.to contain_apt__setting('list-my_source').with(ensure: 'present').without_content(%r{# my_source\ndeb-src hello.there wheezy main\n})
      }
    end
  end

  describe 'no defaults' do
    let :facts do
      {
        os: { family: 'Debian', name: 'Debian', release: { major: '7', full: '7.0' } },
        lsbdistid: 'Debian',
        lsbdistcodename: 'wheezy',
        osfamily: 'Debian',
        operatingsystem: 'Debian',
        lsbdistrelease: '7.0',
        puppetversion: Puppet.version,
      }
    end

    context 'with complex pin' do
      let :params do
        {
          location: 'hello.there',
          pin: { 'release' => 'wishwash',
                 'explanation' => 'wishwash',
                 'priority'    => 1001 },
        }
      end

      it {
        is_expected.to contain_apt__setting('list-my_source').with(ensure: 'present').with_content(%r{hello.there wheezy main\n})
      }

      it { is_expected.to contain_file('/etc/apt/sources.list.d/my_source.list').that_notifies('Class[Apt::Update]') }

      it {
        is_expected.to contain_apt__pin('my_source').that_comes_before('Apt::Setting[list-my_source]').with(ensure: 'present',
                                                                                                            priority: 1001,
                                                                                                            explanation: 'wishwash',
                                                                                                            release: 'wishwash')
      }
    end

    context 'with simple key' do
      let :params do
        {
          comment: 'foo',
          location: 'http://debian.mirror.iweb.ca/debian/',
          release: 'sid',
          repos: 'testing',
          key: GPG_KEY_ID,
          pin: '10',
          architecture: 'x86_64',
          allow_unsigned: true,
        }
      end

      it {
        is_expected.to contain_apt__setting('list-my_source').with(ensure: 'present').with_content(%r{# foo\ndeb \[arch=x86_64 trusted=yes\] http://debian.mirror.iweb.ca/debian/ sid testing\n})
                                                             .without_content(%r{deb-src})
      }

      it {
        is_expected.to contain_apt__pin('my_source').that_comes_before('Apt::Setting[list-my_source]').with(ensure: 'present',
                                                                                                            priority: '10',
                                                                                                            origin: 'debian.mirror.iweb.ca')
      }

      it {
        is_expected.to contain_apt__key("Add key: #{GPG_KEY_ID} from Apt::Source my_source").that_comes_before('Apt::Setting[list-my_source]').with(ensure: 'present',
                                                                                                                                                    id: GPG_KEY_ID)
      }
    end

    context 'with complex key' do
      let :params do
        {
          comment: 'foo',
          location: 'http://debian.mirror.iweb.ca/debian/',
          release: 'sid',
          repos: 'testing',
          key: { 'id' => GPG_KEY_ID, 'server' => 'pgp.mit.edu',
                 'content' => 'GPG key content',
                 'source'  => 'http://apt.puppetlabs.com/pubkey.gpg' },
          pin: '10',
          architecture: 'x86_64',
          allow_unsigned: true,
        }
      end

      it {
        is_expected.to contain_apt__setting('list-my_source').with(ensure: 'present').with_content(%r{# foo\ndeb \[arch=x86_64 trusted=yes\] http://debian.mirror.iweb.ca/debian/ sid testing\n})
                                                             .without_content(%r{deb-src})
      }

      it {
        is_expected.to contain_apt__pin('my_source').that_comes_before('Apt::Setting[list-my_source]').with(ensure: 'present',
                                                                                                            priority: '10',
                                                                                                            origin: 'debian.mirror.iweb.ca')
      }

      it {
        is_expected.to contain_apt__key("Add key: #{GPG_KEY_ID} from Apt::Source my_source").that_comes_before('Apt::Setting[list-my_source]').with(ensure: 'present',
                                                                                                                                                    id: GPG_KEY_ID,
                                                                                                                                                    server: 'pgp.mit.edu',
                                                                                                                                                    content: 'GPG key content',
                                                                                                                                                    source: 'http://apt.puppetlabs.com/pubkey.gpg')
      }
    end

    context 'with simple key' do
      let :params do
        {
          comment: 'foo',
          location: 'http://debian.mirror.iweb.ca/debian/',
          release: 'sid',
          repos: 'testing',
          key: GPG_KEY_ID,
          pin: '10',
          architecture: 'x86_64',
          allow_unsigned: true,
        }
      end

      it {
        is_expected.to contain_apt__setting('list-my_source').with(ensure: 'present').with_content(%r{# foo\ndeb \[arch=x86_64 trusted=yes\] http://debian.mirror.iweb.ca/debian/ sid testing\n})
                                                             .without_content(%r{deb-src})
      }

      it {
        is_expected.to contain_apt__pin('my_source').that_comes_before('Apt::Setting[list-my_source]').with(ensure: 'present',
                                                                                                            priority: '10',
                                                                                                            origin: 'debian.mirror.iweb.ca')
      }

      it {
        is_expected.to contain_apt__key("Add key: #{GPG_KEY_ID} from Apt::Source my_source").that_comes_before('Apt::Setting[list-my_source]').with(ensure: 'present',
                                                                                                                                                    id: GPG_KEY_ID)
      }
    end
  end

  context 'allow_unsigned true' do
    let :facts do
      {
        os: { family: 'Debian', name: 'Debian', release: { major: '7', full: '7.0' } },
        lsbdistid: 'Debian',
        lsbdistcodename: 'wheezy',
        osfamily: 'Debian',
        puppetversion: Puppet.version,
      }
    end
    let :params do
      {
        location: 'hello.there',
        allow_unsigned: true,
      }
    end

    it {
      is_expected.to contain_apt__setting('list-my_source').with(ensure: 'present').with_content(%r{# my_source\ndeb \[trusted=yes\] hello.there wheezy main\n})
    }
  end

  context 'architecture equals x86_64' do
    let :facts do
      {
        os: { family: 'Debian', name: 'Debian', release: { major: '7', full: '7.0' } },
        lsbdistid: 'Debian',
        lsbdistcodename: 'wheezy',
        osfamily: 'Debian',
        puppetversion: Puppet.version,
      }
    end
    let :params do
      {
        location: 'hello.there',
        include: { 'deb' => false, 'src' => true },
        architecture: 'x86_64',
      }
    end

    it {
      is_expected.to contain_apt__setting('list-my_source').with(ensure: 'present').with_content(%r{# my_source\ndeb-src \[arch=x86_64\] hello.there wheezy main\n})
    }
  end

  context 'with architecture fact and unset architecture parameter' do
    let :facts do
      {
        architecture: 'amd64',
        os: { family: 'Debian', name: 'Debian', release: { major: '7', full: '7.0' } },
        lsbdistid: 'Debian',
        lsbdistcodename: 'wheezy',
        osfamily: 'Debian',
        puppetversion: Puppet.version,
      }
    end
    let :params do
      {
        location: 'hello.there',
        include: { 'deb' => false, 'src' => true },
      }
    end

    it {
      is_expected.to contain_apt__setting('list-my_source').with(ensure: 'present').with_content(%r{# my_source\ndeb-src hello.there wheezy main\n})
    }
  end

  context 'include_src => true' do
    let :facts do
      {
        os: { family: 'Debian', name: 'Debian', release: { major: '7', full: '7.0' } },
        lsbdistid: 'Debian',
        lsbdistcodename: 'wheezy',
        osfamily: 'Debian',
        puppetversion: Puppet.version,
      }
    end
    let :params do
      {
        location: 'hello.there',
        include: { 'src' => true },
      }
    end

    it {
      is_expected.to contain_apt__setting('list-my_source').with(ensure: 'present').with_content(%r{# my_source\ndeb hello.there wheezy main\ndeb-src hello.there wheezy main\n})
    }
  end

  context 'include deb => false' do
    let :facts do
      {
        os: { family: 'Debian', name: 'Debian', release: { major: '7', full: '7.0' } },
        lsbdistid: 'debian',
        lsbdistcodename: 'wheezy',
        osfamily: 'debian',
        puppetversion: Puppet.version,
      }
    end
    let :params do
      {
        include: { 'deb' => false },
        location: 'hello.there',
      }
    end

    it {
      is_expected.to contain_apt__setting('list-my_source').with(ensure: 'present').without_content(%r{deb-src hello.there wheezy main\n})
    }
    it { is_expected.to contain_apt__setting('list-my_source').without_content(%r{deb hello.there wheezy main\n}) }
  end

  context 'include src => true and include deb => false' do
    let :facts do
      {
        os: { family: 'Debian', name: 'Debian', release: { major: '7', full: '7.0' } },
        lsbdistid: 'debian',
        lsbdistcodename: 'wheezy',
        osfamily: 'debian',
        puppetversion: Puppet.version,
      }
    end
    let :params do
      {
        include: { 'deb' => false, 'src' => true },
        location: 'hello.there',
      }
    end

    it {
      is_expected.to contain_apt__setting('list-my_source').with(ensure: 'present').with_content(%r{deb-src hello.there wheezy main\n})
    }
    it { is_expected.to contain_apt__setting('list-my_source').without_content(%r{deb hello.there wheezy main\n}) }
  end

  context 'ensure => absent' do
    let :facts do
      {
        os: { family: 'Debian', name: 'Debian', release: { major: '7', full: '7.0' } },
        lsbdistid: 'Debian',
        lsbdistcodename: 'wheezy',
        osfamily: 'Debian',
        puppetversion: Puppet.version,
      }
    end
    let :params do
      {
        ensure: 'absent',
      }
    end

    it {
      is_expected.to contain_apt__setting('list-my_source').with(ensure: 'absent')
    }
  end

  describe 'validation' do
    context 'no release' do
      let :facts do
        {
          os: { family: 'Debian', name: 'Debian', release: { major: '7', full: '7.0' } },
          lsbdistid: 'Debian',
          osfamily: 'Debian',
          puppetversion: Puppet.version,
        }
      end
      let(:params) { { location: 'hello.there' } }

      it do
        is_expected.to raise_error(Puppet::Error, %r{lsbdistcodename fact not available: release parameter required})
      end
    end

    context 'release is empty string' do
      let :facts do
        {
          os: { family: 'Debian', name: 'Debian', release: { major: '7', full: '7.0' } },
          lsbdistid: 'Debian',
          osfamily: 'Debian',
          puppetversion: Puppet.version,
        }
      end
      let(:params) { { location: 'hello.there', release: '' } }

      it { is_expected.to contain_apt__setting('list-my_source').with_content(%r{hello\.there  main}) }
    end

    context 'invalid pin' do
      let :facts do
        {
          os: { family: 'Debian', name: 'Debian', release: { major: '7', full: '7.0' } },
          lsbdistid: 'Debian',
          lsbdistcodename: 'wheezy',
          osfamily: 'Debian',
          puppetversion: Puppet.version,
        }
      end
      let :params do
        {
          location: 'hello.there',
          pin: true,
        }
      end

      it do
        is_expected.to raise_error(Puppet::Error, %r{expects a value})
      end
    end

    context 'with notify_update = undef (default)' do
      let :facts do
        {
          os: { family: 'Debian', name: 'Debian', release: { major: '7', full: '7.0' } },
          lsbdistid: 'Debian',
          lsbdistcodename: 'wheezy',
          osfamily: 'Debian',
          puppetversion: Puppet.version,
        }
      end
      let :params do
        {
          location: 'hello.there',
        }
      end

      it { is_expected.to contain_apt__setting("list-#{title}").with_notify_update(true) }
    end

    context 'with notify_update = true' do
      let :facts do
        {
          os: { family: 'Debian', name: 'Debian', release: { major: '7', full: '7.0' } },
          lsbdistid: 'Debian',
          lsbdistcodename: 'wheezy',
          osfamily: 'Debian',
          puppetversion: Puppet.version,
        }
      end
      let :params do
        {
          location: 'hello.there',
          notify_update: true,
        }
      end

      it { is_expected.to contain_apt__setting("list-#{title}").with_notify_update(true) }
    end

    context 'with notify_update = false' do
      let :facts do
        {
          os: { family: 'Debian', name: 'Debian', release: { major: '7', full: '7.0' } },
          lsbdistid: 'Debian',
          lsbdistcodename: 'wheezy',
          osfamily: 'Debian',
          puppetversion: Puppet.version,
        }
      end
      let :params do
        {
          location: 'hello.there',
          notify_update: false,
        }
      end

      it { is_expected.to contain_apt__setting("list-#{title}").with_notify_update(false) }
    end
  end
end
