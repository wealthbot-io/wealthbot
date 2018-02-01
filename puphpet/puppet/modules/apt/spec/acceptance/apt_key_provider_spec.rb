require 'spec_helper_acceptance'

PUPPETLABS_GPG_KEY_SHORT_ID    = 'EF8D349F'.freeze
PUPPETLABS_GPG_KEY_LONG_ID     = '7F438280EF8D349F'.freeze
PUPPETLABS_GPG_KEY_FINGERPRINT = '6F6B15509CF8E59E6E469F327F438280EF8D349F'.freeze
PUPPETLABS_APT_URL             = 'apt.puppetlabs.com'.freeze
PUPPETLABS_GPG_KEY_FILE        = 'DEB-GPG-KEY-puppet'.freeze
CENTOS_GPG_KEY_SHORT_ID        = 'C105B9DE'.freeze
CENTOS_GPG_KEY_LONG_ID         = '0946FCA2C105B9DE'.freeze
CENTOS_GPG_KEY_FINGERPRINT     = 'C1DAC52D1664E8A4386DBA430946FCA2C105B9DE'.freeze
CENTOS_REPO_URL                = 'ftp.cvut.cz/centos'.freeze
CENTOS_GPG_KEY_FILE            = 'RPM-GPG-KEY-CentOS-6'.freeze

SHOULD_NEVER_EXIST_ID          = 'EF8D349F'.freeze

KEY_CHECK_COMMAND              = 'apt-key adv --list-keys --with-colons --fingerprint | grep '.freeze
PUPPETLABS_KEY_CHECK_COMMAND   = "#{KEY_CHECK_COMMAND} #{PUPPETLABS_GPG_KEY_FINGERPRINT}".freeze
CENTOS_KEY_CHECK_COMMAND       = "#{KEY_CHECK_COMMAND} #{CENTOS_GPG_KEY_FINGERPRINT}".freeze

MAX_TIMEOUT_RETRY              = 3
TIMEOUT_RETRY_WAIT             = 5
TIMEOUT_ERROR_MATCHER = %r{no valid OpenPGP data found}

def populate_default_options_pp(value)
  default_options_pp = <<-EOS
          apt_key { 'puppetlabs':
            id     => '#{value}',
            ensure => 'present',
          }
  EOS
  default_options_pp
end

def install_key(key)
  retry_on_error_matching(MAX_TIMEOUT_RETRY, TIMEOUT_RETRY_WAIT, TIMEOUT_ERROR_MATCHER) do
    shell("apt-key adv --keyserver hkps.pool.sks-keyservers.net \
              --recv-keys #{key}")
  end
end

def apply_manifest_twice(manifest_pp)
  apply_manifest(manifest_pp, catch_failures: true)
  apply_manifest(manifest_pp, catch_changes: true)
end

invalid_key_length_pp = <<-EOS
        apt_key { 'puppetlabs':
          id => '8280EF8D349F',
        }
  EOS

ensure_absent_pp = <<-EOS
        apt_key { 'centos':
          id     => '#{CENTOS_GPG_KEY_LONG_ID}',
          ensure => 'absent',
        }
  EOS

ensure_absent_long_key_pp = <<-EOS
        apt_key { 'puppetlabs':
          id     => '#{PUPPETLABS_GPG_KEY_LONG_ID}',
          ensure => 'absent',
        }
  EOS

gpg_key_pp = <<-EOS
          apt_key { 'puppetlabs':
            id      => '#{PUPPETLABS_GPG_KEY_FINGERPRINT}',
            ensure  => 'present',
            content => "-----BEGIN PGP PUBLIC KEY BLOCK-----

  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
  3xJn
  =mMjt
  -----END PGP PUBLIC KEY BLOCK-----",
            }
  EOS

multiple_keys_pp = <<-EOS
          apt_key { 'puppetlabs':
            id      => '#{PUPPETLABS_GPG_KEY_FINGERPRINT}',
            ensure  => 'present',
            content => "-----BEGIN PGP PUBLIC KEY BLOCK-----
  Version: GnuPG v1

  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
  FPfZDNCu/TXoqyJk7434jJrcHgPryzrHFBLfEmc=
  =TREp
  -----END PGP PUBLIC KEY BLOCK----- ",
            }
  EOS

