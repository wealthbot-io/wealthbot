node { // The "node" directive tells Jenkins to run commands on the same slave.
    checkout scm

    stage 'Bundle install'

    wrap([$class: 'AnsiColorBuildWrapper', 'colorMapName': 'gnome-terminal']) {
      sh 'bundle install'
    }

    stage 'Acceptance Testing'

    env.PUPPET_INSTALL_VERSION = "1.5.2"

    env.PUPPET_INSTALL_TYPE = "agent"

    env.BEAKER_set = "centos-7-x64-vagrant_libvirt"

    print "Beaker Settings will be: ${env.PUPPET_INSTALL_VERSION} ${env.PUPPET_INSTALL_TYPE} ${env.BEAKER_set}"

    wrap([$class: 'AnsiColorBuildWrapper', 'colorMapName': 'gnome-terminal']) {
      sh 'bundle exec rake acceptance'
    }

}
