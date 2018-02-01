# Class: gcc::params
#
# This class manages parameters for the gcc module
#
# Parameters:
#
# Actions:
#
# Requires:
#
# Sample Usage:
#
class gcc::params {
  case $::osfamily {
    'RedHat': {
      $gcc_packages = [ 'gcc', 'gcc-c++' ]
    }
    'Debian': {
      $gcc_packages = [ 'gcc', 'build-essential' ]
    }
    default: {
      fail("Class['gcc::params']: Unsupported osfamily: ${::osfamily}")
    }
  }
}