bogus_key_pp = <<-EOS
        apt_key { 'puppetlabs':
          id      => '#{PUPPETLABS_GPG_KEY_LONG_ID}',
          ensure  => 'present',
          content => 'For posterity: such content, much bogus, wow',
        }
  EOS

hkps_pool_pp = <<-EOS
        apt_key { 'puppetlabs':
          id     => '#{PUPPETLABS_GPG_KEY_LONG_ID}',
          ensure => 'present',
          server => 'hkps.pool.sks-keyservers.net',
        }
  EOS

hkp_pool_pp = <<-EOS
        apt_key { 'puppetlabs':
          id     => '#{PUPPETLABS_GPG_KEY_FINGERPRINT}',
          ensure => 'present',
          server => 'hkp://hkps.pool.sks-keyservers.net:80',
        }
  EOS

nonexistant_key_server_pp = <<-EOS
        apt_key { 'puppetlabs':
          id     => '#{PUPPETLABS_GPG_KEY_LONG_ID}',
          ensure => 'present',
          server => 'nonexistant.key.server',
        }
  EOS

dot_server_pp = <<-EOS
        apt_key { 'puppetlabs':
          id     => '#{PUPPETLABS_GPG_KEY_LONG_ID}',
          ensure => 'present',
          server => '.pgp.key.server',
        }
  EOS

http_works_pp = <<-EOS
        apt_key { 'puppetlabs':
          id     => '#{PUPPETLABS_GPG_KEY_LONG_ID}',
          ensure => 'present',
          source => 'http://#{PUPPETLABS_APT_URL}/#{PUPPETLABS_GPG_KEY_FILE}',
        }
  EOS

http_works_userinfo_pp = <<-EOS
        apt_key { 'puppetlabs':
          id     => '#{PUPPETLABS_GPG_KEY_LONG_ID}',
          ensure => 'present',
          source => 'http://dummyuser:dummypassword@#{PUPPETLABS_APT_URL}/#{PUPPETLABS_GPG_KEY_FILE}',
        }
  EOS

four_oh_four_pp = <<-EOS
        apt_key { 'puppetlabs':
          id     => '#{PUPPETLABS_GPG_KEY_LONG_ID}',
          ensure => 'present',
          source => 'http://#{PUPPETLABS_APT_URL}/herpderp.gpg',
        }
  EOS

socket_error_pp = <<-EOS
        apt_key { 'puppetlabs':
          id     => '#{PUPPETLABS_GPG_KEY_LONG_ID}',
          ensure => 'present',
          source => 'http://apt.puppetlabss.com/herpderp.gpg',
        }
  EOS

ftp_works_pp = <<-EOS
        apt_key { 'CentOS 6':
          id     => '#{CENTOS_GPG_KEY_LONG_ID}',
          ensure => 'present',
          source => 'ftp://#{CENTOS_REPO_URL}/#{CENTOS_GPG_KEY_FILE}',
        }
  EOS

ftp_550_pp = <<-EOS
        apt_key { 'CentOS 6':
          id     => '#{SHOULD_NEVER_EXIST_ID}',
          ensure => 'present',
          source => 'ftp://#{CENTOS_REPO_URL}/herpderp.gpg',
        }
  EOS

ftp_socket_error_pp = <<-EOS
        apt_key { 'puppetlabs':
          id     => '#{PUPPETLABS_GPG_KEY_LONG_ID}',
          ensure => 'present',
          source => 'ftp://apt.puppetlabss.com/herpderp.gpg',
        }
  EOS

https_works_pp = <<-EOS
        apt_key { 'puppetlabs':
          id     => '#{PUPPETLABS_GPG_KEY_LONG_ID}',
          ensure => 'present',
          source => 'https://#{PUPPETLABS_APT_URL}/#{PUPPETLABS_GPG_KEY_FILE}',
        }
  EOS

https_userinfo_pp = <<-EOS
        apt_key { 'puppetlabs':
          id     => '#{PUPPETLABS_GPG_KEY_LONG_ID}',
          ensure => 'present',
          source => 'https://dummyuser:dummypassword@#{PUPPETLABS_APT_URL}/#{PUPPETLABS_GPG_KEY_FILE}',
        }
  EOS

