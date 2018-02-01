# PRIVATE CLASS: do not use directly
class mongodb::repo (
  $ensure         = $mongodb::params::ensure,
  $version        = $mongodb::params::version,
  $repo_location  = undef,
  $proxy          = undef,
  $proxy_username = undef,
  $proxy_password = undef,
) inherits mongodb::params {
  case $::osfamily {
    'RedHat', 'Linux': {
      if $version != undef {
        $mongover = split($version, '[.]')
      }
      if ($repo_location != undef){
        $location = $repo_location
        $description = 'MongoDB Custom Repository'
      } elsif $mongodb::globals::use_enterprise_repo == true {
        $location = "https://repo.mongodb.com/yum/redhat/\$releasever/mongodb-enterprise/${mongover[0]}.${mongover[1]}/\$basearch/"
        $description = 'MongoDB Enterprise Repository'
      }
      elsif $version and (versioncmp($version, '3.0.0') >= 0) {
        $location = $::architecture ? {
          'x86_64' => "http://repo.mongodb.org/yum/redhat/${::operatingsystemmajrelease}/mongodb-org/${mongover[0]}.${mongover[1]}/x86_64/",
          default  => undef
        }
        $description = 'MongoDB Repository'
      }
      else {
        $location = $::architecture ? {
          'x86_64' => 'http://downloads-distro.mongodb.org/repo/redhat/os/x86_64/',
          'i686'   => 'http://downloads-distro.mongodb.org/repo/redhat/os/i686/',
          'i386'   => 'http://downloads-distro.mongodb.org/repo/redhat/os/i686/',
          default  => undef
        }
        $description = 'MongoDB/10gen Repository'
      }

      class { 'mongodb::repo::yum': }
    }

    'Debian': {
      if ($repo_location != undef){
        $location = $repo_location
      }
      elsif $version and (versioncmp($version, '3.0.0') >= 0) {
        if $mongodb::globals::use_enterprise_repo == true {
            $repo_domain = 'repo.mongodb.com'
            $repo_path   = 'mongodb-enterprise'
        } else {
            $repo_domain = 'repo.mongodb.org'
            $repo_path   = 'mongodb-org'
        }

        $mongover = split($version, '[.]')
        $location = $::operatingsystem ? {
          'Debian' => "https://${repo_domain}/apt/debian",
          'Ubuntu' => "https://${repo_domain}/apt/ubuntu",
          default  => undef
        }
        $release     = "${::lsbdistcodename}/${repo_path}/${mongover[0]}.${mongover[1]}"
        $repos       = $::operatingsystem ? {
          'Debian' => 'main',
          'Ubuntu' => 'multiverse',
          default => undef
        }
        $key = "${mongover[0]}.${mongover[1]}" ? {
          '3.4'   => '0C49F3730359A14518585931BC711F9BA15703C6',
          '3.2'   => '42F3E95A2C4F08279C4960ADD68FA50FEA312927',
          default => '492EAFE8CD016A07919F1D2B9ECBEC467F0CEB10'
        }
        $key_server = 'hkp://keyserver.ubuntu.com:80'
      } else {
        $location = $::operatingsystem ? {
          'Debian' => 'http://downloads-distro.mongodb.org/repo/debian-sysvinit',
          'Ubuntu' => 'http://downloads-distro.mongodb.org/repo/ubuntu-upstart',
          default  => undef
        }
        $release     = 'dist'
        $repos       = '10gen'
        $key         = '492EAFE8CD016A07919F1D2B9ECBEC467F0CEB10'
        $key_server  = 'hkp://keyserver.ubuntu.com:80'
      }
      class { 'mongodb::repo::apt': }
    }

    default: {
      if($ensure == 'present' or $ensure == true) {
        fail("Unsupported managed repository for osfamily: ${::osfamily}, operatingsystem: ${::operatingsystem}, module ${module_name} currently only supports managing repos for osfamily RedHat, Debian and Ubuntu")
      }
    }
  }
}
