require 'json'

test_settings['cluster_name'] = SecureRandom.hex(10)

test_settings['repo_version2x']          = '2.x'
test_settings['repo_version5x']          = '5.x'
# Default repo
test_settings['repo_version']            = test_settings['repo_version2x']
test_settings['install_package_version'] = '2.4.2'
test_settings['upgrade_package_version'] = '2.4.3'
test_settings['package_name']            = 'elasticsearch'

test_settings['security_user']             = 'elasticuser'
test_settings['security_password']         = SecureRandom.hex
test_settings['security_hashed_password']  = \
  '$2a$10$DddrTs0PS3qNknUTq0vpa.g.0JpU.jHDdlKp1xox1W5ZHX.w8Cc8C'
test_settings['security_hashed_plaintext'] = 'foobar'

test_settings['index'] = [*('a'..'z')].sample(8).join

case fact('osfamily')
when 'RedHat'
  test_settings['url']             = 'http://download.elasticsearch.org/elasticsearch/elasticsearch/elasticsearch-2.3.5.rpm'
  test_settings['local']           = '/tmp/elasticsearch-2.3.5.rpm'
  test_settings['puppet']          = 'elasticsearch-2.3.5.rpm'
  test_settings['service_name_a']  = 'elasticsearch-es-01'
  test_settings['service_name_b']  = 'elasticsearch-es-02'
  test_settings['defaults_file_a'] = '/etc/sysconfig/elasticsearch-es-01'
  test_settings['defaults_file_b'] = '/etc/sysconfig/elasticsearch-es-02'
  test_settings['port_a']          = '9200'
  test_settings['port_b']          = '9201'

  test_settings['pid_a'] = '/var/run/elasticsearch/elasticsearch-es-01.pid'
  test_settings['pid_b'] = '/var/run/elasticsearch/elasticsearch-es-02.pid'
when 'Debian'
  test_settings['url'] = 'http://download.elasticsearch.org/elasticsearch/elasticsearch/elasticsearch-2.3.5.deb'
  test_settings['local'] = '/tmp/elasticsearch-2.3.5.deb'
  test_settings['puppet'] = 'elasticsearch-2.3.5.deb'
  case fact('operatingsystem')
  when 'Ubuntu'
    # From 15.04 onwards, ubuntu moved to systemd.
    if Gem::Version.new(fact('operatingsystemrelease')) \
        >= Gem::Version.new('15.04')
      test_settings['pid_a'] = '/var/run/elasticsearch/elasticsearch-es-01.pid'
      test_settings['pid_b'] = '/var/run/elasticsearch/elasticsearch-es-02.pid'
    else
      test_settings['pid_a'] = '/var/run/elasticsearch-es-01.pid'
      test_settings['pid_b'] = '/var/run/elasticsearch-es-02.pid'
    end
  when 'Debian'
    case fact('lsbmajdistrelease')
    when '7'
      test_settings['pid_a'] = '/var/run/elasticsearch-es-01.pid'
      test_settings['pid_b'] = '/var/run/elasticsearch-es-02.pid'
    else
      test_settings['pid_a'] = '/var/run/elasticsearch/elasticsearch-es-01.pid'
      test_settings['pid_b'] = '/var/run/elasticsearch/elasticsearch-es-02.pid'
    end
  end
  test_settings['service_name_a']  = 'elasticsearch-es-01'
  test_settings['service_name_b']  = 'elasticsearch-es-02'
  test_settings['defaults_file_a'] = '/etc/default/elasticsearch-es-01'
  test_settings['defaults_file_b'] = '/etc/default/elasticsearch-es-02'
  test_settings['port_a']          = '9200'
  test_settings['port_b']          = '9201'
when 'Suse'
  test_settings['url']             = 'http://download.elasticsearch.org/elasticsearch/elasticsearch/elasticsearch-2.3.5.rpm'
  test_settings['local']           = '/tmp/elasticsearch-2.3.5.rpm'
  test_settings['puppet']          = 'elasticsearch-2.3.5.rpm'
  test_settings['service_name_a']  = 'elasticsearch-es-01'
  test_settings['service_name_b']  = 'elasticsearch-es-02'
  test_settings['defaults_file_a'] = '/etc/sysconfig/elasticsearch-es-01'
  test_settings['defaults_file_b'] = '/etc/sysconfig/elasticsearch-es-02'
  test_settings['port_a']          = '9200'
  test_settings['port_b']          = '9201'
  test_settings['pid_a'] = '/var/run/elasticsearch/elasticsearch-es-01.pid'
  test_settings['pid_b'] = '/var/run/elasticsearch/elasticsearch-es-02.pid'
end

test_settings['datadir_1'] = '/var/lib/elasticsearch-data/1/'
test_settings['datadir_2'] = '/var/lib/elasticsearch-data/2/'
test_settings['datadir_3'] = '/var/lib/elasticsearch-data/3/'

test_settings['template'] = JSON.load(File.new('spec/fixtures/templates/pre_6.0.json'))
test_settings['template_snapshot'] = JSON.load(File.new('spec/fixtures/templates/post_6.0.json'))

RSpec.configuration.test_settings = test_settings