https_404_pp = <<-EOS
        apt_key { 'puppetlabs':
          id     => '#{SHOULD_NEVER_EXIST_ID}',
          ensure => 'present',
          source => 'https://#{PUPPETLABS_APT_URL}/herpderp.gpg',
        }
  EOS

https_socket_error_pp = <<-EOS
        apt_key { 'puppetlabs':
          id     => '#{SHOULD_NEVER_EXIST_ID}',
          ensure => 'present',
          source => 'https://apt.puppetlabss.com/herpderp.gpg',
        }
  EOS

path_exists_pp = <<-EOS
        apt_key { 'puppetlabs':
          id     => 'EF8D349F',
          ensure => 'present',
          source => '/tmp/puppetlabs-pubkey.gpg',
        }
  EOS

path_does_not_exist_pp = <<-EOS
        apt_key { 'puppetlabs':
          id     => '#{PUPPETLABS_GPG_KEY_LONG_ID}',
          ensure => 'present',
          source => '/tmp/totally_bogus.file',
        }
  EOS

path_bogus_content_pp = <<-EOS
        apt_key { 'puppetlabs':
          id     => '#{PUPPETLABS_GPG_KEY_LONG_ID}',
          ensure => 'present',
          source => '/tmp/fake-key.gpg',
        }
  EOS

debug_works_pp = <<-EOS
        apt_key { 'puppetlabs':
          id      => '#{PUPPETLABS_GPG_KEY_LONG_ID}',
          ensure  => 'present',
          options => 'debug',
        }
  EOS

fingerprint_match_pp = <<-EOS
        apt_key { 'puppetlabs':
          id      => '#{PUPPETLABS_GPG_KEY_FINGERPRINT}',
          ensure  => 'present',
          source  => 'https://#{PUPPETLABS_APT_URL}/#{PUPPETLABS_GPG_KEY_FILE}',
        }
  EOS

fingerprint_does_not_match_pp = <<-EOS
        apt_key { 'puppetlabs':
          id      => '6F6B15509CF8E59E6E469F327F438280EF8D9999',
          ensure  => 'present',
          source  => 'https://#{PUPPETLABS_APT_URL}/#{PUPPETLABS_GPG_KEY_FILE}',
        }
  EOS

