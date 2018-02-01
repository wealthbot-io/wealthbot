shared_examples 'instance' do |name, init|
  it { should contain_elasticsearch__instance(name) }
  it { should contain_augeas("defaults_#{name}") }
  it { should contain_datacat("/etc/elasticsearch/#{name}/elasticsearch.yml") }
  it { should contain_datacat_fragment("main_config_#{name}") }
  it { should contain_elasticsearch__instance(name) }
  it { should contain_elasticsearch__service(name) }
  it { should contain_exec("mkdir_configdir_elasticsearch_#{name}") }
  it { should contain_exec("mkdir_datadir_elasticsearch_#{name}")
    .with(:command => "mkdir -p /var/lib/elasticsearch/#{name}") }
  it { should contain_exec("mkdir_logdir_elasticsearch_#{name}")
    .with(:command => "mkdir -p /var/log/elasticsearch/#{name}") }
  it { should contain_elasticsearch__service(name) }
  it { should contain_service("elasticsearch-instance-#{name}") }

  %w[/var/log/elasticsearch /var/lib/elasticsearch /etc/elasticsearch].each do |dir|
    it { should contain_file("#{dir}/#{name}").with(:ensure => 'directory') }
  end

  %w[elasticsearch.yml jvm.options logging.yml log4j2.properties scripts].each do |file|
    it { should contain_file("/etc/elasticsearch/#{name}/#{file}") }
  end

  case init
  when :sysv
    it { should contain_elasticsearch__service__init(name) }
    it { should contain_elasticsearch_service_file("/etc/init.d/elasticsearch-#{name}") }
    it { should contain_file("/etc/init.d/elasticsearch-#{name}") }
  when :systemd
    it { should contain_elasticsearch__service__systemd(name) }
    it { should contain_elasticsearch_service_file("/lib/systemd/system/elasticsearch-#{name}.service") }
    it { should contain_file("/lib/systemd/system/elasticsearch-#{name}.service") }
    it { should contain_exec("systemd_reload_#{name}") }
  end
end
