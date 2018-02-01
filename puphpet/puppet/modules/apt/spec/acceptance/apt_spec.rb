require 'spec_helper_acceptance'

MAX_TIMEOUT_RETRY              = 3
TIMEOUT_RETRY_WAIT             = 5
TIMEOUT_ERROR_MATCHER = %r{no valid OpenPGP data found}

everything_everything_pp = <<-EOS
      if $::lsbdistcodename == 'lucid' {
        $sources = undef
      } else {
        $sources = {
          'puppetlabs' => {
            'ensure'   => present,
            'location' => 'http://apt.puppetlabs.com',
            'repos'    => 'main',
            'key'      => {
              'id'     => '6F6B15509CF8E59E6E469F327F438280EF8D349F',
              'server' => 'hkps.pool.sks-keyservers.net',
            },
          },
        }
      }
      class { 'apt':
        update => {
          'frequency' => 'always',
          'timeout'   => 400,
          'tries'     => 3,
        },
        purge => {
          'sources.list'   => true,
          'sources.list.d' => true,
          'preferences'    => true,
          'preferences.d'  => true,
        },
        sources => $sources,
      }
  EOS

describe 'apt class' do
  context 'reset' do
    it 'fixes the sources.list' do
      shell('cp /etc/apt/sources.list /tmp')
    end
  end

  context 'all the things' do
    it 'works with no errors' do
      # Apply the manifest (Retry if timeout error is received from key pool)
      retry_on_error_matching(MAX_TIMEOUT_RETRY, TIMEOUT_RETRY_WAIT, TIMEOUT_ERROR_MATCHER) do
        apply_manifest(everything_everything_pp, catch_failures: true)
      end

      apply_manifest(everything_everything_pp, catch_failures: true)
    end
    it 'stills work' do
      shell('apt-get update')
      shell('apt-get -y --force-yes upgrade')
    end
  end

  context 'reset' do
    it 'fixes the sources.list' do
      shell('cp /tmp/sources.list /etc/apt')
    end
  end
end