describe 'apt_key' do
  before(:each) do
    # Delete twice to make sure everything is cleaned
    # up after the short key collision
    shell("apt-key del #{PUPPETLABS_GPG_KEY_SHORT_ID}",
          acceptable_exit_codes: [0, 1, 2])
    shell("apt-key del #{PUPPETLABS_GPG_KEY_SHORT_ID}",
          acceptable_exit_codes: [0, 1, 2])
  end

  describe 'default options' do
    key_versions = {
      '32bit key id'                        => PUPPETLABS_GPG_KEY_SHORT_ID.to_s,
      '64bit key id'                        => PUPPETLABS_GPG_KEY_LONG_ID.to_s,
      '160bit key fingerprint'              => PUPPETLABS_GPG_KEY_FINGERPRINT.to_s,
      '32bit lowercase key id'              => PUPPETLABS_GPG_KEY_SHORT_ID.downcase.to_s,
      '64bit lowercase key id'              => PUPPETLABS_GPG_KEY_LONG_ID.downcase.to_s,
      '160bit lowercase key fingerprint'    => PUPPETLABS_GPG_KEY_FINGERPRINT.downcase.to_s,
      '0x formatted 32bit key id'           => "0x#{PUPPETLABS_GPG_KEY_SHORT_ID}",
      '0x formatted 64bit key id'           => "0x#{PUPPETLABS_GPG_KEY_LONG_ID}",
      '0x formatted 160bit key fingerprint' => "0x#{PUPPETLABS_GPG_KEY_FINGERPRINT}",
      '0x formatted 32bit lowercase key id' => "0x#{PUPPETLABS_GPG_KEY_SHORT_ID.downcase}",
      '0x formatted 64bit lowercase key id' => "0x#{PUPPETLABS_GPG_KEY_LONG_ID.downcase}",
      '0x formatted 160bit lowercase key fingerprint' => "0x#{PUPPETLABS_GPG_KEY_FINGERPRINT.downcase}",
    }

    key_versions.each do |key, value|
      context key.to_s do
        it 'works' do
          apply_manifest_twice(populate_default_options_pp(value))
          shell(PUPPETLABS_KEY_CHECK_COMMAND)
        end
      end
    end

    context 'invalid length key id' do
      it 'fails' do
        apply_manifest(invalid_key_length_pp, expect_failures: true) do |r|
          expect(r.stderr).to match(%r{Valid values match})
        end
      end
    end
  end

  describe 'ensure =>' do
    context 'absent' do
      it 'is removed' do
        # Install the key first (retry because key pool may timeout)
        install_key(CENTOS_GPG_KEY_FINGERPRINT)
        shell(CENTOS_KEY_CHECK_COMMAND)

        # Time to remove it using Puppet
        apply_manifest_twice(ensure_absent_pp)

        shell(CENTOS_KEY_CHECK_COMMAND, acceptable_exit_codes: [1])

        # Re-Install the key (retry because key pool may timeout)
        install_key(CENTOS_GPG_KEY_FINGERPRINT)
      end
    end

    context 'absent, added with long key', unless: (fact('operatingsystem') == 'Debian' && fact('operatingsystemmajrelease') == '6') do
      it 'is removed' do
        # Install the key first (retry because key pool may timeout)
        install_key(PUPPETLABS_GPG_KEY_LONG_ID)
        shell(PUPPETLABS_KEY_CHECK_COMMAND)

        # Time to remove it using Puppet
        apply_manifest_twice(ensure_absent_long_key_pp)

        shell(PUPPETLABS_KEY_CHECK_COMMAND, acceptable_exit_codes: [1])
      end
    end
  end

  describe 'content =>' do
    context 'puppetlabs gpg key' do
      it 'works' do
        # Apply the manifest (Retry if timeout error is received from key pool)
        retry_on_error_matching(MAX_TIMEOUT_RETRY, TIMEOUT_RETRY_WAIT, TIMEOUT_ERROR_MATCHER) do
          apply_manifest(gpg_key_pp, catch_failures: true)
        end

        apply_manifest(gpg_key_pp, catch_changes: true)
        shell(PUPPETLABS_KEY_CHECK_COMMAND)
      end
    end

    context 'multiple keys' do
      it 'runs without errors' do
        apply_manifest_twice(multiple_keys_pp)
        shell(PUPPETLABS_KEY_CHECK_COMMAND)
      end
    end

    context 'bogus key' do
      it 'fails' do
        apply_manifest(bogus_key_pp, expect_failures: true) do |r|
          expect(r.stderr).to match(%r{no valid OpenPGP data found})
        end
      end
    end
  end

  describe 'server =>' do
    context 'hkps.pool.sks-keyservers.net' do
      it 'works' do
        # Apply the manifest (Retry if timeout error is received from key pool)
        retry_on_error_matching(MAX_TIMEOUT_RETRY, TIMEOUT_RETRY_WAIT, TIMEOUT_ERROR_MATCHER) do
          apply_manifest(hkps_pool_pp, catch_failures: true)
        end

        apply_manifest(hkps_pool_pp, catch_changes: true)
        shell(PUPPETLABS_KEY_CHECK_COMMAND)
      end
    end

    context 'hkp://hkps.pool.sks-keyservers.net:80' do
      it 'works' do
        retry_on_error_matching(MAX_TIMEOUT_RETRY, TIMEOUT_RETRY_WAIT, TIMEOUT_ERROR_MATCHER) do
          apply_manifest(hkp_pool_pp, catch_failures: true)
        end

        apply_manifest(hkp_pool_pp, catch_changes: true)
        shell(PUPPETLABS_KEY_CHECK_COMMAND)
      end
    end

    context 'nonexistant.key.server' do
      it 'fails' do
        apply_manifest(nonexistant_key_server_pp, expect_failures: true) do |r|
          expect(r.stderr).to match(%r{(Host not found|Couldn't resolve host)})
        end
      end
    end

    context 'key server start with dot' do
      it 'fails' do
        apply_manifest(dot_server_pp, expect_failures: true) do |r|
          expect(r.stderr).to match(%r{Invalid value ".pgp.key.server"})
        end
      end
    end
  end

  describe 'source =>' do
    context 'http://' do
      it 'works' do
        apply_manifest_twice(http_works_pp)
        shell(PUPPETLABS_KEY_CHECK_COMMAND)
      end

      it 'works with userinfo' do
        apply_manifest_twice(http_works_userinfo_pp)
        shell(PUPPETLABS_KEY_CHECK_COMMAND)
      end

      it 'fails with a 404' do
        apply_manifest(four_oh_four_pp, expect_failures: true) do |r|
          expect(r.stderr).to match(%r{404 Not Found})
        end
      end

      it 'fails with a socket error' do
        apply_manifest(socket_error_pp, expect_failures: true) do |r|
          expect(r.stderr).to match(%r{could not resolve})
        end
      end
    end

    context 'ftp://' do
      before(:each) do
        shell("apt-key del #{CENTOS_GPG_KEY_LONG_ID}",
              acceptable_exit_codes: [0, 1, 2])
      end

      it 'works' do
        apply_manifest_twice(ftp_works_pp)
        shell(CENTOS_KEY_CHECK_COMMAND)
      end

      it 'fails with a 550' do
        apply_manifest(ftp_550_pp, expect_failures: true) do |r|
          expect(r.stderr).to match(%r{550 Failed to open})
        end
      end

      it 'fails with a socket error' do
        apply_manifest(ftp_socket_error_pp, expect_failures: true) do |r|
          expect(r.stderr).to match(%r{could not resolve})
        end
      end
    end

    context 'https://' do
      it 'works' do
        apply_manifest_twice(https_works_pp)
        shell(PUPPETLABS_KEY_CHECK_COMMAND)
      end

      it 'works with userinfo' do
        apply_manifest_twice(https_userinfo_pp)
        shell(PUPPETLABS_KEY_CHECK_COMMAND)
      end

      it 'fails with a 404' do
        apply_manifest(https_404_pp, expect_failures: true) do |r|
          expect(r.stderr).to match(%r{404 Not Found})
        end
      end

      it 'fails with a socket error' do
        apply_manifest(https_socket_error_pp, expect_failures: true) do |r|
          expect(r.stderr).to match(%r{could not resolve})
        end
      end
    end

    context '/path/that/exists' do
      before(:each) do
        shell("curl -o /tmp/puppetlabs-pubkey.gpg \
              http://#{PUPPETLABS_APT_URL}/#{PUPPETLABS_GPG_KEY_FILE}")
      end

      after(:each) do
        shell('rm /tmp/puppetlabs-pubkey.gpg')
      end

      it 'works' do
        apply_manifest_twice(path_exists_pp)
        shell(PUPPETLABS_KEY_CHECK_COMMAND)
      end
    end

    context '/path/that/does/not/exist' do
      it 'fails' do
        apply_manifest(path_does_not_exist_pp, expect_failures: true) do |r|
          expect(r.stderr).to match(%r{does not exist})
        end
      end
    end

    context '/path/that/exists/with/bogus/content' do
      before(:each) do
        shell('echo "here be dragons" > /tmp/fake-key.gpg')
      end

      after(:each) do
        shell('rm /tmp/fake-key.gpg')
      end
      it 'fails' do
        apply_manifest(path_bogus_content_pp, expect_failures: true) do |r|
          expect(r.stderr).to match(%r{no valid OpenPGP data found})
        end
      end
    end
  end

  describe 'options =>' do
    context 'debug' do
      it 'works' do
        apply_manifest_twice(debug_works_pp)
        shell(PUPPETLABS_KEY_CHECK_COMMAND)
      end
    end
  end

  describe 'fingerprint validation against source/content' do
    context 'fingerprint in id matches fingerprint from remote key' do
      it 'works' do
        apply_manifest_twice(fingerprint_match_pp)
      end
    end

    context 'fingerprint in id does NOT match fingerprint from remote key' do
      it 'works' do
        apply_manifest(fingerprint_does_not_match_pp, expect_failures: true) do |r|
          expect(r.stderr).to match(%r{don't match})
        end
      end
    end
  end
end
