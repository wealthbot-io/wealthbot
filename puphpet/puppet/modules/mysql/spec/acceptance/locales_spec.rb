require 'spec_helper_acceptance'
require 'beaker/i18n_helper'

describe 'mysql localization', if: (fact('osfamily') == 'Debian' || fact('osfamily') == 'RedHat') && puppet_version =~ %r{(^4\.10\.[56789]|5\.\d\.\d)} do
  before :all do
    hosts.each do |host|
      on(host, "sed -i \"96i FastGettext.locale='ja'\" /opt/puppetlabs/puppet/lib/ruby/vendor_ruby/puppet.rb")
      change_locale_on(host, 'ja_JP.utf-8')
    end
  end

  context 'when triggering puppet simple string error' do
    let(:pp) do
      <<-EOS
    class { 'mysql::server':
            config_file             => '/tmp/mysql.sFlJdV/my.cnf',
            includedir              => '/tmp/mysql.sFlJdV/include',
            manage_config_file      => 'true',
            override_options        => { 'mysqld' => { 'key_buffer_size' => '32M' }},
            package_ensure          => 'present',
            purge_conf_dir          => 'true',
            remove_default_accounts => 'true',
            restart                 => 'true',
            root_group              => 'root',
            root_password           => 'test',
            old_root_password       => 'kittensnmittens',
            service_enabled         => 'false'
          }
      EOS
    end

    it 'displays Japanese error' do
      apply_manifest(pp, catch_error: true) do |r|
        expect(r.stderr).to match(%r{`old_root_password`属性は廃止予定であり、今後のリリースで廃止されます。}i)
      end
    end
  end

  context 'when triggering puppet interpolated string failure' do
    let(:pp) do
      <<-EOS
    class { 'mysql::server': root_password => 'password' }
    class { 'mysql::server::backup':
              backupuser     => 'myuser',
              backuppassword => 'mypassword',
              backupdir      => '/tmp/backups',
              backupcompress => true,
              prescript      => true,
              provider       => 'mysqldump',
              execpath       => '/usr/bin:/usr/sbin:/bin:/sbin:/opt/zimbra/bin',
          }
      EOS
    end

    it 'displays Japanese failure' do
      apply_manifest(pp, catch_failures: true) do |r|
        expect(r.stderr).to match(%r{'prescript'オプションは、現在、mysqldumpバックアッププロバイダ向けには実装されていません。}i)
      end
    end
  end

  context 'when triggering ruby simple string failure' do
    let(:pp) do
      <<-EOS
      mysql::db { 'mydb':
        user     => 'thisisalongusernametestfortodayandtomorrowandthenextday',
        password => 'mypass',
        host     => 'localhost',
        grant    => ['SELECT', 'UPDATE'],
      }
    EOS
    end

    it 'displays Japanese failure' do
      apply_manifest(pp, expect_failures: true) do |r|
        expect(r.stderr).to match(%r{MySQLユーザ名は最大\d{2}文字に制限されています。}i)
      end
    end
  end

  context 'when triggering ruby interpolated string error' do
    let(:pp) do
      <<-EOS
      mysql_user{ '"name@localhost':
        ensure => 'present',
       }
      EOS
    end

    it 'displays Japanese error' do
      apply_manifest(pp, expect_failures: true) do |r|
        expect(r.stderr).to match(%r{無効なデータベースのユーザ"name@localhost}i)
      end
    end
  end

  after :all do
    hosts.each do |host|
      on(host, 'sed -i "96d" /opt/puppetlabs/puppet/lib/ruby/vendor_ruby/puppet.rb')
      change_locale_on(host, 'en_US')
    end
  end
end
