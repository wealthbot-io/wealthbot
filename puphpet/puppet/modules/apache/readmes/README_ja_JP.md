# apache

[モジュールの概要]: #module-description

[セットアップ]: #setup
[Apacheの使用を始める]: #beginning-with-apache

[使用方法]: #usage
[バーチャルホストの設定]: #configuring-virtual-hosts
[SSLを使ったバーチャルホストの設定]: #configuring-virtual-hosts-with-ssl
[バーチャルホストのポートおよびアドレスのバインディング設定]: #configuring-virtual-host-port-and-address-bindings
[アプリおよびプロセッサのバーチャルホストの設定]: #configuring-virtual-hosts-for-apps-and-processors
[IPベースのバーチャルホストの設定]: #configuring-ip-based-virtual-hosts
[Apacheモジュールのインストール]: #installing-apache-modules
[任意モジュールのインストール]: #installing-arbitrary-modules
[固有モジュールのインストール]: #installing-specific-modules
[FastCGIサーバの設定]: #configuring-fastcgi-servers-to-handle-php-files
[ロードバランシングの例]: #load-balancing-examples
[apacheの影響]: #what-the-apache-module-affects

[リファレンス]: #reference
[パブリッククラス]: #public-classes
[プライベートクラス]: #private-classes
[パブリック定義タイプ]: #public-defined-types
[プライベート定義タイプ]: #private-defined-types
[テンプレート]: #templates

[制約事項]: #limitations

[開発]: #development
[貢献]: #contributing

[`AddDefaultCharset`]: https://httpd.apache.org/docs/current/mod/core.html#adddefaultcharset
[`add_listen`]: #add_listen
[`Alias`]: https://httpd.apache.org/docs/current/mod/mod_alias.html#alias
[`AliasMatch`]: https://httpd.apache.org/docs/current/mod/mod_alias.html#aliasmatch
[エイリアスサーバ]: https://httpd.apache.org/docs/current/urlmapping.html
[`AllowEncodedSlashes`]: https://httpd.apache.org/docs/current/mod/core.html#allowencodedslashes
[`apache`]: #class-apache
[`apache_version`]: #apache_version
[`apache::balancer`]: #defined-type-apachebalancer
[`apache::balancermember`]: #defined-type-apachebalancermember
[`apache::fastcgi::server`]: #defined-type-apachefastcgiserver
[`apache::mod`]: #defined-type-apachemod
[`apache::mod::<MODULE NAME>`]: #classes-apachemodmodule-name
[`apache::mod::alias`]: #class-apachemodalias
[`apache::mod::auth_cas`]: #class-apachemodauth_cas
[`apache::mod::auth_mellon`]: #class-apachemodauth_mellon
[`apache::mod::authn_dbd`]: #class-apachemodauthn_dbd
[`apache::mod::authnz_ldap`]: #class-apachemodauthnz_ldap
[`apache::mod::cluster`]: #class-apachemodcluster
[`apache::mod::disk_cache`]: #class-apachemoddisk_cache
[`apache::mod::dumpio`]: #class-apachemoddumpio
[`apache::mod::event`]: #class-apachemodevent
[`apache::mod::ext_filter`]: #class-apachemodext_filter
[`apache::mod::geoip`]: #class-apachemodgeoip
[`apache::mod::itk`]: #class-apachemoditk
[`apache::mod::jk`]: #class-apachemodjk
[`apache::mod::ldap`]: #class-apachemodldap
[`apache::mod::passenger`]: #class-apachemodpassenger
[`apache::mod::peruser`]: #class-apachemodperuser
[`apache::mod::prefork`]: #class-apachemodprefork
[`apache::mod::proxy`]: #class-apachemodproxy
[`apache::mod::proxy_balancer`]: #class-apachemodproxybalancer
[`apache::mod::proxy_fcgi`]: #class-apachemodproxy_fcgi
[`apache::mod::proxy_html`]: #class-apachemodproxy_html
[`apache::mod::security`]: #class-apachemodsecurity
[`apache::mod::shib`]: #class-apachemodshib
[`apache::mod::ssl`]: #class-apachemodssl
[`apache::mod::status`]: #class-apachemodstatus
[`apache::mod::userdir`]: #class-apachemoduserdir
[`apache::mod::worker`]: #class-apachemodworker
[`apache::mod::wsgi`]: #class-apachemodwsgi
[`apache::params`]: #class-apacheparams
[`apache::version`]: #class-apacheversion
[`apache::vhost`]: #defined-type-apachevhost
[`apache::vhost::custom`]: #defined-type-apachevhostcustom
[`apache::vhost::WSGIImportScript`]: #wsgiimportscript
[Apache HTTPサーバ]: https://httpd.apache.org
[Apacheモジュール]: https://httpd.apache.org/docs/current/mod/
[配列]: https://docs.puppet.com/puppet/latest/reference/lang_data_array.html

[オーディットログ]: https://github.com/SpiderLabs/ModSecurity/wiki/ModSecurity-2-Data-Formats#audit-log

[beaker-rspec]: https://github.com/puppetlabs/beaker-rspec

[証明書失効リスト]: https://httpd.apache.org/docs/current/mod/mod_ssl.html#sslcarevocationfile
[証明書失効リストパス]: https://httpd.apache.org/docs/current/mod/mod_ssl.html#sslcarevocationpath
[コモンゲートウェイインターフェース]: https://httpd.apache.org/docs/current/howto/cgi.html
[`confd_dir`]: #confd_dir
[`content`]: #content
[CONTRIBUTING.md]: CONTRIBUTING.md
[カスタムエラードキュメント]: https://httpd.apache.org/docs/current/custom-error.html
[`custom_fragment`]: #custom_fragment

[`default_mods`]: #default_mods
[`default_ssl_crl`]: #default_ssl_crl
[`default_ssl_crl_path`]: #default_ssl_crl_path
[`default_ssl_vhost`]: #default_ssl_vhost
[`dev_packages`]: #dev_packages
[`directory`]: #directory
[`directories`]: #parameter-directories-for-apachevhost
[`DirectoryIndex`]: https://httpd.apache.org/docs/current/mod/mod_dir.html#directoryindex
[`docroot`]: #docroot
[`docroot_owner`]: #docroot_owner
[`docroot_group`]: #docroot_group
[`DocumentRoot`]: https://httpd.apache.org/docs/current/mod/core.html#documentroot

[`EnableSendfile`]: https://httpd.apache.org/docs/current/mod/core.html#enablesendfile
[適用モード]: http://selinuxproject.org/page/Guide/Mode
[`ensure`]: https://docs.puppet.com/latest/type.html#package-attribute-ensure
[`error_log_file`]: #error_log_file
[`error_log_syslog`]: #error_log_syslog
[`error_log_pipe`]: #error_log_pipe
[`ExpiresByType`]: https://httpd.apache.org/docs/current/mod/mod_expires.html#expiresbytype
[エクスポートリソース]: http://docs.puppet.com/latest/reference/lang_exported.md
[`ExtendedStatus`]: https://httpd.apache.org/docs/current/mod/core.html#extendedstatus

[Facter]: http://docs.puppet.com/facter/
[FastCGI]: http://www.fastcgi.com/
[FallbackResource]: https://httpd.apache.org/docs/current/mod/mod_dir.html#fallbackresource
[`fallbackresource`]: #fallbackresource
[`FileETag`]: https://httpd.apache.org/docs/current/mod/core.html#fileetag
[フィルタルール]: https://httpd.apache.org/docs/current/filter.html
[`filters`]: #filters
[`ForceType`]: https://httpd.apache.org/docs/current/mod/core.html#forcetype

[GeoIPScanProxyHeaders]: http://dev.maxmind.com/geoip/legacy/mod_geoip2/#Proxy-Related_Directives
[`gentoo/puppet-portage`]: https://github.com/gentoo/puppet-portage

[ハッシュ]: https://docs.puppet.com/puppet/latest/reference/lang_data_hash.html
[`HttpProtocolOptions`]: http://httpd.apache.org/docs/current/mod/core.html#httpprotocoloptions

[`IncludeOptional`]: https://httpd.apache.org/docs/current/mod/core.html#includeoptional
[`Include`]: https://httpd.apache.org/docs/current/mod/core.html#include
[インターバル構文]: https://httpd.apache.org/docs/current/mod/mod_expires.html#AltSyn
[`ip`]: #ip
[`ip_based`]: #ip_based
[IPベースのバーチャルホスト]: https://httpd.apache.org/docs/current/vhosts/ip-based.html

[`KeepAlive`]: https://httpd.apache.org/docs/current/mod/core.html#keepalive
[`KeepAliveTimeout`]: https://httpd.apache.org/docs/current/mod/core.html#keepalivetimeout
[`keepalive`パラメータ]: #keepalive
[`keepalive_timeout`]: #keepalive_timeout
[`limitreqfieldsize`]: https://httpd.apache.org/docs/current/mod/core.html#limitrequestfieldsize

[`lib`]: #lib
[`lib_path`]: #lib_path
[`Listen`]: https://httpd.apache.org/docs/current/bind.html
[`ListenBackLog`]: https://httpd.apache.org/docs/current/mod/mpm_common.html#listenbacklog
[`LoadFile`]: https://httpd.apache.org/docs/current/mod/mod_so.html#loadfile
[`LogFormat`]: https://httpd.apache.org/docs/current/mod/mod_log_config.html#logformat
[`logroot`]: #logroot
[ログセキュリティ]: https://httpd.apache.org/docs/current/logs.html#security

[`manage_docroot`]: #manage_docroot
[`manage_user`]: #manage_user
[`manage_group`]: #manage_group
[`supplementary_groups`]: #supplementary_groups
[`MaxConnectionsPerChild`]: https://httpd.apache.org/docs/current/mod/mpm_common.html#maxconnectionsperchild
[`max_keepalive_requests`]: #max_keepalive_requests
[`MaxRequestWorkers`]: https://httpd.apache.org/docs/current/mod/mpm_common.html#maxrequestworkers
[`MaxSpareThreads`]: https://httpd.apache.org/docs/current/mod/mpm_common.html#maxsparethreads
[MIME `content-type`]: https://www.iana.org/assignments/media-types/media-types.xhtml
[`MinSpareThreads`]: https://httpd.apache.org/docs/current/mod/mpm_common.html#minsparethreads
[`mod_alias`]: https://httpd.apache.org/docs/current/mod/mod_alias.html
[`mod_auth_cas`]: https://github.com/Jasig/mod_auth_cas
[`mod_auth_kerb`]: http://modauthkerb.sourceforge.net/configure.html
[`mod_authnz_external`]: https://github.com/phokz/mod-auth-external
[`mod_auth_dbd`]: http://httpd.apache.org/docs/current/mod/mod_authn_dbd.html
[`mod_auth_mellon`]: https://github.com/UNINETT/mod_auth_mellon
[`mod_dbd`]: http://httpd.apache.org/docs/current/mod/mod_dbd.html
[`mod_disk_cache`]: https://httpd.apache.org/docs/2.2/mod/mod_disk_cache.html
[`mod_dumpio`]: https://httpd.apache.org/docs/2.4/mod/mod_dumpio.html
[`mod_env`]: http://httpd.apache.org/docs/current/mod/mod_env.html
[`mod_expires`]: https://httpd.apache.org/docs/current/mod/mod_expires.html
[`mod_ext_filter`]: https://httpd.apache.org/docs/current/mod/mod_ext_filter.html
[`mod_fcgid`]: https://httpd.apache.org/mod_fcgid/mod/mod_fcgid.html
[`mod_geoip`]: http://dev.maxmind.com/geoip/legacy/mod_geoip2/
[`mod_info`]: https://httpd.apache.org/docs/current/mod/mod_info.html
[`mod_ldap`]: https://httpd.apache.org/docs/2.2/mod/mod_ldap.html
[`mod_mpm_event`]: https://httpd.apache.org/docs/current/mod/event.html
[`mod_negotiation`]: https://httpd.apache.org/docs/current/mod/mod_negotiation.html
[`mod_pagespeed`]: https://developers.google.com/speed/pagespeed/module/?hl=en
[`mod_passenger`]: https://www.phusionpassenger.com/library/config/apache/reference/
[`mod_php`]: http://php.net/manual/en/book.apache.php
[`mod_proxy`]: https://httpd.apache.org/docs/current/mod/mod_proxy.html
[`mod_proxy_balancer`]: https://httpd.apache.org/docs/current/mod/mod_proxy_balancer.html
[`mod_reqtimeout`]: https://httpd.apache.org/docs/current/mod/mod_reqtimeout.html
[`mod_rewrite`]: https://httpd.apache.org/docs/current/mod/mod_rewrite.html
[`mod_security`]: https://www.modsecurity.org/
[`mod_ssl`]: https://httpd.apache.org/docs/current/mod/mod_ssl.html
[`mod_status`]: https://httpd.apache.org/docs/current/mod/mod_status.html
[`mod_version`]: https://httpd.apache.org/docs/current/mod/mod_version.html
[`mod_wsgi`]: https://modwsgi.readthedocs.org/en/latest/
[モジュール貢献ガイド]: https://docs.puppet.com/forge/contributing.html
[`mpm_module`]: #mpm_module
[マルチプロセッシングモジュール]: https://httpd.apache.org/docs/current/mpm.html

[名前ベースのバーチャルホスト]: https://httpd.apache.org/docs/current/vhosts/name-based.html
[`no_proxy_uris`]: #no_proxy_uris

[オープンソース版Puppet]: https://docs.puppet.com/puppet/
[`Options`]: https://httpd.apache.org/docs/current/mod/core.html#options

[`path`]: #path
[`Peruser`]: https://www.freebsd.org/cgi/url.cgi?ports/www/apache22-peruser-mpm/pkg-descr
[`port`]: #port
[`priority`]: #defined-types-apachevhost
[`proxy_dest`]: #proxy_dest
[`proxy_dest_match`]: #proxy_dest_match
[`proxy_pass`]: #proxy_pass
[`ProxyPass`]: https://httpd.apache.org/docs/current/mod/mod_proxy.html#proxypass
[`ProxySet`]: https://httpd.apache.org/docs/current/mod/mod_proxy.html#proxyset
[Puppet Enterprise]: https://docs.puppet.com/pe/
[Puppet Forge]: https://forge.puppet.com
[Puppet]: https://puppet.com
[Puppetモジュール]: https://docs.puppet.com/puppet/latest/reference/modules_fundamentals.html
[Puppetモジュールのコード]: https://github.com/puppetlabs/puppetlabs-apache/blob/master/manifests/default_mods.pp
[`purge_configs`]: #purge_configs
[`purge_vhost_dir`]: #purge_vhost_dir
[Python]: https://www.python.org/

[Rack]: http://rack.github.io/
[`rack_base_uris`]: #rack_base_uris
[RFC 2616]: https://www.ietf.org/rfc/rfc2616.txt
[`RequestReadTimeout`]: https://httpd.apache.org/docs/current/mod/mod_reqtimeout.html#requestreadtimeout
[rspec-puppet]: http://rspec-puppet.com/

[`ScriptAlias`]: https://httpd.apache.org/docs/current/mod/mod_alias.html#scriptalias
[`ScriptAliasMatch`]: https://httpd.apache.org/docs/current/mod/mod_alias.html#scriptaliasmatch
[`scriptalias`]: #scriptalias
[SELinux]: http://selinuxproject.org/
[`ServerAdmin`]: https://httpd.apache.org/docs/current/mod/core.html#serveradmin
[`serveraliases`]: #serveraliases
[`ServerLimit`]: https://httpd.apache.org/docs/current/mod/mpm_common.html#serverlimit
[`ServerName`]: https://httpd.apache.org/docs/current/mod/core.html#servername
[`ServerRoot`]: https://httpd.apache.org/docs/current/mod/core.html#serverroot
[`ServerTokens`]: https://httpd.apache.org/docs/current/mod/core.html#servertokens
[`ServerSignature`]: https://httpd.apache.org/docs/current/mod/core.html#serversignature
[サービス属性リスタート]: http://docs.puppet.com/latest/type.html#service-attribute-restart
[`source`]: #source
[`SSLCARevocationCheck`]: https://httpd.apache.org/docs/current/mod/mod_ssl.html#sslcarevocationcheck
[SSL証明書のキーファイル]: https://httpd.apache.org/docs/current/mod/mod_ssl.html#sslcertificatekeyfile
[SSLチェーン]: https://httpd.apache.org/docs/current/mod/mod_ssl.html#sslcertificatechainfile
[SSL暗号化]: https://httpd.apache.org/docs/current/ssl/index.html
[`ssl`]: #ssl
[`ssl_cert`]: #ssl_cert
[`ssl_compression`]: #ssl_compression
[`ssl_key`]: #ssl_key
[`StartServers`]: https://httpd.apache.org/docs/current/mod/mpm_common.html#startservers
[suPHP]: http://www.suphp.org/Home.html
[`suphp_addhandler`]: #suphp_addhandler
[`suphp_configpath`]: #suphp_configpath
[`suphp_engine`]: #suphp_engine
[対応するオペレーティングシステム]: https://forge.puppet.com/supported#puppet-supported-modules-compatibility-matrix

[`ThreadLimit`]: https://httpd.apache.org/docs/current/mod/mpm_common.html#threadlimit
[`ThreadsPerChild`]: https://httpd.apache.org/docs/current/mod/mpm_common.html#threadsperchild
[`TimeOut`]: https://httpd.apache.org/docs/current/mod/core.html#timeout
[テンプレート]: http://docs.puppet.com/puppet/latest/reference/lang_template.html
[`TraceEnable`]: https://httpd.apache.org/docs/current/mod/core.html#traceenable

[`verify_config`]: #verify_config
[`vhost`]: #defined-type-apachevhost
[`vhost_dir`]: #vhost_dir
[`virtual_docroot`]: #virtual_docroot

[Webサーバゲートウェイインターフェース ]: https://www.python.org/dev/peps/pep-3333/#abstract
[`WSGIRestrictEmbedded`]: http://modwsgi.readthedocs.io/en/develop/configuration-directives/WSGIRestrictEmbedded.html
[`WSGIPythonPath`]: http://modwsgi.readthedocs.org/en/develop/configuration-directives/WSGIPythonPath.html
[`WSGIPythonHome`]: http://modwsgi.readthedocs.org/en/develop/configuration-directives/WSGIPythonHome.html

#### 目次

1. [モジュールの概要 - apacheモジュールとは？　何をするためのもの？][モジュールの概要]
2. [セットアップ - apacheの使用を開始するにあたっての基礎][セットアップ]
    - [apacheモジュールが影響を与えるもの][apacheの影響]
    - [Apacheの使用を始める - インストール][Apacheの使用を始める]
3. [使用方法 - 設定に使用できるクラスと定義タイプ][使用方法]
    - [バーチャルホストの設定 - 使用開始に役立つ例][バーチャルホストの設定]
    - [PHPファイルを処理するFastCGIサーバの設定][FastCGIサーバの設定]
    - [エクスポートおよび非エクスポートリソースのロードバランシング][ロードバランシングの例]
4. [リファレンス - モジュールの機能と動作について][リファレンス]
    - [パブリッククラス][]
    - [プライベートクラス][]
    - [パブリック定義タイプ][]
    - [プライベート定義タイプ][]
    - [テンプレート][]
5. [制約事項 - OSの互換性など][制約事項]
6. [開発 - モジュールへの貢献方法][開発]
    - [apacheモジュールへの貢献][貢献]
    - [テストの実施 - クイックガイド][テストの実施]

## モジュールの概要

[Apache HTTPサーバ][] (Apache HTTPD、あるいは単にApacheとも呼ばれます)は、広く使用されているWebサーバです。この[Puppetモジュール][]によって、インフラ内でApacheを管理するための設定がシンプルなものになります。幅広いバーチャルホストセットアップを設定および管理し、[Apacheモジュール][]を効率的にインストールして設定することができます。

## セットアップ

### apacheモジュールが影響を与えるもの:

- (作成し、書き込みを行う)設定ファイルおよびディレクトリ
  - **警告**: Puppetにより管理*されていない*設定はパージされます。
- Apacheのパッケージ/サービス/設定ファイル
- Apacheモジュール
- バーチャルホスト
- リッスンするポート
- FreeBSDおよびGentooの`/etc/make.conf` 

Gentooでは、このモジュールは [`gentoo/puppet-portage`][] Puppetモジュールに依存します。Gentooについては、いくつかのオプションが適用され、一部の機能や設定が有効になりますが、このモジュールに[対応するオペレーティングシステム][]ではない点に留意してください。

> **警告**: このモジュールにより、Apache設定ファイルおよびディレクトリが修正され、Puppetで管理されていない設定がパージされます。Apache設定はPuppetで管理する必要があります。これは、管理されていない設定ファイルにより、予期せぬ不具合が生じる可能性があるためです。
>
>全面的なPuppet管理を一時的に無効にするには、[`apache`][]クラス宣言の[`purge_configs`][]パラメータをfalseに設定します。この手順は、カスタマイズした設定を保存し、リロケーションするための一時的な対策としてのみ推奨されます。

### Apacheの使用を始める

デフォルトパラメータを用いてPuppetでApacheをインストールするには、[`apache`][]クラスを宣言します。

``` puppet
class { 'apache': }
```

デフォルトオプションを用いてこのクラスを宣言すると、モジュールでは以下のことが実行されます。

- オペレーティングシステムに適したApacheソフトウェアパッケージおよび[必要なApacheモジュール](#default_mods)をインストールします。
- オペレーティングシステムに応じた[デフォルトロケーション](#conf_dir)を用いて、ディレクトリ内に必要な設定ファイルを配置します。
- デフォルトのバーチャルホストおよび標準的なポート('80')とアドレス('\*')のバインディングを用いてサーバを設定します。
- ドキュメントルートディレクトリを作成します。オペレーティングシステムによって異なりますが、通常は`/var/www`です。
- Apacheサービスを開始します。

Apacheのデフォルト設定は、オペレーティングシステムによって異なります。これらのデフォルトは、テスト環境では機能しますが、本稼働環境には推奨されません。実際のサイトに応じてクラスのパラメータをカスタマイズすることを推奨します。

例えば、以下の宣言では、apacheモジュールの[デフォルトのバーチャルホスト設定][バーチャルホストの設定]を使わずにApacheがインストールされるので、すべてのApacheバーチャルホストをカスタマイズすることができます。

``` puppet
class { 'apache':
  default_vhost => false,
}
```

> **注意**: `default_vhost`を`false`に設定する場合、少なくとも1つの`apache::vhost`リソースを追加する必要があります。追加しなければ、Apacheは起動しません。デフォルトのバーチャルホストを設定するには、`apache`クラスで`default_vhost`を設定するか、[`apache::vhost`][]定義タイプを使用します。[`apache::vhost`][]定義タイプを用いて、追加の固有バーチャルホストを設定することもできます。

## 使用方法

### バーチャルホストの設定

デフォルトの[`apache`][]クラスは、ポート80にバーチャルホストを設定します。すべてのインターフェースをリッスンし、[`docroot`][]パラメータのデフォルトディレクトリ`/var/www`をサーブします。


基本の[名前ベースのバーチャルホスト][]を設定するには、[`apache::vhost`][]定義タイプで[`port`][]および[`docroot`][]パラメータを指定します。

``` puppet
apache::vhost { 'vhost.example.com':
  port    => '80',
  docroot => '/var/www/vhost',
}
```

すべてのバーチャルホストパラメータのリストについては、[`apache::vhost`][]定義タイプのリファレンスを参照してください。

> **注意**: Apacheはバーチャルホストをアルファベット順に処理します。サーバ管理者は、バーチャルホスト設定ファイル名の先頭に数字を付けることで、 Apacheバーチャルホスト処理の優先順位を設定できます。[`apache::vhost`][]定義タイプは、デフォルトの [`priority`][]である15を適用します。これはPuppetではバーチャルホストのファイル名の先頭に`15-`が付いていると解釈されます。そのため、優先順位が同じサイトが複数ある場合や、`priority`パラメータの値をfalseに設定して優先順位番号を無効にした場合でも、Apacheはバーチャルホストをアルファベット順に処理します。

`docroot`のユーザおよびグループのオーナーシップを設定するには、[`docroot_owner`][]および[`docroot_group`][]パラメータを使用します。

``` puppet
apache::vhost { 'user.example.com':
  port          => '80',
  docroot       => '/var/www/user',
  docroot_owner => 'www-data',
  docroot_group => 'www-data',
}
```

#### SSLを使ったバーチャルホストの設定

[SSL encryption][]およびデフォルトのSSL証明書を使うようにバーチャルホストを設定するには、[`ssl`][]パラメータを設定します。また、[`port`][]パラメータを指定する必要もあります。通常は、'443'という値がHTTPSリクエストに対応します。

``` puppet
apache::vhost { 'ssl.example.com':
  port    => '443',
  docroot => '/var/www/ssl',
  ssl     => true,
}
```

SSLおよび固有SSL証明書を使うようにバーチャルホストを設定するには、[`ssl_cert`][]および[`ssl_key`][]パラメータで証明書およびキーへのパスを使用します。

``` puppet
apache::vhost { 'cert.example.com':
  port     => '443',
  docroot  => '/var/www/cert',
  ssl      => true,
  ssl_cert => '/etc/ssl/fourth.example.com.cert',
  ssl_key  => '/etc/ssl/fourth.example.com.key',
}
```

同じドメインでSSLと暗号化されていないバーチャルホストを混ぜて設定するには、それぞれを個別の[`apache::vhost`][]定義タイプで宣言します。

``` puppet
# The non-ssl virtual host
apache::vhost { 'mix.example.com non-ssl':
  servername => 'mix.example.com',
  port       => '80',
  docroot    => '/var/www/mix',
}

# The SSL virtual host at the same domain
apache::vhost { 'mix.example.com ssl':
  servername => 'mix.example.com',
  port       => '443',
  docroot    => '/var/www/mix',
  ssl        => true,
}
```

暗号化されていない接続をSSLにリダイレクトするようにバーチャルホストを設定するには、それぞれを個別の[`apache::vhost`][]定義タイプで宣言し、SSLが有効化されているバーチャルホストに、暗号化されていないリクエストをリダイレクトします。

``` puppet
apache::vhost { 'redirect.example.com non-ssl':
  servername      => 'redirect.example.com',
  port            => '80',
  docroot         => '/var/www/redirect',
  redirect_status => 'permanent',
  redirect_dest   => 'https://redirect.example.com/'
}

apache::vhost { 'redirect.example.com ssl':
  servername => 'redirect.example.com',
  port       => '443',
  docroot    => '/var/www/redirect',
  ssl        => true,
}
```

#### バーチャルホストのポートおよびアドレスのバインディング設定　

バーチャルホストはデフォルトですべてのIPアドレス('\*')をリッスンします。特定のIPアドレスをリッスンするようにバーチャルホストを設定するには、[`ip`][]パラメータを使用します。

``` puppet
apache::vhost { 'ip.example.com':
  ip      => '127.0.0.1',
  port    => '80',
  docroot => '/var/www/ip',
}
```

[`ip`][]パラメータにIPアドレスの配列を使えば、1つのバーチャルホストに複数のIPアドレスを設定することもできます。

``` puppet
apache::vhost { 'ip.example.com':
  ip      => ['127.0.0.1','169.254.1.1'],
  port    => '80',
  docroot => '/var/www/ip',
}
```

[`port`][]パラメータにポートの配列を使えば、1つのバーチャルホストに複数のポートを設定することができます。

``` puppet
apache::vhost { 'ip.example.com':
  ip      => ['127.0.0.1'],
  port    => ['80','8080']
  docroot => '/var/www/ip',
}
```

[エイリアスサーバ][]を使ってバーチャルホストを設定するには、[`serveraliases`][]パラメータを使ってエイリアスを指定します。

``` puppet
apache::vhost { 'aliases.example.com':
  serveraliases => [
    'aliases.example.org',
    'aliases.example.net',
  ],
  port          => '80',
  docroot       => '/var/www/aliases',
}
```

`/var/www/example.com`に'http://example.com.loc'をマッピングするケースのように、 同じ名前のディレクトリにマッピングされたサブドメイン用にワイルドカードエイリアスを使ってバーチャルホストを設定するには、[`serveraliases`][]パラメータを使ってワイルドカードエイリアスを、[`virtual_docroot`][]パラメータを使ってドキュメントルートを定義します。

``` puppet
apache::vhost { 'subdomain.loc':
  vhost_name      => '*',
  port            => '80',
  virtual_docroot => '/var/www/%-2+',
  docroot         => '/var/www',
  serveraliases   => ['*.loc',],
}
```

[フィルタルール][]を使ってバーチャルホストを設定するには、[`filters`][]パラメータを使って、フィルタディレクティブを[array][]として渡します。

``` puppet
apache::vhost { 'subdomain.loc':
  port    => '80',
  filters => [
    'FilterDeclare  COMPRESS',
    'FilterProvider COMPRESS DEFLATE resp=Content-Type $text/html',
    'FilterChain    COMPRESS',
    'FilterProtocol COMPRESS DEFLATE change=yes;byteranges=no',
  ],
  docroot => '/var/www/html',
}
```

#### アプリおよびプロセッサのバーチャルホストの設定　

[suPHP][]を使ってバーチャルホストを設定するには、以下のパラメータを使用します。

* [`suphp_engine`][]、suPHPエンジンを有効にします。
* [`suphp_addhandler`][]、MIMEタイプを定義します。
* [`suphp_configpath`][]、suPHPがPHPインタープリタに渡すパスを設定します。 
* [`directory`][]、ディレクトリ、ファイル、ロケーションの各ディレクティブブロックを設定します。

例:　

``` puppet
apache::vhost { 'suphp.example.com':
  port             => '80',
  docroot          => '/home/appuser/myphpapp',
  suphp_addhandler => 'x-httpd-php',
  suphp_engine     => 'on',
  suphp_configpath => '/etc/php5/apache2',
  directories      => [
    { 'path'  => '/home/appuser/myphpapp',
      'suphp' => {
        user  => 'myappuser',
        group => 'myappgroup',
      },
    },
  ],
}
```

[Python][]アプリケーション用の[Webサーバゲートウェイインターフェース][] (WSGI)を使ってバーチャルホストを設定するには、`wsgi`パラメータセットを使用します。

``` puppet
apache::vhost { 'wsgi.example.com':
  port                        => '80',
  docroot                     => '/var/www/pythonapp',
  wsgi_application_group      => '%{GLOBAL}',
  wsgi_daemon_process         => 'wsgi',
  wsgi_daemon_process_options => {
    processes    => '2',
    threads      => '15',
    display-name => '%{GROUP}',
  },
  wsgi_import_script          => '/var/www/demo.wsgi',
  wsgi_import_script_options  => {
    process-group     => 'wsgi',
    application-group => '%{GLOBAL}',
  },
  wsgi_process_group          => 'wsgi',
  wsgi_script_aliases         => { '/' => '/var/www/demo.wsgi' },
}
```

Apache 2.2.16の時点では、Apacheは[FallbackResource][]をサポートしています。これは、一般的なRewriteRulesに代わるシンプルなディレクティブです。[`fallbackresource`][]パラメータを使えば、FallbackResourceを設定できます。

``` puppet
apache::vhost { 'wordpress.example.com':
  port             => '80',
  docroot          => '/var/www/wordpress',
  fallbackresource => '/index.php',
}
```

> **注意**: Apache 2.2.24以降では、`fallbackresource`パラメータがサポートするのは'disabled'値のみです。

[コモンゲートウェイインターフェース][] (CGI)ファイル用の指定ディレクトリを使ってバーチャルホストを設定するには、[`scriptalias`][]パラメータを使って`cgi-bin`パスを定義します。

``` puppet
apache::vhost { 'cgi.example.com':
  port        => '80',
  docroot     => '/var/www/cgi',
  scriptalias => '/usr/lib/cgi-bin',
}
```

[Rack][]用のバーチャルホストを設定するには、[`rack_base_uris`][]パラメータを使用します。

``` puppet
apache::vhost { 'rack.example.com':
  port           => '80',
  docroot        => '/var/www/rack',
  rack_base_uris => ['/rackapp1', '/rackapp2'],
}
```

#### IPベースのバーチャルホストの設定　

任意のポートをリッスンし、固有IPアドレスのリクエストに応答する[IPベースのバーチャルホスト][]を設定することができます。この例では、サーバはポート80および81をリッスンします。これは、この例のバーチャルホストが[`port`][]パラメータにより宣言されて_いない_ ためです。

``` puppet
apache::listen { '80': }

apache::listen { '81': }
```

[`ip_based`][]パラメータを使ってIPベースのバーチャルホストを設定します。

``` puppet
apache::vhost { 'first.example.com':
  ip       => '10.0.0.10',
  docroot  => '/var/www/first',
  ip_based => true,
}

apache::vhost { 'second.example.com':
  ip       => '10.0.0.11',
  docroot  => '/var/www/second',
  ip_based => true,
}
```

任意の[SSL][SSL暗号化]構成と暗号化されていない構成を組み合わせ、IPベースと[名前ベースのバーチャルホスト][]を混ぜて設定することもできます。

この例では、1つのIPアドレス(この例では、10.0.0.10)に2つのIPベースのバーチャルホストを追加します。一方はSSLを使用するもの、もう一方は暗号化されていないものです。

``` puppet
apache::vhost { 'The first IP-based virtual host, non-ssl':
  servername => 'first.example.com',
  ip         => '10.0.0.10',
  port       => '80',
  ip_based   => true,
  docroot    => '/var/www/first',
}

apache::vhost { 'The first IP-based vhost, ssl':
  servername => 'first.example.com',
  ip         => '10.0.0.10',
  port       => '443',
  ip_based   => true,
  docroot    => '/var/www/first-ssl',
  ssl        => true,
}
```

次に、第2のIPアドレス(10.0.0.20)に2つの名前ベースのバーチャルホストを追加します。

``` puppet
apache::vhost { 'second.example.com':
  ip      => '10.0.0.20',
  port    => '80',
  docroot => '/var/www/second',
}

apache::vhost { 'third.example.com':
  ip      => '10.0.0.20',
  port    => '80',
  docroot => '/var/www/third',
}
```

10.0.0.10または10.0.0.20のいずれかで応答する名前ベースのバーチャルホストを追加するには、Apacheのデフォルトの`Listen 80`を無効にする**必要があります**。これは、前述のIPベースのバーチャルホストとコンフリクトするためです。無効にするには、[`add_listen`][]パラメータを`false`に設定します。

``` puppet
apache::vhost { 'fourth.example.com':
  port       => '80',
  docroot    => '/var/www/fourth',
  add_listen => false,
}

apache::vhost { 'fifth.example.com':
  port       => '80',
  docroot    => '/var/www/fifth',
  add_listen => false,
}
```

### Apacheモジュールのインストール　

Puppet apacheモジュールを使って[Apacheモジュール][]をインストールするには、2つの方法があります。

- [`apache::mod::<MODULE NAME>`][] クラスを使って、[パラメータを伴う固有のApacheモジュールをインストール][固有モジュールのインストール]する方法
- [`apache::mod`][]定義タイプを使って、[任意のApacheモジュールをインストール][任意モジュールのインストール]する方法

#### 固有モジュールのインストール

Puppet apacheモジュールは、多くの一般的な[Apacheモジュール][]のインストールをサポートしており、多くの場合、パラメータ化された設定オプションがあります。サポートされるApacheモジュールのリストについては、[`apache::mod::<MODULE NAME>`][]クラスリファレンスを参照してください。

例えば、[`apache::mod::ssl`][]クラスを宣言すれば、デフォルト設定で`mod_ssl` Apacheモジュールをインストールすることができます。

``` puppet
class { 'apache::mod::ssl': }
```

[`apache::mod::ssl`][]には複数のパラメータ化されたオプションがあり、宣言する際に設定することができます。たとえば、圧縮を有効にして`mod_ssl`を有効化するには、[`ssl_compression`][]パラメータをtrueに設定します。

``` puppet
class { 'apache::mod::ssl':
  ssl_compression => true,
}
```

一部のモジュールには必須条件があります。[`apache::mod::<MODULE NAME>`][]のリファレンスを参照してください。

#### 任意モジュールのインストール

オペレーティングシステムのパッケージマネージャでインストール可能な任意のモジュールの名前を[`apache::mod`][]定義タイプに渡し、それをインストールすることができます。固有モジュールクラスとは異なり、 [`apache::mod`][]定義タイプでは、インストールされている他のモジュールや固有のパラメータに基づいてインストールが調整されることはありません。Puppetはモジュールのパッケージを取得し、インストールするだけです。詳細な設定はユーザが必要に応じて行います。

例えば、[`mod_authnz_external`][] Apacheモジュールをインストールするには、'mod_authnz_external'の名前を使って定義タイプを宣言します。

``` puppet
apache::mod { 'mod_authnz_external': }
```

この方法でApacheモジュールを定義する際には、いくつかのオプションパラメータを指定できます。詳細については、[定義タイプのリファレンス][`apache::mod`]を参照してください。

### PHPファイルを処理するFastCGIサーバの設定

[`apache::fastcgi::server`][]定義タイプを追加すれば、 [FastCGI][]サーバで特定のファイルに関するリクエストを処理することができます。以下の例では、PHPリクエストを処理するFastCGIサーバをポート9000の127.0.0.1 (ローカルホスト)で定義しています。

``` puppet
apache::fastcgi::server { 'php':
  host       => '127.0.0.1:9000',
  timeout    => 15,
  flush      => false,
  faux_path  => '/var/www/php.fcgi',
  fcgi_alias => '/php.fcgi',
  file_type  => 'application/x-httpd-php'
}
```

[`custom_fragment`][]パラメータを使えば、指定したファイルタイプがFastCGIサーバで処理されるように、バーチャルホストを設定することができます。

``` puppet
apache::vhost { 'www':
  ...
  custom_fragment => 'AddType application/x-httpd-php .php'
  ...
}
```

### ロードバランシングの例

Apacheは、[`mod_proxy`][] Apacheモジュールを通じて、複数のグループのサーバにわたるロードバランシングをサポートしています。Puppetでは、[`apache::balancer`][]および[`apache::balancermember`][]定義タイプにより、Apacheロードバランシンググループ(バランサクラスタとも呼ばれます)をサポートしています。

[エクスポートリソース][]でロードバランシングを有効にするには、[`apache::balancermember`][]定義タイプをロードバランサメンバーサーバからエクスポートします。

``` puppet
@@apache::balancermember { "${::fqdn}-puppet00":
  balancer_cluster => 'puppet00',
  url              => "ajp://${::fqdn}:8009",
  options          => ['ping=5', 'disablereuse=on', 'retry=5', 'ttl=120'],
}
```

次に、プロキシサーバでロードバランシンググループを作成します。

``` puppet
apache::balancer { 'puppet00': }
```

リソースをエクスポートせずにロードバランシングを有効にするには、プロキシサーバで以下を宣言します。

``` puppet
apache::balancer { 'puppet00': }

apache::balancermember { "${::fqdn}-puppet00":
  balancer_cluster => 'puppet00',
  url              => "ajp://${::fqdn}:8009",
  options          => ['ping=5', 'disablereuse=on', 'retry=5', 'ttl=120'],
}
```

次に、プロキシサーバで`apache::balancer`および`apache::balancermember`定義タイプを宣言します。

バランサで[ProxySet](https://httpd.apache.org/docs/current/mod/mod_proxy.html#proxyset)ディレクティブを使うには、`apache::balancer`の[`proxy_set`](#proxy_set)パラメータを使用します。

``` puppet
apache::balancer { 'puppet01':
  proxy_set => {
    'stickysession' => 'JSESSIONID',
    'lbmethod'      => 'bytraffic',
  },
}
```

ロードバランシングのスケジューラのアルゴリズム(`lbmethod`)は、[mod_proxy_balancerドキュメント](https://httpd.apache.org/docs/current/mod/mod_proxy_balancer.html)に記載されています。

## リファレンス

- [**パブリッククラス**](#public-classes)
    - [クラス: apache](#class-apache)
    - [クラス: apache::dev](#class-apachedev)
    - [クラス: apache::vhosts](#class-apachevhosts)
    - [クラス: apache::mod::\*](#classes-apachemodname)
- [**プライベートクラス**](#private-classes)
    - [クラス: apache::confd::no_accf](#class-apacheconfdno_accf)
    - [クラス: apache::default_confd_files](#class-apachedefault_confd_files)
    - [クラス: apache::default_mods](#class-apachedefault_mods)
    - [クラス: apache::package](#class-apachepackage)
    - [クラス: apache::params](#class-apacheparams)
    - [クラス: apache::service](#class-apacheservice)
    - [クラス: apache::version](#class-apacheversion)
- [**パブリック定義タイプ**](#public-defined-types)
    - [定義タイプ: apache::balancer](#defined-type-apachebalancer)
    - [定義タイプ: apache::balancermember](#defined-type-apachebalancermember)
    - [定義タイプ: apache::custom_config](#defined-type-apachecustom_config)
    - [定義タイプ: apache::fastcgi::server](#defined-type-fastcgi-server)
    - [定義タイプ: apache::listen](#defined-type-apachelisten)
    - [定義タイプ: apache::mod](#defined-type-apachemod)
    - [定義タイプ: apache::namevirtualhost](#defined-type-apachenamevirtualhost)
    - [定義タイプ: apache::vhost](#defined-type-apachevhost)
    - [定義タイプ: apache::vhost::custom](#defined-type-apachevhostcustom)
- [**プライベート定義タイプ**](#private-defined-types)
    - [定義タイプ: apache::default_mods::load](#defined-type-default_mods-load)
    - [定義タイプ: apache::peruser::multiplexer](#defined-type-apacheperusermultiplexer)
    - [定義タイプ: apache::peruser::processor](#defined-type-apacheperuserprocessor)
    - [定義タイプ: apache::security::file_link](#defined-type-apachesecurityfile_link)
- [**テンプレート**](#templates)

### パブリッククラス

#### クラス: `apache`

システムでのApacheの基本的な設定とインストールをガイドします。

デフォルトオプションを用いてこのクラスを宣言すると、Puppetでは以下が実行されます。

- オペレーティングシステムに適したApacheソフトウェアパッケージおよび[必要なApacheモジュール](#default_mods)をインストールします。
- [デフォルトロケーション](#conf_dir)を用いて、ディレクトリ内に必要な設定ファイルを配置します。デフォルトロケーションは、オペレーティングシステムによって異なります。
- デフォルトのバーチャルホストおよび標準的なポート('80')とアドレス('\*')のバインディングを用いてサーバを設定します。
- ドキュメントルートディレクトリを作成します。オペレーティングシステムによって異なりますが、通常は`/var/www`です。
- Apacheサービスを開始します。

ここでは、デフォルトの`apache`クラスを宣言するだけです。

``` puppet
class { 'apache': }
```

##### `allow_encoded_slashes`

[`AllowEncodedSlashes`][]宣言のサーバデフォルトを設定します。これにより、'\'および'/'を含むURLに対する応答が変更されます。このパラメータを指定しない場合、サーバの設定でこの宣言が省かれ、Apacheのデフォルト設定'off'が使用されます。

値: 'on'、'off'、'nodecode'。

デフォルト値: `undef`。

##### `apache_version`

使用するApacheのバージョンを定義し、モジュールテンプレートの挙動、パッケージ名、デフォルトのApacheモジュールを設定します。このパラメータを理由なく手動で設定することは、推奨していません。

デフォルト値: [`apache::version`][]クラスにより検出されたオペレーティングシステムとリリースバージョンによって異なります。

##### `conf_dir`

Apacheサーバのメイン設定ファイルを置くディレクトリを設定します。

デフォルト値: オペレーティングシステムによって異なります。

- **Debian**: `/etc/apache2`
- **FreeBSD**: `/usr/local/etc/apache22`
- **Gentoo**: `/etc/apache2`
- **Red Hat**: `/etc/httpd/conf`

##### `conf_template`

メインのApache設定ファイルで使用される[テンプレート][]を定義します。apacheモジュールは、`conf.d`エントリによりカスタマイズされた最小限の設定ファイルを使用するように設計されているため、このパラメータの変更には潜在的なリスクが伴います。

デフォルト値: `apache/httpd.conf.erb`。

##### `confd_dir`

Apacheサーバのカスタム設定ディレクトリの場所を設定します。

デフォルト値: オペレーティングシステムによって異なります。

- **Debian**: `/etc/apache2/conf.d`
- **FreeBSD**: `/usr/local/etc/apache22`
- **Gentoo**: `/etc/apache2/conf.d`
- **Red Hat**: `/etc/httpd/conf.d`

##### `default_charset`

メイン設定ファイルで[`AddDefaultCharset`][]ディレクティブとして使用されます。

デフォルト値: `undef`。

##### `default_confd_files`

[`confd_dir`][]パラメータにより定義されるディレクトリに、インクルード可能なApache設定ファイルのデフォルトセットを生成するかどうかを決定します。この設定ファイルは、サーバのオペレーティングシステムにApacheパッケージとともに通常インストールされるものに相当します。

ブーリアン。

デフォルト値: `true`。

##### `default_mods`

オペレーティングシステムに応じたデフォルトの[Apacheモジュール][]のセットを設定して有効にするかどうかを決定します。

`false`の場合、Puppetはオペレーティングシステム上でHTTPデーモンを機能させるのに必要なApacheモジュールのみを含めます。[`apache::mod::<MODULE NAME>`][]クラスまたは[`apache::mod`][]定義タイプを使えば、他のモジュールを個別に宣言することができます。

`true`の場合、Puppetはオペレーティングシステムと [`apache_version`][]および[`mpm_module`][]パラメータの値に応じて、その他のモジュールもインストールします。このモジュールリストは頻繁に変更されるので、最新のリストについては[Puppetモジュールのコード][]を参照してください。

このパラメータに配列が含まれる場合、Puppetは渡されたすべてのApacheモジュールを有効にします。

値: ブーリアンまたはApacheモジュール名の配列。

デフォルト値: `true`。

##### `default_ssl_ca`

Apacheサーバのデフォルトの証明書認証局を設定します。

デフォルト値を使えばApacheサーバは機能しますが、本稼働環境にこのサーバをデプロイする前に、各自の認証局情報を用いてこのパラメータを更新する**必要があります**。

ブーリアン。

デフォルト値: `undef`。

##### `default_ssl_cert`

[SSL暗号化][]証明書の保存場所を設定します。

デフォルト値を使えばApacheサーバは機能しますが、本稼働環境にこのサーバをデプロイする前に、各自の証明書ロケーション情報を用いてこのパラメータを更新する**必要があります**。

デフォルト値: オペレーティングシステムによって異なります。

- **Debian**: `/etc/ssl/certs/ssl-cert-snakeoil.pem`
- **FreeBSD**: `/usr/local/etc/apache22/server.crt`
- **Gentoo**: `/etc/ssl/apache2/server.crt`
- **Red Hat**: `/etc/pki/tls/certs/localhost.crt`

##### `default_ssl_chain`

デフォルトの[SSLチェーン][]の保存場所を設定します。 

デフォルト値を使えばApacheサーバは機能しますが、本稼働環境にこのサーバをデプロイする前に、各自のSSLチェーンを用いてこのパラメータを更新する**必要があります**。

デフォルト値: `undef`。

##### `default_ssl_crl`

使用するデフォルトの[証明書失効リスト][] (CRL)ファイルのパスを設定します。

デフォルト値を使えばApacheサーバは機能しますが、本稼働環境にこのサーバをデプロイする前に、CRLファイルパスを用いてこのパラメータを更新する**必要があります**。このパラメータは、[`default_ssl_crl_path`][]とともに使用することも、その代わりに使用することもできます。

デフォルト値: `undef`。

##### `default_ssl_crl_path`

サーバの[証明書失効リストパス][]を設定します。これにはCRLが含まれます。

デフォルト値を使えばApacheサーバは機能しますが、本稼働環境でこのサーバをデプロイする前に、CRLファイルパスを用いてこのパラメータを更新する**必要があります**。

デフォルト値: `undef`。

##### `default_ssl_crl_check`

[`SSLCARevocationCheck`][]ディレクティブを通じてデフォルトの証明書失効チェックレベルを設定します。このパラメータはApache 2.4以上にのみ適用され、それ以前のバージョンでは無視されます。

デフォルト値を使えばApacheサーバは機能しますが、本稼働環境で証明書失効リストを使用する際には、このパラメータを指定する**必要があります**。

デフォルト値: `undef`。

##### `default_ssl_key`

[SSL証明書キーファイル][]の保存場所を設定します。

デフォルト値を使えばApacheサーバは機能しますが、本稼働環境にこのサーバをデプロイする前に、各自のSSLキーのロケーションを用いてこのパラメータを更新する**必要があります**。

デフォルト値: オペレーティングシステムによって異なります。

- **Debian**: `/etc/ssl/private/ssl-cert-snakeoil.key`
- **FreeBSD**: `/usr/local/etc/apache22/server.key`
- **Gentoo**: `/etc/ssl/apache2/server.key`
- **Red Hat**: `/etc/pki/tls/private/localhost.key`


##### `default_ssl_vhost`

デフォルトの[SSL][SSL暗号化]バーチャルホストを設定します。

`true`の場合、Puppetは [`apache::vhost`][]定義タイプを用いて、以下のバーチャルホストを自動的に設定します。

```puppet
apache::vhost { 'default-ssl':
  port            => 443,
  ssl             => true,
  docroot         => $docroot,
  scriptalias     => $scriptalias,
  serveradmin     => $serveradmin,
  access_log_file => "ssl_${access_log_file}",
  }
```

> **注意**: SSLバーチャルホストはHTTPSクエリにのみ応答します。


ブーリアン。

デフォルト値: `false`。

##### `default_type`

_Apache 2.2のみ_。サーバが他の方法で適切な`content-type`を決定できない場合に送信される[MIME `content-type`][]を設定します。このディレクティブはApache 2.4以降では廃止予定になっており、設定ファイルの下位互換性確保の目的でのみ使われます。 

デフォルト値: `undef`。

##### `default_vhost`

クラスが宣言された際にデフォルトのバーチャルホストを設定します。

[カスタマイズしたバーチャルホスト][バーチャルホストの設定]を設定するには、このパラメータの値を`false`に設定します。

> **注意**: 少なくとも1つのバーチャルホストがなければ、Apacheは起動しません。このパラメータを`false`に設定する場合は、別の場所でバーチャルホストを設定する必要があります。

ブーリアン。

デフォルト値: `true`。

##### `dev_packages`

使用する固有devパッケージを設定します。

値: 文字列または文字列の配列。

IUS yumリポジトリからhttpd 2.4を使用する例:

``` puppet
include ::apache::dev
class { 'apache':
 apache_name => 'httpd24u',
 dev_packages => 'httpd24u-devel',
}
```

デフォルト値: オペレーティングシステムによって異なります。

- **Red Hat:** 'httpd-devel'
- **Debian 8/Ubuntu 13.10以降:** ['libaprutil1-dev', 'libapr1-dev', 'apache2-dev']
- **それ以前のDebian/Ubuntuバージョン:** ['libaprutil1-dev', 'libapr1-dev', 'apache2-prefork-dev']
- **FreeBSD, Gentoo:** `undef`
- **Suse:** ['libapr-util1-devel', 'libapr1-devel']

##### `docroot`

デフォルトの[`DocumentRoot`][]の場所を設定します。

デフォルト値: オペレーティングシステムによって異なります。

- **Debian**: `/var/www/html`
- **FreeBSD**: `/usr/local/www/apache22/data`
- **Gentoo**: `/var/www/localhost/htdocs`
- **Red Hat**: `/var/www/html`

##### `error_documents`

Apacheサーバの[カスタムエラードキュメント][]を有効にするかどうかを決定します。

ブーリアン。

デフォルト値: `false`。

##### `group`

リクエストに応答するために生成されるApacheプロセスを所有するグループIDを設定します。

デフォルトでは、Puppetはこのグループを`apache`クラスの下のリソースとして管理するよう試み、[`apache::params`][]クラスにより検出されたオペレーティングシステムに基づいてグループを決定します。このグループリソースを作成せずに、別のPuppetモジュールで作成されたグループを使用するには、[`manage_group`][]パラメータの値を`false`に設定します。

> **注意**: このパラメータを修正すると、Apacheが子プロセスを生成してリソースにアクセスする際に使用するグループIDのみが変更されます。親サーバプロセスを所有するユーザは変更されません。

##### `httpd_dir`

Apacheサーバの基本設定ディレクトリを設定します。これは、特別に再パッケージされたApacheサーバビルドにおいて、デフォルトのディストリビューションパッケージと組み合わせると意図せぬ結果が生じる可能性がある場合に役立ちます。

デフォルト値: オペレーティングシステムによって異なります。

- **Debian**: `/etc/apache2`
- **FreeBSD**: `/usr/local/etc/apache22`
- **Gentoo**: `/etc/apache2`
- **Red Hat**: `/etc/httpd`

##### `http_protocol_options`

HTTPプロトコルチェックの厳密さを指定します。

有効なオプション: 以下の値の選択肢のシーケンス: `Strict`または`Unsafe`、`RegisteredMethods`または`LenientMethods`、`Allow0.9`または`Require1.0`。

デフォルト '`Strict LenientMethods Allow0.9`'。

##### `keepalive`

[`KeepAlive`][]ディレクティブによってHTTPの持続的接続を有効にするかどうかを決定します。 'On'に設定する場合は、[`keepalive_timeout`][]および[`max_keepalive_requests`][]パラメータを使って関連オプションを設定してください。 

値: 'Off', 'On'。

デフォルト値: 'Off'。

##### `keepalive_timeout`

[`KeepAliveTimeout`]ディレクティブによって、HTTPの持続的接続でApacheサーバが後続のリクエストを行うまでの待機時間を設定します。このパラメータが意味を持つのは、[`keepalive` parameter][]を有効にしている場合のみです。

デフォルト値: '15'。

##### `max_keepalive_requests`

[`keepalive` parameter][]が有効の場合に、1回の接続で許可されるリクエストの数を制限します。

デフォルト値: '100'。

##### `lib_path`

[Apacheモジュール][Apacheモジュール]ファイルの保存場所を指定します。

デフォルト値: オペレーティングシステムによって異なります。

- **Debian**および**Gentoo**: `/usr/lib/apache2/modules`
- **FreeBSD**: `/usr/local/libexec/apache24`
- **Red Hat**: `modules`

> **注意**: このパラメータは、特別な理由がない限り手動で設定しないでください。

##### `log_level`

エラーログの詳細レベルを変更します。値: 'alert'、'crit'、'debug'、'emerg'、'error'、'info'、'notice'、'warn'。

デフォルト値: 'warn'。

##### `log_formats`

追加の[`LogFormat`][]ディレクティブを定義します。値: [ハッシュ][]、例:

``` puppet
$log_formats = { vhost_common => '%v %h %l %u %t \"%r\" %>s %b' }
```

Puppetの作成する`httpd.conf`には、以下のような複数の`LogFormats`が事前定義されています。

``` httpd
LogFormat "%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"" combined
LogFormat "%h %l %u %t \"%r\" %>s %b" common
LogFormat "%{Referer}i -> %U" referer
LogFormat "%{User-agent}i" agent
LogFormat "%{X-Forwarded-For}i %l %u %t \"%r\" %s %b \"%{Referer}i\" \"%{User-agent}i\"" forwarded
```

定義した`log_formats`パラメータに上記のいずれかが含まれる場合は、**ユーザの**定義により上書きされます。

##### `logroot`

バーチャルホストのApacheログファイルのディレクトリを変更します。

デフォルト値: オペレーティングシステムによって異なります。

- **Debian**: `/var/log/apache2`
- **FreeBSD**: `/var/log/apache22`
- **Gentoo**: `/var/log/apache2`
- **Red Hat**: `/var/log/httpd`

##### `logroot_mode`

デフォルトの[`logroot`][]ディレクトリをオーバーライドします。

> **注意**: 影響を把握できない場合は、ログが保存されているディレクトリへの書き込みアクセス権限を付与_しないで_ください。詳細については、[Apacheドキュメント][ログセキュリティ]を参照してください。

デフォルト値: `undef`。

##### `manage_group`

`false`の場合、Puppetではグループリソースは作成されません。

別のPuppetモジュールで作成されたグループをApacheの実行に使用する場合は、この値を`false`に設定してください。このパラメータを設定せずに過去に作成されたグループを使用しようとすると、重複リソースエラーが生じます。

ブーリアン。

デフォルト値: `true`。

##### `supplementary_groups`

ユーザの所属するグループのリスト。主要グループに加えて設定する場合に使用します。

デフォルト値: 追加グループなし。

注意: このオプションは、`manage_user`がtrueに設定されている場合のみ有効です。

##### `manage_user`

`false`の場合、Puppetではユーザリソースが作成されません。

このパラメータは、別のPuppetモジュールで作成されたユーザをApache実行に使用する場合などに使用します。このパラメータを設定せずに過去に作成されたユーザを使用しようとすると、重複リソースエラーが生じます。

ブーリアン。

デフォルト値: `true`。

##### `mod_dir`

Puppetが[Apacheモジュール][]の設定ファイルを置く場所を設定します。

デフォルト値: オペレーティングシステムによって異なります。

- **Debian**: `/etc/apache2/mods-available`
- **FreeBSD**: `/usr/local/etc/apache22/Modules`
- **Gentoo**: `/etc/apache2/modules.d`
- **Red Hat**: `/etc/httpd/conf.d`

##### `mod_packages`

デフォルトのモジュールパッケージ名をユーザがオーバーライドすることを許可します。

```puppet
include apache::params
class { 'apache':
 mod_packages => merge($::apache::params::mod_packages, {
 'auth_kerb' => 'httpd24-mod_auth_kerb',
 })
}
```

ハッシュ。デフォルト値: `$apache::params::mod_packages`。

##### `mpm_module`

HTTPDプロセスに関してロードおよび設定する[マルチプロセッシングモジュール][] (MPM)を決定します。値: 'event'、'itk'、'peruser'、'prefork'、'worker'、`false`。

カスタムパラメータを用いて以下のクラスを明示的に宣言するためには、このパラメータを`false`に設定する必要があります。 

- [`apache::mod::event`][]
- [`apache::mod::itk`][]
- [`apache::mod::peruser`][]
- [`apache::mod::prefork`][]
- [`apache::mod::worker`][]

デフォルト値: オペレーティングシステムによって異なります。

- **Debian**: 'worker'
- **FreeBSD、Gentoo、Red Hat**: 'prefork'

##### `package_ensure`

`package`リソースの[`ensure`][]属性を制御します。値: 'absent'、'installed' (またはそれに相当する'present')、またはバージョン文字列。

デフォルト値: 'installed'。

##### `pidfile`

pidファイルのカスタムロケーションの設定を許可します。カスタムビルトのApache rpmを使用する場合に役立ちます。

デフォルト値: オペレーティングシステムによって異なります。

- **Debian:** '\${APACHE_PID_FILE}'
- **FreeBSD:** '/var/run/httpd.pid'
- **Red Hat:** 'run/httpd.pid'

##### `ports_file`

Apacheポート設定を含むファイルのパスを設定します。

デフォルト値: '{$conf_dir}/ports.conf'。

##### `purge_configs`

他のすべてのApache設定およびバーチャルホストを削除します。

このパラメータを`false`に設定すると、一時的な対策として、既存の設定や管理されていない設定をApacheモジュールと共存させることができます。この場合、設定をこのモジュール内のリソースに移すことを推奨します。バーチャルホストの設定については、[`purge_vhost_dir`][]を参照してください。

ブーリアン。

デフォルト値: `true`。

##### `purge_vhost_dir`

[`vhost_dir`][]パラメータの値が[`confd_dir`][]パラメータの値と異なる場合は、このパラメータにより、Puppetにより管理されて_いない_`vhost_dir`内の設定を削除するかどうかが決定されます。

`purge_vhost_dir`を`false`に設定すると、一時的な対策として、`vhost_dir`内の既存の設定や管理されていない設定をapacheモジュールと共存させることができます。

ブーリアン。

デフォルト値: [`purge_configs`][]と同じ。

##### `rewrite_lock`

リライトロックのカスタムロケーションの設定を可能にします。これは、バーチャルホストの[`rewrites`][]パラメータでタイプprgのRewriteMapを使用している場合のベストプラクティスとされています。このパラメータは、Apacheバージョン2.2以前のみに適用され、それよりも新しいバージョンでは無視されます。

デフォルト値: `undef`。

##### `sendfile`

[`EnableSendfile`][]ディレクティブで静的ファイルをサーブする際に、ApacheがLinuxカーネルの`sendfile`サポートを使用するようにします。値: 'On'、'Off'。

デフォルト値: 'On'。

##### `serveradmin`

Apacheの[`ServerAdmin`][]ディレクティブでApacheサーバ管理者の連絡先情報を設定します。

デフォルト値: 'root@localhost'。

##### `servername`

Apacheの[`ServerName`][]ディレクティブでApacheサーバ名を設定します。

`false`に設定すると、ServerNameは設定されません。

デフォルト値:  [Facter][]により報告された'fqdn' fact。

##### `server_root`

Apacheの[`ServerRoot`][]ディレクティブでApacheサーバのルートを設定します。

デフォルト値: オペレーティングシステムによって異なります。

- **Debian**: `/etc/apache2`
- **FreeBSD**: `/usr/local`
- **Gentoo**: `/var/www`
- **Red Hat**: `/etc/httpd`

##### `server_signature`

Apacheの[`ServerSignature`][]ディレクティブで、エラードキュメントや一部の[Apacheモジュール][]のアウトプットなどの、サーバ生成ドキュメントの下部に表示される末尾のフッタの行を設定します。値: 'Off'、'On'。

デフォルト値: 'On'。

##### `server_tokens`

Apacheの[`ServerTokens`][]ディレクティブで、Apacheからブラウザに送信される、Apacheやオペレーティングシステムに関する情報の量を制御します。

デフォルト値: 'OS'。

##### `service_enable`

システムの起動時にPuppetがApache HTTPDサービスを有効にするかどうかを決定します。

ブーリアン。

デフォルト値: `true`。

##### `service_ensure`

サービスが稼働していることをPuppetが確認するかどうかを決定します。値: `true` (または'running')、`false` (または'stopped')。

値を`false`または'stopped'にすると、'httpd'サービスリソースの`ensure`パラメータが`false`に設定されます。この設定は、Pacemakerなどの別のアプリケーションでサービスを管理する場合に役立ちます。

デフォルト値: 'running'。

##### `service_name`

Apacheサービスの名前を設定します。

デフォルト値: オペレーティングシステムによって異なります。

- **DebianおよびGentoo**: 'apache2'
- **FreeBSD**: 'apache22'
- **Red Hat**: 'httpd'

##### `service_manage`

PuppetでHTTPDサービスの状態を管理するかどうかを決定します。

ブーリアン。

デフォルト値: `true`。

##### `service_restart`

HTTPDサービスの再起動にあたり、Puppetが特定のコマンドを使用するかどうかを決定します。

値: Apacheサービスを再起動するためのコマンド。デフォルト設定では、 [デフォルトのPuppet挙動][サービス属性リスタート]が使われます。

デフォルト値: `undef`。

##### `ssl_ca`

SSL証明書認証局を指定します。[SSLCACertificateFile](https://httpd.apache.org/docs/current/mod/mod_ssl.html#sslcacertificatefile)を使用してSSLクライアント認証で使用する証明書を確認します。

これはバーチャルホストレベルでオーバーライドすることが可能です。

デフォルト値: `undef`。


##### `timeout`

Apacheの[`TimeOut`][]ディレクティブを設定します。このディレクティブは、一部のイベントに関してリクエスト履行を止めるまでの Apacheの待機秒数を定義します。

デフォルト値: 120。

##### `trace_enable`

[`TraceEnable`][]ディレクティブで、Apacheが`TRACE`リクエスト([RFC 2616][]ごと)をどのように処理するかを制御します。

値: 'Off', 'On'。

デフォルト値: 'On'。

##### `use_systemd`

systemdモジュールをCentos 7サーバにインストールするかどうかを制御します。これは、カスタムビルトのRPMを使用している場合は特に役立ちます。 

ブーリアン。

デフォルト値: `true`。

##### `file_mode`

設定ファイルの許可モードを設定します。

値: 文字列、記号表記法または数字表記法での許可モード。

デフォルト値: '0644'。

##### `root_directory_options`

httpd.confの/ディレクトリで指定するオプションの配列。

デフォルト値: 'FollowSymLinks'。

##### `root_directory_secured`

httpd.confの/ディレクトリについて、デフォルトのアクセスポリシーを設定します。`false`にすると、特定のアクセスポリシーがないすべてのリソースへのアクセスが許可されます。 `true`にするとデフォルトですべてのリソースへのアクセスが拒否されます。`true`の場合、リソースへのアクセスを許可するには、具体的なルールを使用する必要があります([`directories`](#parameter-directories-for-apachevhost)パラメータを用いたディレクトリブロックなどで)。

ブーリアン。

デフォルト値: `false`。

##### `vhost_dir`

バーチャルホストの設定ファイルの保存場所を変更します。

デフォルト値: オペレーティングシステムによって異なります。

- **Debian**: `/etc/apache2/sites-available`
- **FreeBSD**: `/usr/local/etc/apache22/Vhosts`
- **Gentoo**: `/etc/apache2/vhosts.d`
- **Red Hat**: `/etc/httpd/conf.d`

##### `vhost_include_pattern`

`vhost_dir`に含まれるファイルのパターンを定義します。

`[^.#]\*.conf[^~]`などの値に設定すると、このディレクトリで偶発的に作成されたファイル(バージョン管理システムやエディタのバックアップにより作成されたファイルなど)がサーバ設定に*含まれなく*なります。

デフォルト値: '*'。

一部のオペレーティングシステムでは、`*.conf`の値が使用されます。デフォルトでは、このモジュールは`.conf`で終わる設定ファイルを作成します。

##### `user`

Apacheがリクエストの応答に使用するユーザを変更します。Apacheの親プロセスは引き続きルートとして稼働しますが、子プロセスはこのパラメータで定義されたユーザとしてリソースにアクセスします。Puppetがこのユーザを管理しないようにするには、[`manage_user`][]パラメータを`false`に設定します。

デフォルト値: [`apache::params`][]クラスにより設定されたユーザに依存します。これはオペレーティングシステムによって異なります。

- **Debian**: 'www-data'
- **FreeBSD**: 'www'
- **Gentoo**および**Red Hat**: 'apache'

Puppetがこのユーザを管理しないようにするには、[`manage_user`][]パラメータをfalseに設定します。

##### `apache_name`

インストールするApacheパッケージの名前。Red Hatのソフトウェアコレクションのパッケージなど、標準的ではないApacheパッケージを使用している場合は、デフォルト設定をオーバーライドする必要があるかもしれません。

デフォルト値: [`apache::params`][]クラスにより設定されたユーザに依存します。これはオペレーティングシステムによって異なります。

- **Debian**: 'apache2'
- **FreeBSD**: 'apache24'
- **Gentoo**: 'www-servers/apache'
- **Red Hat**: 'httpd'

##### `error_log`

メインサーバインスタンスのエラーログファイルの名前。`/`、`|`、または`syslog`で始まる文字列の場合、フルパスが設定されます。それ以外の場合は、ファイル名の先頭に`$logroot`がつきます。

デフォルト値: オペレーティングシステムによって異なります。

- **Debian**: 'error.log'
- **FreeBSD**: 'httpd-error.log'
- **Gentoo**: 'error.log'
- **Red Hat**: 'error_log'
- **Suse**: 'error.log'

##### `scriptalias`

グローバルスクリプトエイリアスに使用するディレクトリ。

デフォルト値: オペレーティングシステムによって異なります。

- **Debian**: '/usr/lib/cgi-bin'
- **FreeBSD**: '/usr/local/www/apache24/cgi-bin'
- **Gentoo**: 'var/www/localhost/cgi-bin'
- **Red Hat**: '/var/www/cgi-bin'
- **Suse**: '/usr/lib/cgi-bin'

##### `access_log_file`

メインサーバインスタンスのアクセスログファイルの名前。

デフォルト値: オペレーティングシステムによって異なります。

- **Debian**: 'error.log'
- **FreeBSD**: 'httpd-access.log'
- **Gentoo**: 'access.log'
- **Red Hat**: 'access_log'
- **Suse**: 'access.log'

#### クラス: `apache::dev`

Apache開発ライブラリをインストールします。

デフォルト値: オペレーティングシステムによって異なります。使用するオペレーティングシステムに基づく、[`apache::params`][]クラスの[`dev_packages`][]パラメータ。

- **Debian**: Ubuntu 13.10およびDebian 8では'libaprutil1-dev'、'libapr1-dev'、'apache2-dev'。その他のバージョンでは'apache2-prefork-dev'。
- **FreeBSD**: `undef`; FreeBSDでは、`apache::dev`を宣言する前に`apache::package`または`apache`クラスを宣言する必要があります。
- **Gentoo**: `undef`
- **Red Hat**: 'httpd-devel'

#### クラス: `apache::vhosts`

[`apache::vhost`][]定義タイプを作成します。

**パラメータ**:

* `vhosts`: [`apache::vhost`][]定義タイプのパラメータを指定します。

  値: [ハッシュ][]、キーは名前を表し、値は[`apache::vhost`][]定義タイプのパラメータの[ハッシュ][]を表します。

  デフォルト値: '{}'。

  > **注意**: すべてのバーチャルホストのパラメータのリストや[バーチャルホストの設定]については、[`apache::vhost`][]定義タイプのリファレンスを参照してください。

  例えば、[名前ベースのバーチャルホスト][名前ベースのバーチャルホスト]のcustom_vhost_1を作成するには、`vhosts`パラメータを'{ "custom_vhost_1" => { "docroot" => "/var/www/custom_vhost_1", "port" => "81" }'に設定し、このクラスを宣言します。

``` puppet
class { 'apache::vhosts':
  vhosts => {
    'custom_vhost_1' => {
      'docroot' => '/var/www/custom_vhost_1',
      'port'    => '81',
    },
  },
}
```

#### クラス: `apache::mod::<MODULE NAME>`

指定した[Apacheモジュール][]を有効にします。Apacheモジュールを有効にして設定するには、このクラスを宣言します。

例えば、アイコンなしで[`mod_alias`][]をインストールして有効にするには、`icons_options`パラメータをNone'に設定して[`apache::mod::alias`][]クラスを宣言します。

``` puppet
class { 'apache::mod::alias':
  icons_options => 'None',
}
```

以下のApacheモジュールにはサポートするクラスがあり、その多くは、パラメータ化された設定が可能です。[`apache::mod`][]定義タイプを使えば、他のApacheモジュールをインストールできます。

* `actions`
* `alias` ([`apache::mod::alias`][]参照)
* `auth_basic`
* `auth_cas`\* ([`apache::mod::auth_cas`][]参照)
* `auth_mellon`\* ([`apache::mod::auth_mellon`][]参照)
* `auth_kerb`
* `authn_core`
* `authn_dbd`\* ([`apache::mod::authn_dbd`][]参照)
* `authn_file`
* `authnz_ldap`\* ([`apache::mod::authnz_ldap`][]参照)
* `authnz_pam`
* `authz_default`
* `authz_user`
* `autoindex`
* `cache`
* `cgi`
* `cgid`
* `cluster` ([`apache::mod::cluster`][]参照)
* `dav`
* `dav_fs`
* `dav_svn`\*
* `dbd`
* `deflate\`
* `dev`
* `dir`\*
* `disk_cache` ([`apache::mod::disk_cache`][]参照)
* `dumpio` ([`apache::mod::dumpio`][]参照)
* `env`
* `event` ([`apache::mod::event`][]参照)
* `expires`
* `ext_filter` ([`apache::mod::ext_filter`][]参照)
* `fastcgi`
* `fcgid`
* `filter`
* `geoip` ([`apache::mod::geoip`][]参照)
* `headers`
* `include`
* `info`\*
* `intercept_form_submit`
* `itk`
* `jk` ([`apache::mod::jk`]参照)
* `ldap` ([`apache::mod::ldap`][]参照)
* `lookup_identity`
* `macro` ([`apache:mod:macro`][]参照)
* `mime`
* `mime_magic`\*
* `negotiation`
* `nss`\* ([`apache::mod::nss`][]参照)
* `pagespeed` ([`apache::mod::pagespeed`][]参照)
* `passenger`\* ([`apache::mod::passenger`][]参照)
* `perl`
* `peruser`
* `php` ([`mpm_module`][]を`prefork`に設定する必要があります)
* `prefork`\*
* `proxy`\* ([`apache::mod::proxy`][]参照)
* `proxy_ajp`
* `proxy_balancer`\* ([`apache::mod::proxy_balancer`][]参照)
* `proxy_balancer`
* `proxy_html` ([`apache::mod::proxy_html`][]参照)
* `proxy_http`
* `python`
* `reqtimeout`
* `remoteip`\*
* `rewrite`
* `rpaf`\*
* `setenvif`
* `security`
* `shib`\* ([`apache::mod::shib`]参照)
* `speling`
* `ssl`\* ([`apache::mod::ssl`][]参照)
* `status`\* ([`apache::mod::status`][]参照)
* `suphp`
* `userdir`\* ([`apache::mod::userdir`][]参照)
* `version`
* `vhost_alias`
* `worker`\*
* `wsgi` ([`apache::mod::wsgi`][]参照)
* `xsendfile`

モジュールに付いている*のマークは、設定やモジュールを設定するためのパラメータが含まれるテンプレートがあることを示しています。ほとんどのApacheモジュールクラスパラメータにはデフォルト値があり、設定は必要ありません。 テンプレートのあるモジュールについては、Puppetでモジュールとともにテンプレートファイルがインストールされます。これらのテンプレートファイルは、モジュールが機能するために必要です。

##### クラス: `apache::mod::alias`

[`mod_alias`][]をインストールして管理します。

**パラメータ**:　

* `icons_options`: Apache [`Options`]ディレクティブにより、アイコンディレクトリのディレクトリリスティングを無効にします。

  デフォルト値: 'Indexes MultiViews'。

* `icons_path`: `/icons/`エイリアスのローカルパスを設定します。

  デフォルト値: オペレーティングシステムによって異なります。

    * **Debian**: `/usr/share/apache2/icons`
    * **FreeBSD**: `/usr/local/www/apache24/icons`
    * **Gentoo**: `/var/www/icons`
    * *Red Hat**: `/var/www/icons`、ただし、Apache 2.4は`/usr/share/httpd/icons`

#### クラス: `apache::mod::disk_cache`

Apache 2.2に[`mod_disk_cache`][]、またはApache 2.4に[`mod_cache_disk`][]をインストールして設定します。

デフォルト値: Apacheバージョンとオペレーティングシステムによって異なります。

- **Debian**: `/var/cache/apache2/mod_cache_disk`
- **FreeBSD**: `/var/cache/mod_cache_disk`
- **Red Hat、Apache 2.4**: `/var/cache/httpd/proxy`
- **Red Hat、Apache 2.2**: `/var/cache/mod_proxy`

キャッシュルートを指定するには、パスを文字列として`cache_root`パラメータに渡します。

``` puppet
class {'::apache::mod::disk_cache':
  cache_root => '/path/to/cache',
}
```

##### クラス: `apache::mod::diskio`

[`mod_diskio`][]をインストールして設定します。

```puppet
class{'apache':
  default_mods => `false`,
  log_level    => 'dumpio:trace7',
}
class{'apache::mod::diskio':
  disk_io_input  => 'On',
  disk_io_output => 'Off',
}
```

**パラメータ**:　

* `dump_io_input`: すべての入力データをエラーログにダンプします。

  値: 'On'、'Off'。　

  デフォルト値: 'Off'。

* `dump_io_output`: すべての出力データをエラーログにダンプします。

  値: 'On'、'Off'。　

  デフォルト値: 'Off'。

##### クラス: `apache::mod::event`

[`mod_mpm_event`][]をインストールして管理します。同じサーバ上に、`apache::mod::event`と一緒に[`apache::mod::itk`][]、[`apache::mod::peruser`][]、[`apache::mod::prefork`][]、[`apache::mod::worker`][]を含めることはできません。

**パラメータ**:　

* `listenbacklog`: モジュールの[`ListenBackLog`][]ディレクティブでペンディング接続キューの最大長を設定します。`false`に設定すると、このパラメータが削除されます。

  デフォルト値: '511'。

* `maxrequestworkers` (_Apache 2.3.12以前_: `maxclients`): モジュールの[`MaxRequestWorkers`][]ディレクティブで、Apacheが同時に処理できる接続の最大数を設定します。`false`に設定すると、このパラメータが削除されます。

  デフォルト値: '150'。

* `maxconnectionsperchild` (_Apache 2.3.8以前_: `maxrequestsperchild`): モジュールの[`MaxConnectionsPerChild`][]ディレクティブで、子サーバが稼働中に処理する接続の数を制限します。`false`に設定すると、このパラメータが削除されます。 

  デフォルト値: '0'。

* `maxsparethreads` and `minsparethreads`: [`MaxSpareThreads`][]および[`MinSpareThreads`][]ディレクティブで、待機スレッドの最大数と最小数を設定します。`false`に設定すると、このパラメータが削除されます。

  デフォルト値: それぞれ'75'および'25'。

* `serverlimit`: [`ServerLimit`][]ディレクティブで、プロセスの設定数を制限します。`false`に設定すると、このパラメータが削除されます。

  デフォルト値: '25'。

* `startservers`: モジュールの[`StartServers`][]ディレクティブで、起動時に作成される子サーバプロセスの数を設定します。`false`に設定すると、このパラメータが削除されます。

  デフォルト値: '2'。

* `threadlimit`: モジュールの[`ThreadLimit`][]ディレクティブで、イベントスレッドの数を制限します。`false`に設定すると、このパラメータが削除されます。 

  デフォルト値: '64'。

* `threadsperchild`: [`ThreadsPerChild`][]ディレクティブで、各子サーバにより作成されるスレッドの数を設定します。

  デフォルト値: '25'。`false`に設定すると、このパラメータが削除されます。

##### クラス: `apache::mod::auth_cas`

[`mod_auth_cas`][]をインストールして管理します。パラメータの名前はApacheモジュールのディレクティブと共通です。

`cas_login_url`および`cas_validate_url`パラメータは必須です。 その他のいくつかのパラメータのデフォルト値は`undef`です。

> **注意**: auth_casモジュールは、EPELにより提供される依存関係パッケージがなければ、RH/CentOSで使用できません。 [https://github.com/Jasig/mod_auth_cas]()を参照してください。

**パラメータ**:　

- `cas_attribute_prefix`: ヘッダを追加します。SAMLバリデーションが有効になっている場合には、このヘッダの値が属性値になります。

  デフォルト値: CAS_。

- `cas_attribute_delimiter`:`cas_attribute_prefix`により作成されたヘッダの属性値の区切り文字。

  デフォルト値: ,。

- `cas_authoritative`: オプションの認証ディレクティブを承認してバインドするかどうかを決定します。

  デフォルト値: `undef`。

- `cas_certificate_path`: `cas_login_url`および`cas_validate_url`のサーバについて、証明書認証局のX509証明書へのパスを設定します。

  デフォルト値: `undef`。

- `cas_cache_clean_interval`: キャッシュクリーニング時間の最小秒数を設定します。

  デフォルト値: `undef`。

- `cas_cookie_domain`: `Set-Cookie` HTTPヘッダの`Domain=`パラメータの値を設定します。 

  デフォルト値: `undef`。

- `cas_cookie_entropy`: セッション識別子を作成する際に使用するバイト数を設定します。

  デフォルト値: `undef`。

- `cas_cookie_http_only`: `mod_auth_cas`がクッキーを発行する際のオプションの`HttpOnly`フラグを設定します。

  デフォルト値: `undef`。

- `cas_cookie_path`: casクッキーセッションデータの保存場所。Webサーバユーザによる書き込みを可能にする必要があります。

  デフォルト値: OSによって異なります。

- `cas_cookie_path_mode`: `cas_cookie_path`のモード。

  デフォルト値: '0750'。

- `cas_debug`: モジュールのデバッギングモードを有効にするかどうかを決定します。

  デフォルト値: 'Off'。

- `cas_idle_timeout`: 待機タイムアウトの制限を秒数で設定します。

  デフォルト値: `undef`。

- `cas_login_url`: **必須**。ユーザがCASで保護されたリソースへのアクセスを試み、かつアクティブなセッションがない場合に、モジュールがユーザをリダイレクトする先のURLを設定します。

- `cas_proxy_validate_url`: プロキシバリデーションを実施する際に使用するURL。

  デフォルト値: `undef`。

- `cas_root_proxied_as`: このApacheサーバへのアクセスがプロキシされた場合に、エンドユーザに表示されるURLを設定します。

  デフォルト値: `undef`。

- `cas_scrub_request_headers`: mod_auth_cas内で特別な意味を持つ可能性のあるインバウンドリクエストヘッダを削除します。

- `cas_sso_enabled`: シングルサインアウトの実験的サポートを有効にします(POSTデータが壊れる可能性があります)。

  デフォルト値: 'Off'。

- `cas_timeout`: `mod_auth_cas`セッションのアクティブ状態を維持する時間(秒数)を制限します。

  デフォルト値: `undef`。

- `cas_validate_depth`: チェーンされた証明書バリデーションの深さを制限します。

  デフォルト値: `undef`。

- `cas_validate_saml`: SAMLに関するCASサーバからの解析応答。

  デフォルト値: 'Off'。

- `cas_validate_server`: CASサーバの証明書をバリデーションするかどうか(1.1 - RedHat 7では廃止予定)。

  デフォルト値: `undef`。

- `cas_validate_url`: **必須**。HTTPクエリ文字列でクライアントの提示するチケットをバリデーションする際に使用するURL。

- `cas_version`: 従うべきCASプロトコルバージョン。値: '1'、'2'。

  デフォルト値: '2'。

- `suppress_warning`: RedHat上にいることを示す警告を表示しないようにします(`mod_auth_cas`パッケージは、現在はepel-testingレポジトリで使用できます)。

  デフォルト値: `false`。

##### クラス: `apache::mod::auth_mellon`

[`mod_auth_mellon`][]をインストールして管理します。パラメータの名前はApacheモジュールのディレクティブと共通です。

``` puppet
class{ 'apache::mod::auth_mellon':
  mellon_cache_size => 101,
}
```

**パラメータ**:　

* `mellon_cache_entry_size`: 1回のセッションの最大サイズ。

  デフォルト値: `undef`。

* `mellon_cache_size`: mellonキャッシュのサイズ、単位はメガバイト。

  デフォルト値: 100。

* `mellon_lock_file`: ロックファイルの場所。

  デフォルト値: '`/run/mod_auth_mellon/lock`'。

* `mellon_post_directory`: ポストリクエストが保存される場所のフルパス。

  デフォルト値: '`/var/cache/apache2/mod_auth_mellon/`'。

* `mellon_post_ttl`: ポストリクエストの維持時間。

  デフォルト値: `undef`。

* `mellon_post_size`: ポストリクエストの最大サイズ。

  デフォルト値: `undef`。

* `mellon_post_count`: ポストリクエストの最大数。

 デフォルト値: `undef`。

##### クラス: `apache::mod::authn_dbd`

`mod_authn_dbd`をインストールし、`authn_dbd.conf.erb`テンプレートを使用して設定を生成します。オプションで、AuthnProviderAliasを作成します。

``` puppet
class { 'apache::mod::authn_dbd':
  $authn_dbd_params =>
    'host=db01 port=3306 user=apache password=xxxxxx dbname=apacheauth',
  $authn_dbd_query  => 'SELECT password FROM authn WHERE user = %s',
  $authn_dbd_alias  => 'db_auth',
}
```

**パラメータ**:　

* `authn_dbd_alias`: AuthnProviderAlias'の名前。

* `authn_dbd_dbdriver`: 使用するデータベースドライブを指定します。

  デフォルト値: 'mysql'。

* `authn_dbd_exptime`: DBDExptimeに相当します。

  デフォルト値: 300。

* `authn_dbd_keep`: DBDKeepに相当します。

  デフォルト値: 8。

* `authn_dbd_max`: DBDMaxに相当します。

  デフォルト値: 20。

* `authn_dbd_min`: DBDMinに相当します。

  デフォルト値: 4。　

* `authn_dbd_params`: **必須**。接続文字列に関して、DBDParamsに相当します。

* `authn_dbd_query`: 認証に関してユーザとパスワードを問い合わせるかどうか。

##### クラス: `apache::mod::authnz_ldap`

`mod_authnz_ldap`をインストールし、`authnz_ldap.conf.erb`テンプレートを使用して設定を生成します。

**パラメータ**:　

* `package_name`: パッケージの名前。

  デフォルト値: `undef`。

* `verify_server_cert`: サーバの証明書を確認するかどうか。

  デフォルト値: `undef`。

##### クラス: `apache::mod::cluster`

**注意**: `mod_cluster`に関して提供されている公式なパッケージはありません。そのため、Apacheモジュールの外部から使用できるようにする必要があります。バイナリはhttp://mod-cluster.jboss.org/にあります。

``` puppet
class { '::apache::mod::cluster':
  ip                      => '172.17.0.1',
  allowed_network         => '172.17.0.',
  balancer_name           => 'mycluster',
  version                 => '1.3.1'
}
```

**パラメータ**:　

* `port`: mod_clusterのリッスンポート。

  デフォルト値: '6666'。

* `server_advertise`: サーバをアドバタイズするかどうか。

  デフォルト値: `true`。

* `advertise_frequency`: アドバタイズメッセージ間のインターバルを秒数[.ミリ秒]で設定します。

  デフォルト値: 10。

* `manager_allowed_network`: ネットワークにmod_cluster_managerへのアクセスを許可するかどうか。

  デフォルト値: '127.0.0.1'。

* `keep_alive_timeout`: Apacheがリクエストを待機する長さを秒数で指定します。

  デフォルト値: 60。

* `max_keep_alive_requests`: 維持されるリクエストの最大数。

  デフォルト値: 0。

* `enable_mcpm_receive`: MCPMを有効にするかどうか。 

  デフォルト値: `true`。

* `ip`: リッスンするIPアドレスを指定します。

* `allowed_network`: バランスドメンバーネットワーク。

* `version`: `mod_cluster`バージョンを指定します。httpd 2.4ではバージョン1.3.0以上が必要です。

##### クラス: `apache::mod::deflate`

[`mod_deflate`][]をインストールして設定します。

**パラメータ**:　

* `types`: デフレートする[MIMEタイプ][MIME `content*type`]の[配列][]。 

  デフォルト値: [ 'text/html text/plain text/xml'、'text/css'、'application/x*javascript application/javascript application/ecmascript'、'application/rss+xml'、'application/json' ]。

* `notes`: [ハッシュ][]、キーはタイプを表し、値はノート名を表します。

  デフォルト値: { 'Input'  => 'instream'、'Output' => 'outstream'、'Ratio'  => 'ratio' }。

##### クラス: `apache::mod::expires`

[`mod_expires`][]をインストールし、`expires.conf.erb`を使用して設定を生成します。

**パラメータ**:　

* `expires_active`: ドキュメント領域に関して`Expires`ヘッダの生成を有効にします。

  ブーリアン。

  デフォルト値: `true`。

* `expires_default`: [`ExpiresByType`][]構文または[インターバル構文][]を用いた有効期限計算のためのデフォルトアルゴリズムを指定します。

  デフォルト値: `undef`。

* `expires_by_type`: [MIME `content*type`][]とその有効時間のセットを記述します。

  値: [ハッシュ][ハッシュ]の[配列][]、各ハッシュのキーは有効なMIME `content*type` ('text/json'など)、値は以下の有効な [インターバル構文][]。

  デフォルト値: `undef`。

##### クラス: `apache::mod::ext_filter`

[`mod_ext_filter`][]をインストールして設定します。

``` puppet
class { 'apache::mod::ext_filter':
  ext_filter_define => {
    'slowdown'       => 'mode=output cmd=/bin/cat preservescontentlength',
    'puppetdb-strip' => 'mode=output outtype=application/json cmd="pdb-resource-filter"',
  },
}
```

**パラメータ**:　

* `ext_filter_define`: フィルタ名とそのパラメータのハッシュ。

  デフォルト値: `undef`。

##### クラス: `apache::mod::fcgid`

[`mod_fcgid`][]をインストールして設定します。

このクラスでは、使用可能なすべてのオプションを個別にパラメータ化するのではなく、`options` [ハッシュ][]を使って`mod_fcgid`を設定します。例:

``` puppet
class { 'apache::mod::fcgid':
  options => {
    'FcgidIPCDir'  => '/var/run/fcgidsock',
    'SharememPath' => '/var/run/fcgid_shm',
    'AddHandler'   => 'fcgid-script .fcgi',
  },
}
```

すべてのオプションのリストについては、[公式`mod_fcgid`ドキュメント][`mod_fcgid`]を参照してください。

`apache::mod::fcgid`を含める場合は、ディレクトリごと、バーチャルホストごとに[`FcgidWrapper`][]を設定できます。最初にモジュールをロードする必要があります。`apache::vhost`で`fcgiwrapper`パラメータを設定している場合、Puppetは自動的にはモジュールを有効化しません。

``` puppet
include apache::mod::fcgid

apache::vhost { 'example.org':
  docroot     => '/var/www/html',
  directories => {
    path        => '/var/www/html',
    fcgiwrapper => {
      command => '/usr/local/bin/fcgiwrapper',
    }
  },
}
```

##### クラス: `apache::mod::geoip`

[`mod_geoip`][]をインストールして管理します。

**パラメータ**:　

* `db_file`: GeoIPデータベースファイルのパスを設定します。

  値: パス、または複数のGeoIPデータベースファイルの[配列][]パス。

  デフォルト値: `/usr/share/GeoIP/GeoIP.dat`。

* `enable`: [`mod_geoip`][]を全体で有効にするかどうかを決定します。

  ブーリアン。

  デフォルト値: `false`。

* `flag`: GeoIPフラグを設定します。

  値: 'CheckCache'、'IndexCache'、'MemoryCache'、'Standard'。

  デフォルト値: 'Standard'。

* `output`: 使用するアウトプット変数を定義します。

  値: 'All'、'Env'、'Request'、'Notes'。

  デフォルト値: 'All'。

* `enable_utf8`: アウトプットをISO*8859*1 (ラテン*1)からUTF*8に変更します。

  ブーリアン。

  デフォルト値: `undef`。

* `scan_proxy_headers`: [GeoIPScanProxyHeaders][]オプションを有効にします。

  ブーリアン。

  デフォルト値: `undef`。

* `scan_proxy_header_field`: クライアントのIPアドレスの決定に使用するヘッダの[`mod_geoip`][]を指定します。

  デフォルト値: `undef`。

* `use_last_xforwarededfor_ip` (sic): IPアドレスのカンマ区切りリストで見つかったクライアントのIPの最初または最後のIPアドレスを使うかどうかを決定します。

  ブーリアン。

  デフォルト値: `undef`。

##### クラス: `apache::mod::info`

サーバ設定の全体的な概要を提供する[`mod_info`][]をインストールして管理します。

**パラメータ**:　

* `allow_from`: IPv4またはIPv6アドレスのホワイトリスト、または`/server*info`にアクセスできる範囲。

  値: IPv4アドレス、IPv6アドレス、または範囲の1つまたは複数のオクテット、またはいずれかの配列。

  デフォルト値: ['127.0.0.1','::1']。　

* `apache_version`: 文字列で表されるApacheのバージョン番号、'2.2'や'2.4'など。　

  デフォルト値: [`$::apache::apache_version`][`apache_version`]の値。


* `restrict_access`: アクセス制限を有効にするかどうかを決定します。`false`の場合、`allow_from`ホワイトリストは無視され、すべてのIPアドレスが `/server*info`にアクセスできるようになります。

  ブーリアン。

  デフォルト値: `true`。

##### クラス: `apache::mod::jk`

`mod_jk`をインストールして管理します。これは、Apache httpdリダイレクションと古いバージョンのTomCatおよびJBossを結ぶコネクタです。

**注意**: mod\_jkに関して提供されている公式のパッケージはありません。そのため、apacheモジュールの制御以外の手段で使用できるようにする必要があります。バイナリは[Apache Tomcatコネクタダウンロードページ](https://tomcat.apache.org/download-connectors.cgi)にあります。

``` puppet
class { '::apache::mod::jk':
  ip           = '192.168.2.15',
  workers_file = 'conf/workers.properties',
  mount_file   = 'conf/uriworkermap.properties',
  shm_file     = 'run/jk.shm',
  shm_size     = '50M',
  $workers_file_content = {
    <Content>
  },
}
```

**`apache::mod::jk`**内のパラメータ:

`mod_jk`パラメータを理解するための情報源としては、[公式ドキュメント](https://tomcat.apache.org/connectors-doc/reference/apache.html)が最適です。ただし、次はこれに含まれません:

**add_listen**

パラメータ`ip`および `port`に従って`Listen`ディレクティブを定義して(下記参照)、ApacheがIP/portの組合せをリッスンし`mod_jk`にリダイレクトするようにします。
`Listen *:<Port>`または`Listen <Port>`のように、別の`Listen`ディレクティブが`mod_jk`バインディングで必要なものと競合するときに役立ちます。

タイプ: ブール値
デフォルト: true

**ip**

`mod_jk`にバインディングするIP。
バインディングアドレスがプライマリのネットワークインターフェースIPではないときに役立ちます。

タイプ: 文字列
デフォルト: `$facts['ipaddress']`

**port**

`mod_jk`にバインディングするポート。
リバースプロキシまたはキャッシュのような、別のものがポート80でリクエストを受信して、異なるポートのApacheに転送する必要があるときに役立ちます。

タイプ: 文字列(数値)
デフォルト: '80'

**workers\_file\_content**

各ディレクティブにはフォーマット`worker.<Worker name>.<Property>=<Value>`があります。このマップは複数ハッシュのハッシュとして表され、外側のハッシュはワーカーを指定し、内側の各ハッシュは各ワーカーのプロパティと値を指定します。
また、2つのグローバルディレクティブ 'worker.list'および'worker.mantain'もあります。  
例えば、ワーカーファイルは以下のようになります。

```
worker.list = status
worker.list = some_name,other_name

worker.mantain = 60

# Optional comment
worker.some_name.type=ajp13
worker.some_name.socket_keepalive=true

# I just like comments
worker.other_name.type=ajp12 (why would you?)
worker.other_name.socket_keepalive=false
```

以下のようにパラメータ化する必要があります。　

```
$workers_file_content = {
  worker_lists   => ['status', 'some_name,other_name'],
  worker_mantain => '60',
  some_name      => {
    comment          => 'Optional comment',
    type             => 'ajp13',
    socket_keepalive => 'true',
  },
  other_name     => {
    comment          => 'I just like comments',
    type             => 'ajp12',
    socket_keepalive => 'false',
  },
}
```

**mount\_file\_content**

各ディレクティブにはフォーマット`<URI> = <Worker name>`があります。このマップは複数ハッシュのハッシュとして表され、外側のハッシュはワーカーを指定し、内側の各ハッシュは次の2つのアイテムを含みます: uri_list - ワーカーにマップするURIを用いた配列 - およびコメント - ワーカーに関するコメントを記したオプションの文字列。 
例えば、マウントファイルは以下のようになります。

```
# Worker 1
/context_1/ = worker_1
/context_1/* = worker_1

# Worker 2
/ = worker_2
/context_2/ = worker_2
/context_2/* = worker_2
```

以下のようにパラメータ化する必要があります。　

```
$mount_file_content = {
  worker_1 => {
    uri_list => ['/context_1/', '/context_1/*'],
    comment  => 'Worker 1',
  },
  worker_2 => {
    uri_list => ['/context_2/', '/context_2/*'],
    comment  => 'Worker 2',
  },
},
```

**shm\_file and log\_file**

これらのファイルがどのように定義されているかによって、クラスはそれらの最終パスを別々に作成します。
- 相対パス: `logroot`で提供されたパスを追加します (下記参照)
- 絶対パスまたはパイプ: 提供されたパスをそのまま使用します

例 (RHEL 6):

```
shm_file => 'shm_file'
# Ends up in
$shm_path = '/var/log/httpd/shm_file'
```
```
shm_file => '/run/shm_file'
# Ends up in
$shm_path = '/run/shm_file'
```
```
shm_file => '"|rotatelogs /var/log/httpd/mod_jk.log.%Y%m%d 86400 -180"'
# Ends up in
$shm_path = '"|rotatelogs /var/log/httpd/mod_jk.log.%Y%m%d 86400 -180"'
```

> デフォルトのlogrootは十分健全です。このため、絶対パスを指定することは推奨しません。

**logroot**

`shm_file`および`log_file`のベースディレクトリは`logroot`パラメータで決定されます。指定されない場合、デフォルトは`apache::params::logroot`です。

> デフォルトのlogrootは十分健全です。このため、上書きすることは推奨しません。

##### クラス: `apache::mod::passenger`　

[`mod_passenger`][]をインストールして管理します。Red Hatベースのシステムの場合は、[passengerドキュメント](https://www.phusionpassenger.com/library/install/apache/install/oss/el6/#step-1:-upgrade-your-kernel,-or-disable-selinux)に記載された最小要件を満たしていることを確認してください。

**パラメータ**:　

* `passenger_high_performance`: [`PassengerHighPerformance`](https://www.phusionpassenger.com/library/config/apache/reference/#passengerhighperformance)を設定します。

  値: 'On'、'Off'。　

  デフォルト値: `undef`。

* `passenger_pool_idle_time`: [`PassengerPoolIdleTime`](https://www.phusionpassenger.com/library/config/apache/reference/#passengerpoolidletime)を設定します。

  デフォルト値: `undef`。

* `passenger_max_pool_size`: [`PassengerMaxPoolSize`](https://www.phusionpassenger.com/library/config/apache/reference/#passengermaxpoolsize)を設定します。

  デフォルト値: `undef`。

* `passenger_max_request_queue_size`: [`PassengerMaxRequestQueueSize`](https://www.phusionpassenger.com/library/config/apache/reference/#passengermaxrequestqueuesize)を設定します。

  デフォルト値: `undef`。

* `passenger_max_requests`: [`PassengerMaxRequests`](https://www.phusionpassenger.com/library/config/apache/reference/#passengermaxrequests)を設定します。

  デフォルト値: `undef`。

* `passenger_data_buffer_dir`: [`PassengerDataBufferDir`](https://www.phusionpassenger.com/library/config/apache/reference/#passengerdatabufferdir)を設定します。

  デフォルト値: `undef`。

##### クラス: `apache::mod::ldap`

[`mod_ldap`][]をインストールして設定し、[`LDAPTrustedGlobalCert`](https://httpd.apache.org/docs/current/mod/mod_ldap.html#ldaptrustedglobalcert)ディレクティブの修正を可能にします。

``` puppet
class { 'apache::mod::ldap':
  ldap_trusted_global_cert_file => '/etc/pki/tls/certs/ldap-trust.crt',
  ldap_trusted_global_cert_type => 'CA_DER',
  ldap_shared_cache_size        => '500000',
  ldap_cache_entries            => '1024',
  ldap_cache_ttl                => '600',
  ldap_opcache_entries          => '1024',
  ldap_opcache_ttl              => '600',
}
```

**パラメータ**　

* `apache_version`: インストールされたApacheバージョンを指定します。

  デフォルト値: `undef`。

* `ldap_trusted_global_cert_file`: LDAPサーバ上でSSLまたはTLS接続を確立する際に使用する、信頼できるCA証明書のパスとファイル名を指定します。

* `ldap_trusted_global_cert_type`:グローバルな信頼できる証明書フォーマットを指定します。

  デフォルト値: 'CA_BASE64'。

* `ldap_shared_cache_size`: 共有されたメモリのキャッシュのサイズをバイトで指定します。

* `ldap_cache_entries`: 一次LDAPキャッシュのエントリの最大数を指定します。

* `ldap_cache_ttl`: キャッシュされたアイテムが有効に保たれる時間を秒数で指定します。

* `ldap_opcache_entries`: LDAP比較演算のキャッシュに用いるエントリ数を指定します。

* `ldap_opcache_ttl`: 演算キャッシュのエントリが有効に保たれる時間を秒数で指定します。

* `package_name`: カスタムパッケージ名を指定します。

  デフォルト値: `undef`。

##### クラス: `apache::mod::negotiation`

[`mod_negotiation`][]をインストールして設定します。

**パラメータ**:　

* `force_language_priority`: `ForceLanguagePriority`オプションを設定します。

  値: 文字列。　

  デフォルト値: `Prefer Fallback`。

* `language_priority`: モジュールの`LanguagePriority`オプションを設定するための言語の[配列][]。

  デフォルト値: [ 'en'、'ca'、'cs'、'da'、'de'、'el'、'eo'、'es'、'et'、'fr'、'he'、'hr'、'it'、'ja'、'ko'、'ltz'、'nl'、'nn'、'no'、'pl'、'pt'、'pt*BR'、'ru'、'sv'、'zh*CN'、'zh*TW' ]。

##### クラス: `apache::mod::nss`

NSS暗号化ライブラリを使用するApacheのSSLプロバイダ。

**パラメータ:**

- `transfer_log`: access.logのパス。
- `error_log`: error.logのパス。
- `passwd_file`: NSSPassPhraseDialogディレクティブに使用するファイルのパス。
- `port`: SSLポート。デフォルト値8443。

##### クラス: `apache::mod::pagespeed`

[`mod_pagespeed`][]をインストールして管理します。これは、Webページをリライトして冗長性と帯域を軽減するためのGoogleモジュールです。

このapacheモジュールには`mod-pagespeed-stable`が必要ですが、Puppetはパッケージを自動的にインストールするために必要なソフトウェアを管理**しません**。パッケージがインストールされていないか、お使いのパッケージマネージャで使用できない場合にこのクラスを宣言すると、Puppet実行は失敗します。

> **注意:** お使いのシステムが最新のGoogle Pagespeed要件を満たしていることを確認してください。

**パラメータ**:　

以下のパラメータはモジュールのディレクティブに相当します。詳細については、[モジュールのドキュメント][`mod_pagespeed`]を参照してください。

* `inherit_vhost_config`: デフォルト値: 'on'。
* `filter_xhtml`: デフォルト値: `false`。
* `cache_path`: デフォルト値: '/var/cache/mod_pagespeed/'。
* `log_dir`: デフォルト値: '/var/log/pagespeed'。
* `memcache_servers`: デフォルト値: []。
* `rewrite_level`: デフォルト値: 'CoreFilters'。
* `disable_filters`: デフォルト値: []。
* `enable_filters`: デフォルト値: []。
* `forbid_filters`: デフォルト値: []。
* `rewrite_deadline_per_flush_ms`: デフォルト値: 10。
* `additional_domains`: デフォルト値: `undef`。
* `file_cache_size_kb`: デフォルト値: 102400。
* `file_cache_clean_interval_ms`: デフォルト値: 3600000。
* `lru_cache_per_process`: デフォルト値: 1024。
* `lru_cache_byte_limit`: デフォルト値: 16384。
* `css_flatten_max_bytes`: デフォルト値: 2048。
* `css_inline_max_bytes`: デフォルト値: 2048。
* `css_image_inline_max_bytes`: デフォルト値: 2048。
* `image_inline_max_bytes`: デフォルト値: 2048。
* `js_inline_max_bytes`: デフォルト値: 2048。
* `css_outline_min_bytes`: デフォルト値: 3000。
* `js_outline_min_bytes`: デフォルト値: 3000。
* `inode_limit`: デフォルト値: 500000。
* `image_max_rewrites_at_once`: デフォルト値: 8。
* `num_rewrite_threads`: デフォルト値: 4。
* `num_expensive_rewrite_threads`: デフォルト値: 4。
* `collect_statistics`: デフォルト値: 'on'。
* `statistics_logging`: デフォルト値: 'on'。
* `allow_view_stats`: デフォルト値: []。
* `allow_pagespeed_console`: デフォルト値: []。
* `allow_pagespeed_message`: デフォルト値: []。
* `message_buffer_size`: デフォルト値: 100000。
* `additional_configuration`: ディレクティブ値ペアのハッシュ、またはpagespeed設定の最後に挿入する行の配列。デフォルト値: '{ }'。

##### クラス: `apache::mod::passenger`　

`mod_passenger`をインストールして設定します。

>**注意**: passengerモジュールは、EPELにより提供される依存関係パッケージと`mod_passengers`カスタムリポジトリがなければ、RH/CentOSでは使用できません。前述の`manage_repo`パラメータと[https://www.phusionpassenger.com/library/install/apache/install/oss/el7/]()を参照してください。

**パラメータ**:　

* `passenger_conf_file`: `$::apache::params::passenger_conf_file`
* `passenger_conf_package_file: `$::apache::params::passenger_conf_package_file`
* `passenger_high_performance`: デフォルト値: `undef`
* `passenger_pool_idle_time`: デフォルト値: `undef`
* `passenger_max_request_queue_size`: デフォルト値: `undef`
* `passenger_max_requests`: デフォルト値: `undef`
* `passenger_spawn_method`: デフォルト値: `undef`
* `passenger_stat_throttle_rate`: デフォルト値: `undef`
* `rack_autodetect`: デフォルト値: `undef`
* `rails_autodetect`: デフォルト値: `undef`
* `passenger_root` : `$::apache::params::passenger_root`
* `passenger_ruby` : `$::apache::params::passenger_ruby`
* `passenger_default_ruby`: `$::apache::params::passenger_default_ruby`
* `passenger_max_pool_size`: デフォルト値: `undef`
* `passenger_min_instances`: デフォルト値: `undef`
* `passenger_max_instances_per_app`: デフォルト値: `undef`
* `passenger_use_global_queue`: デフォルト値: `undef`
* `passenger_app_env`: デフォルト値: `undef`
* `passenger_log_file`: デフォルト値: `undef`
* `passenger_log_level`: デフォルト値: `undef`
* `passenger_data_buffer_dir`: デフォルト値: `undef`
* `manage_repo`: phusionpassenger.comリポジトリを管理するかどうか。デフォルト値: `true`
* `mod_package`: デフォルト値: `undef`
* `mod_package_ensure`: デフォルト値: `undef`
* `mod_lib`: デフォルト値: `undef`
* `mod_lib_path`: デフォルト値: `undef`
* `mod_id`: デフォルト値: `undef`
* `mod_path`: デフォルト値: `undef`

##### クラス: `apache::mod::proxy`

I`mod_proxy`をインストールし、`proxy.conf.erb`テンプレートを使用して設定を生成します。

**`apache::mod::proxy`内のパラメータ**:

- `allow_from`: デフォルト値: `undef`
- `apache_version`: デフォルト値: `undef`
- `package_name`: デフォルト値: `undef`
- `proxy_requests`: デフォルト値: 'Off'
- `proxy_via`: デフォルト値: 'On'

##### クラス: `apache::mod::proxy_balancer`

ロードバランシングを提供する[`mod_proxy_balancer`][]をインストールして管理します。

**パラメータ**:　

* `manager`: バランサマネージャのサポートを有効にするかどうかを決定します。

  デフォルト値: `false`。

* `manager_path`: バランサマネージャのサーバロケーション。

  デフォルト値: '/balancer*manager'。

* `allow_from`: `/balancer*manager`にアクセスできるIPv4またはIPv6アドレスの[配列][]。

  デフォルト値: ['127.0.0.1','::1']。　

* `apache_version`: 文字列で表されるApacheのバージョン番号、'2.2'や'2.4'など。　

  デフォルト値: [`$::apache::apache_version`][`apache_version`]の値。Apache 2.4以上では、`mod_slotmem_shm`がロードされます。

##### クラス: `apache::mod::php`

[`mod_php`][]をインストールして設定します。

**パラメータ**:　

以下のパラメータのデフォルト値は、オペレーティングシステムによって異なります。このクラスのパラメータのほとんどは、`mod_php`ディレクティブに相当します。詳細については、[モジュールのドキュメント][`mod_php`]を参照してください。

* `package_name`: `mod_php`をインストールするパッケージの名前。
* `path`: `mod_php`共有オブジェクト(`.so`)ファイルのパスを定義します。
* `source`: デフォルト設定のパスを定義します。値には`puppet:///`パスが含まれます。
* `template`: Puppetが設定ファイルの生成に使用する`php.conf`テンプレートのパスを定義します。
* `content`: `php.conf`に任意のコンテンツを追加します。

##### クラス: `apache::mod::proxy_html`

**注意**: `mod_proxy_html`に関して提供されている公式なパッケージはありません。そのため、apacheモジュールの外部から使用できるようにする必要があります。

##### クラス: `apache::mod::reqtimeout`

[`mod_reqtimeout`][]をインストールして設定します。

**パラメータ**　

* `timeouts`: [`RequestReadTimeout`][]オプションを設定します。

  値:  文字列または[配列][]。

  デフォルト値: ['header=20-40,MinRate=500', 'body=20,MinRate=500']。

##### クラス: `apache::mod::rewrite`

Apacheモジュール`mod_rewrite`をインストールして有効にします。

##### クラス: `apache::mod::shib`

[Shibboleth](http://shibboleth.net/) Apacheモジュール`mod_shib`をインストールします。このモジュールは、Shibboleth認証プロバイダおよびShibboleth FederationsによるSAML2シングルサインオン(SSO)認証を有効にするものです。このクラスを定義すると、`apache::vhost`インスタンス内でShibboleth固有のパラメータが有効になります。

このクラスでインストールおよび設定されるのは、Shibboleth SSO認証をコンシュームするWebアプリケーションのApacheコンポーネントのみです。PuppetでShibboleth設定を手動で管理することも、[Shibboleth Puppetモジュール](https://github.com/aethylred/puppet-shibboleth)を使用することもできます。

**注意**: shibbolethモジュールは、Shibbolethのリポジトリにより提供される依存関係パッケージがなければ、RH/CentOSでは使用できません。[http://wiki.aaf.edu.au/tech-info/sp-install-guide]()を参照してください。

##### クラス: `apache::mod::ssl`

[Apache SSL機能][`mod_ssl`]をインストールし、`ssl.conf.erb`テンプレートを使用して設定を生成します。ほとんどのオペレーティングシステムでは、この`ssl.conf`はモジュール設定ディレクトリに置かれています。Red Hatベースのオペレーティングシステムでは、このファイルは`/etc/httpd/conf.d`にあります。これは、RPMが設定を保存するのと同じロケーションです。

バーチャルホストでSSLを使用するには、`::apache`の[`default_ssl_vhost`][]パラメータを`true`に設定する**か**、[`apache::vhost`][]の[`ssl`][]パラメータを`true`に設定する必要があります。

- `ssl_cipher`: デフォルト値: 'HIGH:MEDIUM:!aNULL:!MD5:!RC4'
- `ssl_compression`: デフォルト値: false
- `ssl_cryptodevice`: デフォルト値: 'builtin'
- `ssl_honorcipherorder`: デフォルト値: true
- `ssl_openssl_conf_cmd`: デフォルト値: undef
- `ssl_options`: デフォルト値: [ 'StdEnvVars' ]
- `ssl_pass_phrase_dialog`: デフォルト値: 'builtin'
- `ssl_protocol`: デフォルト値: [ 'all', '-SSLv2', '-SSLv3' ]
- `ssl_proxy_protocol`: デフォルト値: []
- `ssl_random_seed_bytes`: 有効なオプション: 文字列、デフォルト値: '512'
- `ssl_sessioncache`: 有効なオプション: 文字列。デフォルト値: '300'
- `ssl_sessioncachetimeout`: 有効なオプション: 文字列。デフォルト値: '300'
- `ssl_mutex`: デフォルト値: OSによって異なります。有効なオプション: [mod_ssl][mod_ssl]ドキュメントを参照
  - RedHat/FreeBSD/Suse/Gentoo: 'default'
  - Debian/Ubuntu + Apache >= 2.4: 'default'
  - Debian/Ubuntu + Apache < 2.4: 'file:\${APACHE_RUN_DIR}/ssl_mutex'
**パラメータ:

* `ssl_cipher`

  デフォルト値: 'HIGH:MEDIUM:!aNULL:!MD5:!RC4'

* `ssl_compression`

  デフォルト値: `false`。

* `ssl_cryptodevice`

  デフォルト値: 'builtin'　

* `ssl_honorcipherorder`

  デフォルト値: `true`。

* `ssl_openssl_conf_cmd`

  デフォルト値: `undef`。

* `ssl_options`

  デフォルト値: [ 'StdEnvVars' ]

* `ssl_pass_phrase_dialog`

  デフォルト値: 'builtin'　

* `ssl_protocol`

  デフォルト値: [ 'all', '*SSLv2', '*SSLv3' ]

* `ssl_random_seed_bytes`

  値: 文字列。　

  デフォルト値: '512'

* `ssl_sessioncachetimeout`

  値: 文字列。　

  デフォルト値: '300'

* `ssl_mutex`:

  値: [mod_ssl][mod_ssl]ドキュメントを参照。

  デフォルト値: OSによって異なります:

  * RedHat/FreeBSD/Suse/Gentoo: 'default'.
  * Debian/Ubuntu + Apache >= 2.4: 'default'.
  * Debian/Ubuntu + Apache < 2.4: 'file:\${APACHE_RUN_DIR}/ssl_mutex'.
  * Ubuntu 10.04: 'file:/var/run/apache2/ssl_mutex'.


##### クラス: `apache::mod::status`

[`mod_status`][]をインストールし、`status.conf.erb`テンプレートを使用して設定を生成します。

**パラメータ**:　

* `allow_from`: `/server-status`にアクセスできるIPv4またはIPv6アドレスの[配列][]。

  デフォルト値: ['127.0.0.1','::1']。　
* `extended_status`: [`ExtendedStatus`][]ディレクティブをつうじて、各リクエストに関する拡張ステータス情報を追跡するかどうかを決定します。

  値: 'Off'、'On'。

  デフォルト値: 'On'。

* `status_path`: ステータスページのサーバロケーション。

  デフォルト値: '/server-status'。

##### クラス: `apache::mod::userdir`

`http://example.com/~user/`構文を用いて、ユーザ指定のディレクトリにアクセスできるようにします。すべてのパラメータは、[公式のApacheドキュメント](https://httpd.apache.org/docs/2.4/mod/mod_userdir.html)で見られます。

**パラメータ**:　

* `overrides`: ディレクティブタイプの[配列][]。

  デフォルト値: '[ 'FileInfo', 'AuthConfig', 'Limit', 'Indexes' ]'。

##### クラス: `apache::mod::version`

多くのオペレーティングシステムおよびApache構成上で[`mod_version`][]をインストールします。

Apache 2.4を使用するDebianおよびUbuntuが`apache::mod::version`で分類された場合は、`mod_version`がビルトインされているためロードできない旨の警告をPuppetが表示します。

##### クラス: `apache::mod::security`

Trustwaveの[`mod_security`][]をインストールして設定します。これはすべてのバーチャルホストでデフォルトで有効化され、実行されます。

**パラメータ**:　

* `activated_rules`: `modsec_crs_path`のルールの[配列][]またはsymlinkを使用してアクティベートする絶対値。 
* `allowed_methods`: 許可されるHTTPメソッドのスペース*区切りリスト。

  デフォルト値: 'GET HEAD POST OPTIONS'。

* `content_types`: 1つまたは複数の許可される[MIMEタイプ][MIME `content*type`]のリスト。

  デフォルト値: 'application/x*www*form*urlencoded|multipart/form*data|text/xml|application/xml|application/x*amf'。

* `crs_package`: CRSルールをインストールするパッケージの名前。

  デフォルト値: [`apache::params`][]内の`modsec_crs_package`。

* `manage_security_crs`: security_crs.confルールファイルを管理します。

  デフォルト値: `true`。

* `modsec_dir`: Puppetがmodsec設定およびアクティベートされたルールリンクをインストールする場所のパスを定義します。 

  デフォルト値: 'On'、[`apache::params`][]の`modsec_dir`により設定。
${modsec\_dir}/activated\_rules。

* `modsec_secruleengine`: modsecルールエンジンを設定します。値: 'On'、'Off'、'DetectionOnly'。

  デフォルト値: [`apache::params`][]の`modsec_secruleengine`。

* `restricted_extensions`: 禁止されるファイル拡張子のスペース*区切りリスト。

  デフォルト値: '.asa/ .asax/ .ascx/ .axd/ .backup/ .bak/ .bat/ .cdx/ .cer/ .cfg/ .cmd/ .com/ .config/ .conf/ .cs/ .csproj/ .csr/ .dat/ .db/ .dbf/ .dll/ .dos/ .htr/ .htw/ .ida/ .idc/ .idq/ .inc/ .ini/ .key/ .licx/ .lnk/ .log/ .mdb/ .old/ .pass/ .pdb/ .pol/ .printer/ .pwd/ .resources/ .resx/ .sql/ .sys/ .vb/ .vbs/ .vbproj/ .vsdisco/ .webinfo/ .xsd/ .xsx/'。

* `restricted_headers`: 禁止されるヘッダのスラッシュおよびスペースで区切ったリスト。

  デフォルト値: 'Proxy*Connection/ /Lock*Token/ /Content*Range/ /Translate/ /via/ /if/'。

* `secdefaultaction`: OWASP ModSecurityコアルールセットに関して、動作モード、自己完結('deny')、コラボレーティブ検出('pass')を設定します。

  デフォルト値: 'deny'。"log,auditlog,deny,status:406,tag:'SLA 24/7'"などの完全な値を設定することもできます。

* `secpcrematchlimit`: PCREライブラリのマッチ限度数を設定します。

  デフォルト値: 1500。　

* `secpcrematchlimitrecursion`: PCREライブラリのマッチ再帰制限数を設定します。

  デフォルト値: 1500。　

* `logroot`: オーディットおよびデバッグログの場所を設定します。

  デフォルト値はApacheのログディレクトリ(Redhat: `/var/log/httpd`、Debian: `/var/log/apache2`)。

* `audit_log_releavant_status`: オーディットロギングの目的に関して、考慮すべき応答ステータスコードを設定します。

  デフォルト値: '^(?:5|4(?!04))'。

* `audit_log_parts`: [オーディットログ][]に入れるべきセクションを設定します。

  デフォルト値: 'ABIJDEFHZ'。

* `anomaly_score_blocking`: OWASP ModSecurityコアルールセットのコラボレーティブ検出ブロッキングをアクティベートまたはディアクティベートします。

  デフォルト値: 'off'。

* `inbound_anomaly_threshold`: OWASP ModSecurityコアルールセットのコラボレーティブ検出モードに関して、インバウンドブロッキングルールのスコアリング閾値レベルを設定します。

  デフォルト値: 5。　

* `outbound_anomaly_threshold`: OWASP ModSecurityコアルールセットのコラボレーティブ検出モードに関して、アウトバウンドブロッキングルールのスコアリング閾値レベルを設定します。

  デフォルト値: 4。　

* `critical_anomaly_score`: OWASP ModSecurityコアルールセットのコラボレーティブ検出モードに関して、重要なセキュリティレベルのスコアリングポイントを設定します。

  デフォルト値: 5。　

* `error_anomaly_score`: OWASP ModSecurityコアルールセットのコラボレーティブ検出モードに関して、エラー深刻度レベルのスコアリングポイントを設定します。

  デフォルト値: 4。　

* `warning_anomaly_score`: OWASP ModSecurityコアルールセットのコラボレーティブ検出モードに関して、警告深刻度レベルのスコアリングポイントを設定します。

  デフォルト値: 3。

* `notice_anomaly_score`: OWASP ModSecurityコアルールセットのコラボレーティブ検出モードに関して、通知深刻度レベルのスコアリングポイントを設定します。 

デフォルト値: 2。

* `secrequestmaxnumargs`: リクエストの引数の最大数を設定します。

  デフォルト値: 255。

* `secrequestbodylimit`:  バッファリングに関してModSecurityが受け入れる最大リクエストボディサイズを設定します。

  デフォルト値: '13107200'。

* `secrequestbodynofileslimit`: バッファリングに関してModSecurityが受け入れる最大リクエストボディサイズを設定します。リクエスト内でトランスポートされたファイルのサイズは除外されます。 

  デフォルト値: '131072'。

* `secrequestbodyinmemorylimit`: ModSecurityがメモリに保存する最大リクエストボディサイズを設定します。

  デフォルト値: '131072'。

##### クラス: `apache::mod::wsgi`

[`mod_wsgi`][]を使用したPythonサポートを有効にします。

**パラメータ**:　

* `mod_path`: `mod_wsgi`共有オブジェクト(`.so`)ファイルのパスを定義します。

  デフォルト値: `undef`。

  * `mod_path`パラメータに`/`が含まれていない場合、Puppetではオペレーティングシステムのデフォルトのモジュールパスの先頭にこれを付加します。含まれている場合は、そのとおりに扱われます。

* `package_name`: `mod_wsgi`をインストールするパッケージの名前。

  デフォルト値: `undef`。

* `wsgi_python_home`: '/path/to/venv'などの[`WSGIPythonHome`][]ディレクティブを定義します。

  値: パスを指定する文字列。　

  デフォルト値: `undef`。

* `wsgi_python_path`: '/path/to/venv/site*packages'などの[`WSGIPythonPath`][]ディレクティブを定義します。

  値: パスを指定する文字列。　

  デフォルト値: `undef`。

* `wsgi_restrict_embedded`: 'On'などの[`WSGIRestrictEmbedded`][]ディレクティブを定義します。

値: On|Off|undef。

デフォルト値: undef。

* `wsgi_socket_prefix`: "\${APACHE\_RUN\_DIR}WSGI"などの[`WSGISocketPrefix`][]ディレクティブを定義します。

  デフォルト値: [`apache::params`][]の`wsgi_socket_prefix`。

このクラスのパラメータはモジュールのディレクティブに相当します。詳細については、[モジュールのドキュメント][`mod_wsgi`]を参照してください。

### プライベートクラス

#### クラス: `apache::confd::no_accf`

FreeBSDの Apache 2.4で必要とされる`no-accf.conf`設定ファイルを`conf.d`内に作成します。

#### クラス: `apache::default_confd_files`

FreeBSDに`conf.d`を含めます。

#### クラス: `apache::default_mods`

デフォルト設定の実行に必要なApacheモジュールをインストールします。詳細については、`apache`クラスの[`default_mods`][]パラメータを参照してください。

#### クラス: `apache::package`

基本のApacheパッケージをインストールして設定します。

#### クラス: `apache::params`

各種のオペレーティングシステムのApacheパラメータを管理します。 

#### クラス: `apache::service`

Apacheデーモンを管理します。

#### クラス: `apache::version`

オペレーティングシステムに基づき、Apacheバージョンの自動検出を試みます。 

### パブリック定義タイプ　

#### 定義タイプ: `apache::balancer`

[`mod_proxy`][]を用いて、Apacheロードバランシンググループ(バランサクラスタとも呼ばれます)を作成します。各ロードバランシンググループには、1つ以上のバランサメンバーが必要です。これは、 [`apache::balancermember`][]定義タイプによりPuppet内で宣言することができます。

各Apacheロードバランシンググループにつき、1つの`apache::balancer`定義タイプを宣言します。すべてのバランサメンバーについて`apache::balancermember`定義タイプをエクスポートし、[エクスポートリソース][]を用いて単一のApacheロードバランササーバで収集することもできます。

**パラメータ**:　

##### `name`

バランサクラスタのタイトルと、その設定を含む`conf.d`の名前を設定します。

##### `proxy_set`

キー‐値ペアを[`ProxySet`][]行として設定します。値: [ハッシュ][]。

デフォルト値: '{}'。

##### `collect_exported`

[エクスポートリソース][]を使用するかどうかを決定します。

すべてのバックエンドサーバを静的に宣言する場合は、このパラメータを`false`に設定し、宣言済みの既存のバランサメンバーリソースに依存するようにします。また、[配列][]引数とともに`apache::balancermember`を使用します。

中央ノードで収集したエクスポートリソースを使用してバックエンドサーバを動的に宣言するには、このパラメータを`true`に設定し、バランサメンバーノードによりエクスポートされたバランサメンバーリソースを収集します。

エクスポートリソースを使用しない場合は、1回のPuppet実行ですべてのバランサメンバーが設定されます。エクスポートリソースを使用する場合は、まずバランシングしたノードについてPuppetを実行し、次にバランサで実行する必要があります。

ブーリアン。

デフォルト値: `true`。

#### 定義タイプ: `apache::balancermember`

[`mod_proxy_balancer`][]のメンバーを定義します。これにより、ロードバランサの`apache.cfg`内でリッスンするサービス設定ブロック内のバランサメンバーが設定されます。

**パラメータ**:　

##### `balancer_cluster`

**必須**。　

Apacheサービスのインスタンス名を設定します。宣言された[`apache::balancer`][]リソースの名前と一致する必要があります。

##### `url`

バランサメンバーサーバとの連絡に使用するURLを指定します。

デフォルト値: 'http://${::fqdn}/'。

##### `options`

URL後に[オプション](https://httpd.apache.org/docs/current/mod/mod_proxy.html#balancermember)の[配列][]を指定します。[`ProxyPass`][]で使用可能な任意のキー-値ペアを使用できます。

デフォルト値: 空配列。　

#### 定義タイプ: `apache::custom_config`

Apacheサーバの`conf.d`ディレクトリにカスタム設定ファイルを追加します。このファイルが無効で、この定義タイプの[`verify_config`][]パラメータの値が`true`になっている場合は、Puppet実行時にエラーが生じます。

**パラメータ**:　

##### `ensure`

設定ファイルが存在するべきかどうかを指定します。

値: 'absent'、'present'。　

デフォルト値: 'present'。　

##### `confdir`　

Puppetが設定ファイルを置くディレクトリを設定します。 

デフォルト値: [`$::apache::confd_dir`][`confd_dir`]の値。

##### `content`

設定ファイルのコンテンツを設定します。`content`および[`source`][]パラメータは、相互排他的な関係にあります。

デフォルト値: `undef`。　

##### `filename`

Puppetが設定を保存する`confdir`下のファイル名を設定します。

デフォルト値: `priority`パラメータから生成したファイル名およびリソース名。

##### `priority`

Apacheでは設定ファイルがアルファベット順に処理されるため、ファイル名の先頭にこのパラメータの数値を付加することで、設定ファイルの優先順位を設定します。

設定ファイル名の優先順位の接頭値を無視するには、このパラメータを`false`に設定します。

デフォルト値: '25'。

##### `source`

設定ファイルのソースを指します。[`content`][]および`source`パラメータは互いに排他的です。

デフォルト値: `undef`。　

##### `verify_command`

Puppetが設定ファイルの確認に用いるコマンドを指定します。完全修飾コマンドを使用してください。

このパラメータは、[`verify_config`][]パラメータの値が`true`になっている場合にのみ使用されます。`verify_command`が失敗すると、Puppet実行により設定ファイルが削除されてエラーが生じますが、Apacheサービスには通知されません。

デフォルト値: '/usr/sbin/apachectl -t'。

##### `verify_config`

Apacheサービスに通知する前に設定ファイルのバリデーションを行うかどうかを指定します。

ブーリアン。

デフォルト値: `true`。

#### 定義タイプ: `apache::fastcgi::server`

特定のファイルタイプを処理する1つまたは複数の外部FastCGIサーバを定義します。この定義タイプは、[`mod_fastcgi`][FastCGI]とともに使用します。

**パラメータ**　

##### `host`

FastCGIのホスト名またはIPアドレスおよびTCPポート番号(1-65535)を決定します。

デフォルト値: '127.0.0.1:9000'。

##### `timeout`

リクエストが中止され、エラーLogLevelにイベントが記録されるまでに、[FastCGI][]アプリケーションが非アクティブの状態で待機する秒数を設定します。この非アクティブタイマーは、FastCGIアプリケーションとの接続が待機中の場合のみ適用されます。アプリケーションの待ち行列に入ったリクエストに対して時間内に記述やフラッシュによる応答がないと、リクエストは中止されます。アプリケーションとの通信は完了したものの、クライアントとの通信が完了しなかった(応答がバッファリングされた)場合は、タイムアウトは適用されません。

デフォルト値: 15。

##### `flush`

アプリケーションから受信したデータを、強制的に[`mod_fastcgi`][FastCGI]がクライアントに書き込みます。デフォルトでは、アプリケーションをできるだけ早くフリーな状態にするために、`mod_fastcgi`はデータをバッファリングします。 

デフォルト値: `false`。

##### `faux_path`

Apacheには、このファイル名を決定するURIを処理する[FastCGI][]があります。このパラメータで設定されたパスは、ローカルのファイルシステムに存在する必要はありません。

デフォルト値: "/var/www/${name}.fcgi"。

##### `alias`

FastCGIサーバとアクションを内部でリンクします。このエイリアスは一意である必要があります。

デフォルト値: "/${name}.fcgi"。

##### `file_type`

FastCGIサーバにより処理するファイルの[MIME `content-type`][]を設定します。

デフォルト値: 'application/x-httpd-php'。

#### 定義タイプ: `apache::listen`

Apacheサーバまたはバーチャルホストのリッスンするアドレスとポートを定義する、Apache設定ディレクトリの`ports.conf`に、[`Listen`][]ディレクティブを追加します。[`apache::vhost`][]クラスはこの定義タイプを使用します。タイトルは `<PORT>`、`<IPV4>:<PORT>`、または`<IPV6>:<PORT>`の形式をとります。

#### 定義タイプ: `apache::mod`

対応する[`apache::mod::<MODULE NAME>`][]クラスを持たないApacheモジュール用のパッケージをインストールし、Apacheサーバの`module`および`enable`ディレクトリ内で、モジュールのデフォルト設定ファイルを確認または配置します。デフォルトのロケーションは、オペレーティングシステムによって異なります。

**パラメータ**:　

##### `package`

**必須**。　

PuppetがApacheモジュールのインストールに使用するパッケージの名前。

デフォルト値: `undef`。

##### `package_ensure`

Apacheモジュールをインストールの必要性をPuppetが確認するかどうかを決定します。

値: 'absent'、'present'。　

デフォルト値: 'present'。　

##### `lib`

モジュールの共有オブジェクト名を定義します。特別な理由がない限り、手動で設定しないでください。

デフォルト値: `mod_$name.so`。

##### `lib_path`

モジュールのライブラリのパスを指定します。特別な理由がない限り、手動で設定しないでください。[`path`][]パラメータは、この値をオーバーライドします。

デフォルト値: `apache`クラスの[`lib_path`][]パラメータ。


##### `loadfile_name`

モジュールの[`LoadFile`][]ディレクティブのファイル名を設定します。Apacheの処理はアルファベット順に行われるため、ファイル名によってモジュールのロード順序も設定できます。

値: `\*.load`の形式のファイル名。

デフォルト値: '$name.load'のように、リソース名の後に'load'をつけた値。

##### `loadfiles`

[`LoadFile`][]ディレクティブの配列を指定します。

デフォルト値: `undef`。

##### `path`

モジュールのパスを指定します。特別な理由がない限り、このパラメータは手動で設定しないでください。

デフォルト値: [`lib_path`][]/[`lib`][]。

#### 定義タイプ: `apache::namevirtualhost`

[名前ベースのバーチャルホスト][]を有効にし、Apache HTTPD設定ディレクトリの `ports.conf`ファイルに関連するすべてのディレクティブを追加します。タイトルは、'\*'、'\*:\<PORT\>'、'\_default\_:\<PORT\>、'\<IP\>'、または'\<IP\>:\<PORT\>'の形式をとることができます。

#### 定義タイプ: `apache::vhost`

apacheモジュールでは、バーチャルホストのセットアップと設定に関して、かなりの柔軟性が認められています。この柔軟性の一部は、定義リソースタイプの`vhost`によるものです。これを使えば、さまざまなパラメータを用いて、Apacheを何度も検証することができます。

`apache::vhost`定義タイプを使えば、デフォルトの範囲外の要件を持つバーチャルホストについて、特別な設定をすることができます。基本の`::apache`クラス内でデフォルトのバーチャルホストを設定することも、カスタマイズしたバーチャルホストをデフォルトとして設定することもできます。カスタマイズしたバーチャルホストの[`priority`][]の数値は基本のクラスよりも小さくなるため、Apacheはカスタマイズしたバーチャルホストを先に処理します。

`apache::vhost`定義タイプでは、`concat::fragment`を使用して設定ファイルを構築します。定義タイプがもともとサポートしていない設定の要素についてカスタムフラグメントを挿入するには、カスタムフラグメントをひとつずつ追加します。

`apache::vhost`定義タイプでは、カスタムフラグメントの`order`パラメータについては10の倍数が使用されるため、10の倍数ではない`order`が機能します。

> **Note:** `apache::vhost`を作成するとき、`default`または`default-ssl`を指定することはできません。これはこの属性を持つvhostsが常にモジュールによって管理されるためです。これは`Apache::Vhost['default']`または`Apache::Vhost['default-ssl]`リソースを上書きできないことを意味します。 オプションの回避策として、`my default`などの別の名前のvhostを作成して、`default`および`default_ssl`が`false`に設定されていることを確認します。

```
class { 'apache':
  default_vhost     => false
  default_ssl_vhost => false,
}
```

**パラメータ**:　

##### `access_log`

`*_access.log`ディレクティブ(`*_file`,`*_pipe`または`*_syslog`)を設定するかどうかを決定します。

ブーリアン。

デフォルト値: `true`。　

##### `access_log_env_var`

特定の環境変数を持つリクエストのみをロギングするように指定します。

デフォルト値: `undef`。

##### `access_log_file`

[`logroot`][]に置く`*_access.log`のファイル名を設定します。バーチャルホスト---例えばexample.comなど---を与えると、[SSL暗号化][SSL暗号化]バーチャルホストの場合はデフォルト値が'example.com_ssl.log'、暗号化されていないバーチャルホストの場合は'example.com_access.log'になります。

デフォルト値: `false`。

##### `access_log_format`

アクセスログに、[`LogFormat`][]のニックネームかカスタムフォーマットの文字列のいずれを使うかを指定します。 

デフォルト値: 'combined'。

##### `access_log_pipe`

Apacheがアクセスログメッセージを送信するパイプを指定します。

デフォルト値: `undef`。

##### `access_log_syslog`

すべてのアクセスログメッセージをsyslogに送ります。

デフォルト値: `undef`。

##### `add_default_charset`

[`AddDefaultCharset`][]ディレクティブのデフォルトのメディア文字セット値を設定します。これは`text/plain`および`text/html`応答に追加されます。

デフォルト値: `undef`。

##### `add_listen`

バーチャルホストが[`Listen`][]ステートメントを作成するかどうかを決定します。

`add_listen`を`false`に設定すると、バーチャルホストは`Listen`ステートメントを作成しません。これは、`ip`パラメータを渡されていないバーチャルホストと渡されているバーチャルホストを組み合わせる場合に重要となります。 

ブーリアン。

デフォルト値: `true`。

##### `use_optional_includes`

Apache 2.4以降の`additional_includes`について、Apacheが[`Include`][]の代わりに[`IncludeOptional`][]ディレクティブを使うかどうかを指定します。

ブーリアン。

デフォルト値: `false`。

##### `additional_includes`

追加の静的なバーチャルホスト固有のApache設定ファイルのパスを指定します。このパラメータを使えば、このモジュールでサポートされていない固有のカスタム設定を実装することができます。

値: パスを指定する文字列また文字列の[配列][]。

デフォルト値: 空配列。　

##### `aliases`

[ハッシュ][ハッシュ]のリストをバーチャルホストに渡し、[`mod_alias`][]ドキュメントに従って[`Alias`][]、[`AliasMatch`][]、[`ScriptAlias`][]、または[`ScriptAliasMatch`][]ディレクティブを作成します。

例:　

``` puppet
aliases => [
  { aliasmatch       => '^/image/(.*)\.jpg$',
    path             => '/files/jpg.images/$1.jpg',
  },
  { alias            => '/image',
    path             => '/ftp/pub/image',
  },
  { scriptaliasmatch => '^/cgi-bin(.*)',
    path             => '/usr/local/share/cgi-bin$1',
  },
  { scriptalias      => '/nagios/cgi-bin/',
    path             => '/usr/lib/nagios/cgi-bin/',
  },
  { alias            => '/nagios',
    path             => '/usr/share/nagios/html',
  },
],
```

`alias`、`aliasmatch`、`scriptalias`、`scriptaliasmatch`キーを機能させるには、`<Directory /path/to/directory>`、`<Location /some/location/here>`などの、それぞれに対応するコンテキストが必要です。Puppetは`aliases`パラメータで指定された順序でディレクティブを作成します。[`mod_alias`][]ドキュメントにもあるように、シャドーイングを避けるため、まず具体性の高い`alias`、`aliasmatch`、`scriptalias`、`scriptaliasmatch`パラメータを追加してから、全般的なパラメータを追加してください。

> **注意**: `scriptaliases`パラメータの代わりに`aliases`パラメータを使用すれば、各種のエイリアスディレクティブの順序を正確に制御できます。`scriptaliases`パラメータを使って`ScriptAliases`を定義すると、すべての*`Alias`ディレクティブの後に*すべての*`ScriptAlias`ディレクティブが*処理されます。これは`Alias`ディレクティブによる`ScriptAlias`ディレクティブのシャドーイングにつながり、多くの場合、問題が生じます。例えば、Nagiosに関する問題が生じる可能性があります。

I[`apache::mod::passenger`][]がロードされ、`PassengerHighPerformance`が`true`になっている場合、`Alias`ディレクティブが`PassengerEnabled => off`ステートメントを履行できない可能性があります。詳細については、[この記事](http://www.conandalton.net/2010/06/passengerenabled-off-not-working.html)を参照してください。

##### `allow_encoded_slashes`

バーチャルホストの[`AllowEncodedSlashes`][]宣言を設定し、サーバのデフォルトをオーバーライドします。これにより、`\`および`/`文字を含むURLに対するバーチャルホストの応答が変更されます。値: 'nodecode'、'off'、'on'。デフォルト設定では、サーバ設定からこの宣言が省かれ、Apacheのデフォルト設定'Off'が選択されます。

デフォルト値: `undef`。　

##### `block`

Apacheがアクセスをブロックする対象のリストを指定します。有効なオプション: 'scm'、これにより、`.svn`、`.git`、`.bzr`ディレクティブへのWebアクセスがブロックされます。

デフォルト値: 空[配列][]。

##### `cas_attribute_prefix`

SAMLバリデーションが有効になっている場合に、このヘッダの値を属性値としてヘッダを追加します。

デフォルト値: [`apache::mod::auth_cas`][]により設定された値。

##### `cas_attribute_delimiter`

`cas_attribute_prefix`により作成されたヘッダの属性値の区切り文字を設定します。

デフォルト値: [`apache::mod::auth_cas`][]により設定された値。　

##### `cas_login_url`

ユーザがCASで保護されたリソースへのアクセスを試み、かつアクティブなセッションがない場合に、モジュールがユーザをリダイレクトする先のURLを設定します。

デフォルト値: [`apache::mod::auth_cas`][]により設定された値。　

##### `cas_scrub_request_headers`

mod_auth_cas内で特別な意味を持つ可能性のあるインバウンドリクエストヘッダを削除します。

デフォルト値: [`apache::mod::auth_cas`][]により設定された値。　

##### `cas_sso_enabled`

`cas_sso_enabled`: シングルサインアウトの実験的サポートを有効にします(POSTデータが壊れる可能性があります)。

デフォルト値: [`apache::mod::auth_cas`][]により設定された値。　

##### `cas_validate_saml`

SAMLに関するCASサーバからの解析応答。

デフォルト値: [`apache::mod::auth_cas`][]により設定された値。　

##### `cas_validate_url`

HTTPクエリ文字列でクライアントの提示するチケットをバリデーションする際に使用するURL。

デフォルト値: [`apache::mod::auth_cas`][]により設定された値。

##### `custom_fragment`

カスタム設定ディレクティブの文字列を渡し、バーチャルホスト設定の最後に配置します。

デフォルト値: `undef`。

##### `default_vhost`

任意の`apache::vhost`定義タイプを、他の`apache::vhost`定義タイプと一致しないリクエストをサーブするためのデフォルトとして設定します。 

デフォルト値: `false`。

##### `directories`

[`directories`](#parameter-directories-for-apachevhost)セクションを参照してください。

##### `directoryindex`

ディレクトリ名の最後で'/'を指定することで、クライアントがディレクトリのインデックスをリクエストした際に探すべきリソースのリストを設定します。詳細については、[`DirectoryIndex`][]ディレクティブドキュメントを参照してください。

デフォルト値: `undef`。

##### `docroot`

**必須**。　

[`DocumentRoot`][]ロケーションを設定します。Apacheはここからファイルをサーブします。

`docroot`と[`manage_docroot`][]がともに`false`に設定されている場合、[`DocumentRoot`][]は設定されず、それに付随する`<Directory /path/to/directory>`ブロックは作成されません。

値: ディレクトリパスを指定する文字列。

##### `docroot_group`

[`docroot`][]ディレクトリへのグループアクセスを設定します。

値: システムグループを指定する文字列。

デフォルト値: 'root'。　

##### `docroot_owner`

[`docroot`][]ディレクトリへの個々のユーザのアクセスを設定します。

値: ユーザアカウントを指定する文字列。

デフォルト値: 'root'。　

##### `docroot_mode`

[`docroot`][]ディレクトリへのアクセス許可を数字表記法で設定します。

値: 文字列。　

デフォルト値: `undef`。

##### `manage_docroot`

Puppetが[`docroot`][]ディレクトリを管理するかどうかを決定します。

ブーリアン。

デフォルト値: `true`。

##### `error_log`

`*_error.log`ディレクティブを設定するかどうかを指定します。

ブーリアン。

デフォルト値: `true`。

##### `error_log_file`

バーチャルホストのエラーログについて、`*_error.log`ファイルを優先します。このパラメータが定義されていない場合、Puppetはまず[`error_log_pipe`][]で、次に[`error_log_syslog`][]で値を確認します。

これらのパラメータをいずれも設定しない場合は、例えばバーチャルホストが`example.com`なら、PuppetはSSLバーチャルホストのデフォルトを'$logroot/example.com_error_ssl.log'、非SSLバーチャルホストのデフォルトを'$logroot/example.com_error.log'とします。

デフォルト値: `undef`。

##### `error_log_pipe`

エラーログメッセージを送るパイプを指定します。

[`error_log_file`][]パラメータに値がある場合は、このパラメータに効力は生じません。このパラメータにも`error_log_file`にも値がない場合、Puppetは[`error_log_syslog`][]をチェックします。

デフォルト値: `undef`。

##### `error_log_syslog`

すべてのエラーログメッセージをsyslogに送るかどうかを決定します。

[`error_log_file`][]パラメータまたは[`error_log_pipe`][]パラメータのいずれかに値がある場合、このパラメータの効力は生じません。これらのパラメータのいずれにも値がない場合は、例えばバーチャルホスト`example.com`では、PuppetはSSLバーチャルホストのデフォルトを'$logroot/example.com_error_ssl.log'、非SSLバーチャルホストのデフォルトを '$logroot/example.com_error.log'とします。

ブーリアン。

デフォルト値: `undef`。

##### `error_documents`

このバーチャルホストの[エラードキュメント](https://httpd.apache.org/docs/current/mod/core.html#errordocument)設定のオーバーライドに使用できるハッシュのリスト。

例:　

``` puppet
apache::vhost { 'sample.example.net':
  error_documents => [
    { 'error_code' => '503', 'document' => '/service-unavail' },
    { 'error_code' => '407', 'document' => 'https://example.com/proxy/login' },
  ],
}
```

デフォルト値: '[]'。

##### `ensure`

バーチャルホストが存在するかどうかを指定します。

値: 'absent'、'present'。　

デフォルト値: 'present'。　

##### `fallbackresource`

[FallbackResource](https://httpd.apache.org/docs/current/mod/mod_dir.html#fallbackresource)ディレクティブを設定します。このディレクティブは、ファイルシステム内のどこにもマッピングされていないURLに対してどのようなアクションをとるか指定します。指定されていない場合は'HTTP 404 (Not Found)'が返されます。値は'/'で始めるか、'disabled'とする必要があります。

デフォルト値: `undef`。

#####`fastcgi_idle_timeout`

fastcgiを使用する場合に、このオプションにより、サーバ応答のタイムアウトを設定します。

デフォルト値: `undef`。

##### `file_e_tag`

[`FileETag`][]宣言のサーバデフォルトを設定します。これにより、静的ファイルの応答ヘッダフィールドが変更されます。

値: 'INode'、'MTime'、'Size'、'All'、'None'。

デフォルト値: `undef`、この場合、Apacheのデフォルト設定'MTime Size'が使用されます。

##### `filters`

[フィルタ](https://httpd.apache.org/docs/current/mod/mod_filter.html)により、アウトプットコンテンツフィルタのスマートな文脈依存設定が有効になります。

``` puppet
apache::vhost { "$::fqdn":
  filters => [
    'FilterDeclare   COMPRESS',
    'FilterProvider  COMPRESS DEFLATE resp=Content-Type $text/html',
    'FilterChain     COMPRESS',
    'FilterProtocol  COMPRESS DEFLATE change=yes;byteranges=no',
  ],
}
```

##### `force_type`

[`ForceType`][]ディレクティブを設定します。このディレクティブは、[MIME `content-type`][]がこのパラメータの値に一致するすべてのマッチングファイルをApacheに強制的にサーブさせます。

#### `add_charset`

ディレクトリおよびファイル拡張子ごとに、Apacheにカスタムコンテンツ文字セットを設定させます。

##### `headers`

レスポンスヘッダを置換、結合、または削除するための行を追加します。詳細については、[Apacheのmod_headersドキュメント](https://httpd.apache.org/docs/current/mod/mod_headers.html#header)を参照してください。

値: 文字列または文字列の配列。　

デフォルト値: `undef`。

##### `ip`

バーチャルホストがリッスンするIPアドレスを設定します。デフォルトでは、Apacheのデフォルト挙動が使用され、すべてのIPをリッスンします。

値: 文字列または文字列の配列。　

デフォルト値: `undef`。

##### `ip_based`

[IPベースの](https://httpd.apache.org/docs/current/vhosts/ip-based.html)バーチャルホストを有効にします。このパラメータにより、NameVirtualHostディレクティブの作成が禁止されます。これは、このディレクティブが名前ベースのバーチャルホストにリクエストを送る際に使用されるためです。

デフォルト値: `false`。

##### `itk`

ハッシュで[ITK](http://mpm-itk.sesse.net/)を設定します。

通常は、以下のように使用します。

``` puppet
apache::vhost { 'sample.example.net':
  docroot => '/path/to/directory',
  itk     => {
    user  => 'someuser',
    group => 'somegroup',
  },
}
```　

値: ハッシュ。キーを含めることもできます。

* ユーザ + グループ
* `assignuseridexpr`
* `assigngroupidexpr`
* `maxclientvhost`
* `nice`
* `limituidrange` (Linux 3.5.0以降)
* `limitgidrange` (Linux 3.5.0以降)

通常は、以下のように使用します。　

``` puppet
apache::vhost { 'sample.example.net':
  docroot => '/path/to/directory',
  itk     => {
    user  => 'someuser',
    group => 'somegroup',
  },
}
```　

デフォルト値: `undef`。

##### `jk_mounts`

'JkMount'および'JkUnMount'ディレクティブによりバーチャルホストを設定し、TomcatとApacheの間をマッピングするURLのパスを処理します。

このパラメータは、ハッシュの配列にする必要があります。各ハッシュには、'worker'と、'mount'または'unmount'キーのいずれかが含まれている必要があります。

通常は、以下のように使用します。　

``` puppet
apache::vhost { 'sample.example.net':
  jk_mounts => [
    { mount   => '/*',     worker => 'tcnode1', },
    { unmount => '/*.jpg', worker => 'tcnode1', },
  ],
}
```
デフォルト値: `undef`。

##### `keepalive`

バーチャルホストで[`KeepAlive`][]ディレクティブによるHTTPの持続的接続を有効にするかどうかを決定します。デフォルトでは、グローバルなサーバ全体の[`KeepAlive`][]設定が有効になります。

バーチャルホストの関連オプションを設定するには、`keepalive_timeout`および`max_keepalive_requests`パラメータを使用します。

値: 'Off'、'On'。

デフォルト値: `undef`。　

##### `keepalive_timeout`

バーチャルホストの[`KeepAliveTimeout`]ディレクティブを設定します。これにより、HTTPの持続的接続で後続のリクエストを実行するまでの待機時間が決まります。デフォルトでは、グローバルなサーバ全体の[`KeepAlive`][]設定が有効になります。

このパラメータが意味を持つのは、グローバルなサーバ全体の[`keepalive`パラメータ][]またはバーチャルホストごとの`keepalive`パラメータのいずれかが有効になっている場合のみです。　

デフォルト値: `undef`。　

##### `max_keepalive_requests`

接続1回につき許可されるバーチャルホストへのリクエスト数を制限します。デフォルトでは、グローバルなサーバ全体の[`KeepAlive`][]設定が有効になります。

このパラメータが意味を持つのは、グローバルなサーバ全体の[`keepalive`パラメータ][]またはバーチャルホストごとの`keepalive`パラメータのいずれかが有効になっている場合のみです。　

デフォルト値: `undef`。

##### `auth_kerb`

バーチャルホストの[`mod_auth_kerb`][]パラメータを有効にします。

通常は、以下のように使用します。　

``` puppet
apache::vhost { 'sample.example.net':
  auth_kerb              => `true`,
  krb_method_negotiate   => 'on',
  krb_auth_realms        => ['EXAMPLE.ORG'],
  krb_local_user_mapping => 'on',
  directories            => {
    path         => '/var/www/html',
    auth_name    => 'Kerberos Login',
    auth_type    => 'Kerberos',
    auth_require => 'valid-user',
  },
}
```

関連するパラメータは、`mod_auth_kerb`ディレクティブの名前に従います。

- `krb_method_negotiate`: Negotiateメソッドを使用するかどうかを決定します。デフォルト値: 'on'。
- `krb_method_k5passwd`: Kerberos v5に関してパスワードベースの認証を使用するかどうかを決定します。デフォルト値: 'on'。
- `krb_authoritative`: 'off'に設定すると、認証コントロールを別のモジュールに渡すことができます。デフォルト値: 'on'。
- `krb_auth_realms`: 認証に使用するKerberos領域の配列を指定します。デフォルト値: '[]'。
- `krb_5keytab`: Kerberos v5キータブファイルのロケーションを指定します。デフォルト値: `undef`。
- `krb_local_user_mapping`: 今後の使用のために、ユーザ名から@REALMを取り除きます。デフォルト値: `undef`。

ブーリアン。

デフォルト値: `false`。

##### `krb_verify_kdc`

このオプションを使えば、ローカルなキータブに対する認証チケットを無効にし、KDCスプーフィング攻撃を防ぐことができます。

デフォルト値: 'on'。

##### `krb_servicename`

Apacheが認証に使用するサービス名を指定します。この名前に対応するキーをキータブに保存する必要があります。

デフォルト値: 'HTTP'。

##### `krb_save_credentials`

このオプションにより、認証情報の保存機能が有効になります。

デフォルト値: 'off'。

##### `logroot`

バーチャルホストのログファイルの保存場所を指定します。

デフォルト値: '/var/log/<apache log location>/'。

##### `$logroot_ensure`

バーチャルホストのlogrootディレクトリを削除するかどうかを決定します。

値: 'directory'、'absent'。

デフォルト値: 'directory'。

##### `logroot_mode`

logrootディレクトリで設定されたモードをオーバーライドします。影響を把握できない場合は、ログが保存されているディレクトリへの書き込みアクセス権限を付与*しないで*ください。詳細については、[Apacheのログセキュリティドキュメント](https://httpd.apache.org/docs/2.4/logs.html#security)を参照してください。

デフォルト値: `undef`。

##### `logroot_owner`

logrootディレクトリへの個々のユーザのアクセスを設定します。

デフォルト値：`undef`。

##### `logroot_group`

[`logroot`][]ディレクトリへのグループアクセスを設定します。

デフォルト値：`undef`。

##### `log_level`

エラーログの詳細レベルを指定します。

値: 'emerg'、'alert'、'crit'、'error'、'warn'、'notice'、'info'、'debug'。

デフォルト値: グローバルサーバ設定については'warn'。バーチャルホストごとにオーバーライドできます。

###### `modsec_body_limit`

バッファリングに関してModSecurityが受け入れる最大リクエストボディサイズをバイト数で設定します。

値: 整数。

デフォルト値: `undef`。

###### `modsec_disable_vhost`

バーチャルホストで[`mod_security`][]を無効にします。[`apache::mod::security`][]が含まれている場合にのみ有効です。

ブーリアン。

デフォルト値: `undef`。

###### `modsec_disable_ids`

バーチャルホストから`mod_security` IDを削除します。

値: バーチャルホストから削除する`mod_security` IDの配列。ハッシュも使用できます。この場合、特定のロケーションからのIDの削除が可能です。

``` puppet
apache::vhost { 'sample.example.net':
  modsec_disable_ids => [ 90015, 90016 ],
}
```

``` puppet
apache::vhost { 'sample.example.net':
  modsec_disable_ids => { '/location1' => [ 90015, 90016 ] },
}
```

デフォルト値: `undef`。

###### `modsec_disable_ips`

[`mod_security`][]ルールマッチングから除外するIPアドレスの配列を指定します。

デフォルト値: `undef`。

###### `modsec_disable_msgs`

バーチャルホストから削除するmod_security Msgの配列。ハッシュも使用できます。この場合、特定のロケーションからのMsgの削除が可能です。

``` puppet
apache::vhost { 'sample.example.net':
  modsec_disable_msgs => [ 'Blind SQL Injection Attack', 'Session Fixation Attack' ],
}
```

``` puppet
apache::vhost { 'sample.example.net':
  modsec_disable_msgs => { '/location1' => [ 'Blind SQL Injection Attack', 'Session Fixation Attack' ] },
}
```

デフォルト値: `undef`。

###### `modsec_disable_tags`

 バーチャルホストから削除するmod_securityタグの配列。ハッシュも使用できます。この場合、特定のロケーションからのタグの削除が可能です。

``` puppet
apache::vhost { 'sample.example.net':
  modsec_disable_tags => [ 'WEB_ATTACK/SQL_INJECTION', 'WEB_ATTACK/XSS' ],
}
```

``` puppet
apache::vhost { 'sample.example.net':
  modsec_disable_tags => { '/location1' => [ 'WEB_ATTACK/SQL_INJECTION', 'WEB_ATTACK/XSS' ] },
}
```

デフォルト値: `undef`。

##### `modsec_audit*`

* `modsec_audit_log`
* `modsec_audit_log_file`
* `modsec_audit_log_pipe`

この3つのパラメータは、いずれも`mod_security`オーディットログの送信方法を決定します([SecAuditLog](https://github.com/SpiderLabs/ModSecurity/wiki/Reference-Manual#SecAuditLog))。

* `modsec_audit_log_file`が設定されている場合は、[`logroot`][]と比較されます。

  デフォルト値: `undef`。

* `modsec_audit_log_pipe`を設定する場合は、パイプで始める必要があります。例えば、'|/path/to/mlogc /path/to/mlogc.conf'のようになります。

  デフォルト値: `undef`。

* `modsec_audit_log`が`true`になっている場合、バーチャルホスト---example.comなど---を与えると、[SSL暗号化][SSL encryption]バーチャルホストの場合はデフォルト値が'example.com\_security\_ssl.log'、暗号化されていないバーチャルホストの場合は'example.com\_security.log'になります。

  デフォルト値: `false`。

上述のパラメータがいずれも設定されていない場合、グローバルオーディットログが使用されます(''/var/log/httpd/modsec\_audit.log''; Debianおよびデリバティブ: ''/var/log/apache2/modsec\_audit.log''; その他: )。

##### `no_proxy_uris`

プロキシを使用しないURLを指定します。このパラメータは、[`proxy_dest`](#proxy_dest)と組み合わせて使用することはできません。

デフォルト値: []。　

##### `no_proxy_uris_match`

このディレクティブは[`no_proxy_uris`][]と同じですが、正規表現をとります。

デフォルト値: []。　

##### `proxy_preserve_host`

[ProxyPreserveHostディレクティブ](https://httpd.apache.org/docs/current/mod/mod_proxy.html#proxypreservehost)を設定します。

このパラメータを`true`に設定すると、受信リクエストの`Host:`行が有効になり、ホスト名ではなくホストにプロキシされます。`false`に設定すると、このディレクティブが'Off'になります。

ブーリアン。

デフォルト値: `false`。

##### `proxy_add_headers`

[ProxyAddHeadersディレクティブ](https://httpd.apache.org/docs/current/mod/mod_proxy.html#proxyaddheaders)を設定します。

このパラメータは、プロキシ関連のHTTPヘッダ(X-Forwarded-For、X-Forwarded-Host、X-Forwarded-Server)をバックエンドサーバに送信するかどうかを制御します。

ブーリアン。

デフォルト値: `false`。

##### `proxy_error_override`

[ProxyErrorOverrideディレクティブ](https://httpd.apache.org/docs/current/mod/mod_proxy.html#proxyerroroverride)を設定します。このディレクティブは、プロキシされたコンテンツに関するエラーページをApacheによりオーバーライドすべきかどうかを制御します。

ブーリアン。

デフォルト値: `false`。

##### `options`

指定されたバーチャルホストの[`Options`][]を設定します。例:

``` puppet
apache::vhost { 'site.name.fdqn':
  …
  options => ['Indexes','FollowSymLinks','MultiViews'],
}
```

> **注意**: [`apache::vhost`][]の[`directories`][]パラメータを使うと、'Options'、'Override'、'DirectoryIndex'は`directories`内のパラメータであるため、無視されます。

デフォルト値: ['Indexes','FollowSymLinks','MultiViews']。

##### `override`

指定されたバーチャルホストのオーバーライドを設定します。[AllowOverride](https://httpd.apache.org/docs/current/mod/core.html#allowoverride)引数の配列を使用できます。

デフォルト値: '[none]'。

##### `passenger_spawn_method`

[PassengerSpawnMethod](https://www.phusionpassenger.com/library/config/apache/reference/#passengerspawnmethod)を設定します。Passengerが引き起こしたアプリケーションに直接か、preforkのcopy-on-writeメカニズムを使用します。

有効なオプション: `smart`または`direct`。

デフォルト値: `undef`。

##### `passenger_app_root`

[PassengerRoot](https://www.phusionpassenger.com/library/config/apache/reference/#passengerapproot)を設定します。これは、DocumentRootと異なる場合のPassengerアプリケーションルートのロケーションです。

値: パスを指定する文字列。　

デフォルト値: `undef`。

##### `passenger_app_env`

[PassengerAppEnv](https://www.phusionpassenger.com/library/config/apache/reference/#passengerappenv)を設定します。これは、Passengerアプリケーションに関する環境です。指定されていない場合は、グローバル設定の'production'がデフォルトになります。

値: 環境名を指定する文字列。

デフォルト値: `undef`。

##### `passenger_log_file`

デフォルトでは、PassengerログメッセージはApacheグローバルエラーログに書き込まれます。[PassengerLogFile](https://www.phusionpassenger.com/library/config/apache/reference/#passengerlogfile)を使えば、そのメッセージを別のファイルに書き込むように設定することができます。このオプションは、Passenger 5.0.5以降でのみ使用できます。

値: パスを指定する文字列。　

デフォルト値: `undef`。

##### `passenger_log_level`

このオプションを使えば、ログファイルに書き込む情報の量を指定できます。設定されていない場合は、[PassengerLogLevel](https://www.phusionpassenger.com/library/config/apache/reference/#passengerloglevel)は設定ファイルに表示されず、デフォルト値が使用されます。

デフォルト値: 3.0.0以前のPassengerバージョン: '0'; 5.0.0以降: '3'。

##### `passenger_ruby`

[PassengerRuby](https://www.phusionpassenger.com/library/config/apache/reference/#passengerruby)を設定します。これは、バーチャルホスト上でこのアプリケーションに関して使用するRubyインタープリタです。

デフォルト値: `undef`。

##### `passenger_min_instances`

[PassengerMinInstances](https://www.phusionpassenger.com/library/config/apache/reference/#passengermininstances)を設定します。これは、実行するアプリケーションプロセスの最小数です。

##### `passenger_max_requests`

[PassengerMaxRequests](https://www.phusionpassenger.com/library/config/apache/reference/#pas
sengermaxrequests)を設定します。これは、アプリケーションプロセスが処理するリクエストの最大数です。

##### `passenger_max_instances_per_app`

[PassengerMaxInstancesPerApp](https://www.phusionpassenger.com/library/config/apache/reference/#passengermaxinstancesperapp)を設定します。これは、単一のアプリケーションに関して同時に存在できるアプリケーションプロセスの最大数です。

デフォルト値: `undef`。

##### `passenger_start_timeout`

[PassengerStartTimeout](https://www.phusionpassenger.com/library/config/apache/reference/#passengerstarttimeout)を設定します。これは、アプリケーション起動のタイムアウトです。

##### `passenger_pre_start`

[PassengerPreStart](https://www.phusionpassenger.com/library/config/apache/reference/#passengerprestart)を設定します。これは、プレ起動が必要とされる場合のアプリケーションのURLです。 

##### `passenger_user`

[PassengerUser](https://www.phusionpassenger.com/library/config/apache/reference/#passengeruser)を設定します。これは、サンドボックスアプリケーションの実行ユーザです。

##### `passenger_high_performance`

[`PassengerHighPerformance`](https://www.phusionpassenger.com/library/config/apache/reference/#passengerhighperformance)パラメータを設定します。

値: `true`、`false`。

デフォルト値: `undef`。

##### `passenger_nodejs`

[`PassengerNodejs`](https://www.phusionpassenger.com/library/config/apache/reference/#passengernodejs)を設定します。これは、バーチャルホスト上でこのアプリケーションに関して使用するNodeJSインタープリタです。

##### `passenger_sticky_sessions`

[`PassengerStickySessions`](https://www.phusionpassenger.com/library/config/apache/reference/#passengerstickysessions)パラメータを設定します。

ブーリアン。

デフォルト値: `undef`。

##### `passenger_startup_file`

[`PassengerStartupFile`](https://www.phusionpassenger.com/library/config/apache/reference/#passengerstartupfile)パスを設定します。このパスは、アプリケーションルートに関連しています。

##### `php_flags & values`

バーチャルホストごとの設定[`php_value`または`php_flag`](http://php.net/manual/en/configuration.changes.php)を許可します。これらのフラグや値は、ユーザまたはアプリケーションにより上書きすることができます。

デフォルト値: '{}'。

##### `php_admin_flags & values`

バーチャルホストごとの設定[`php_admin_value`または`php_admin_flag`](http://php.net/manual/en/configuration.changes.php)を許可します。これらのフラグや値は、ユーザまたはアプリケーションにより上書きすることができます。

デフォルト値: '{}'。

##### `port`

ホストを設定するポートを設定します。モジュールのデフォルトでは、ホストがリッスンするのは、非SSLバーチャルホストではポート80、SSLバーチャルホストではポート443です。ホストはこのパラメータで設定されたポートのみをリッスンします。

##### `priority`

Apache HTTPD VirtualHost設定ファイルに関連するロード順序を設定します。 

優先順位に一致するものがない場合は、アルファベット順で最初の名前ベースのバーチャルホストが使用されます。同様に、高い優先順位を渡すと、他に一致する名前がなければ、アルファベット順で最初の名前ベースのバーチャルホストが使用されます。

> **注意:** このパラメータを使用する必要はありません。ただし、使用する場合は、`apache::vhost`の`default_vhost`パラメータの優先順位は'15'になる点に留意してください。

ファイル名の優先順位の接頭値を無視するには、優先順位として`false`を渡します。

デフォルト値: '25'。

##### `proxy_dest`

[ProxyPass](https://httpd.apache.org/docs/current/mod/mod_proxy.html#proxypass)設定の宛先アドレスを指定します。

デフォルト値: `undef`。

##### `proxy_pass`

[ProxyPass](https://httpd.apache.org/docs/current/mod/mod_proxy.html#proxypass)設定の`path => URI`値の配列を指定します。オプションで、配列としてパラメータを追加できます。

デフォルト値: `undef`。

``` puppet
apache::vhost { 'site.name.fdqn':
  …
  proxy_pass => [
    { 'path' => '/a', 'url' => 'http://backend-a/' },
    { 'path' => '/b', 'url' => 'http://backend-b/' },
    { 'path' => '/c', 'url' => 'http://backend-a/c', 'params' => {'max'=>20, 'ttl'=>120, 'retry'=>300}},
    { 'path' => '/l', 'url' => 'http://backend-xy',
      'reverse_urls' => ['http://backend-x', 'http://backend-y'] },
    { 'path' => '/d', 'url' => 'http://backend-a/d',
      'params' => { 'retry' => '0', 'timeout' => '5' }, },
    { 'path' => '/e', 'url' => 'http://backend-a/e',
      'keywords' => ['nocanon', 'interpolate'] },
    { 'path' => '/f', 'url' => 'http://backend-f/',
      'setenv' => ['proxy-nokeepalive 1','force-proxy-request-1.0 1']},
    { 'path' => '/g', 'url' => 'http://backend-g/',
      'reverse_cookies' => [{'path' => '/g', 'url' => 'http://backend-g/',}, {'domain' => 'http://backend-g', 'url' => 'http:://backend-g',},], },
    { 'path' => '/h', 'url' => 'http://backend-h/h',
      'no_proxy_uris' => ['/h/admin', '/h/server-status'] },
  ],
}
```

* `reverse_urls`。*オプション。*この設定は、`mod_proxy_balancer`とともに使用する場合に役立ちます。値: 配列または文字列。
* `reverse_cookies`。*オプション。*`ProxyPassReverseCookiePath`および`ProxyPassReverseCookieDomain`を設定します。
* `params`。*オプション。*接続設定などのProxyPassキー-値パラメータを許可します。
* `setenv`。*オプション。*プロキシディレクティブの[環境変数](https://httpd.apache.org/docs/current/mod/mod_proxy.html#envsettings)を設定します。値: 配列。

##### `proxy_dest_match`

このディレクティブは[`proxy_dest`][]と同じですが、正規表現をとります。詳細については、[ProxyPassMatch](https://httpd.apache.org/docs/current/mod/mod_proxy.html#proxypassmatch)を参照してください。

##### `proxy_dest_reverse_match`

[`proxy_dest_match`][]が指定されている場合に、ProxyPassReverseを渡せるようにします。詳細については、[ProxyPassReverse](https://httpd.apache.org/docs/current/mod/mod_proxy.html#proxypassreverse)を参照してください。

##### `proxy_pass_match`

このディレクティブは[`proxy_pass`][]と同じですが、正規表現をとります。詳細については、[ProxyPassMatch](https://httpd.apache.org/docs/current/mod/mod_proxy.html#proxypassmatch)を参照してください。

##### `rack_base_uris`

rack設定のリソース識別子を設定します。指定されたファイルパスは、_rack.erbテンプレート内の[Phusion Passenger](http://www.modrails.com/documentation/Users%20guide%20Apache.html#_railsbaseuri_and_rackbaseuri)のrackアプリケーションルートとしてリストされます。

デフォルト値: `undef`。

#####`passenger_base_uris`

任意のURIをPhusion Passengerのサーブするアプリケーションとして指定するのに使用します。指定されたファイルパスは、_passenger_base_uris.erbテンプレート内の[Phusion Passenger](https://www.phusionpassenger.com/documentation/Users%20guide%20Apache.html#PassengerBaseURI)のpassengerアプリケーションルートとしてリストされます。

デフォルト値: `undef`。

##### `redirect_dest`

リダイレクト先のアドレスを指定します。

デフォルト値: `undef`。

##### `redirect_source`

`redirect_dest`で指定された宛先にリダイレクトするソースURIを指定します。リダイレクトするアイテムが複数提供されている場合は、ソースと宛先の長さを一致させる必要があります。また、アイテムは順序に依存します。

``` puppet
apache::vhost { 'site.name.fdqn':
  …
  redirect_source => ['/images','/downloads'],
  redirect_dest   => ['http://img.example.com/','http://downloads.example.com/'],
}
```

##### `redirect_status`

リダイレクトに追加するステータスを指定します。

デフォルト値: `undef`。

``` puppet
apache::vhost { 'site.name.fdqn':
  …
  redirect_status => ['temp','permanent'],
}
```

##### `redirectmatch_*`

* `redirectmatch_regexp`
* `redirectmatch_status`
* `redirectmatch_dest`

任意の正規表現について呼び出すサーバステータスとユーザの転送先を決定します。配列として入力します。

デフォルト値: `undef`。

``` puppet
apache::vhost { 'site.name.fdqn':
  …
  redirectmatch_status => ['404','404'],
  redirectmatch_regexp => ['\.git(/.*|$)/','\.svn(/.*|$)'],
  redirectmatch_dest => ['http://www.example.com/1','http://www.example.com/2'],
}
```

##### `request_headers`

他のリクエストヘッダの追加、リクエストヘッダの削除など、収集した[リクエストヘッダ](https://httpd.apache.org/docs/current/mod/mod_headers.html#requestheader)をさまざまな形で修正します。

デフォルト値: `undef`。

``` puppet
apache::vhost { 'site.name.fdqn':
  …
  request_headers => [
    'append MirrorID "mirror 12"',
    'unset MirrorID',
  ],
}
```

##### `rewrites`

URLリライトルールを作成します。ハッシュの配列が求められます。

値: 'comment'、'rewrite_base'、'rewrite_cond'、'rewrite_rule'、'rewrite_map'のいずれかのハッシュキー。

デフォルト値: `undef`。

誰かがindex.htmlにアクセスした場合、welcome.htmlを表示するように指定できます。

``` puppet
apache::vhost { 'site.name.fdqn':
  …
  rewrites => [ { rewrite_rule => ['^index\.html$ welcome.html'] } ]
}
```

このパラメータにより条件をリライトし、`true`の場合に関連ルールを実行させることが可能です。例えば、ビジターがIEを使っている場合のみURLをリライトするには、以下のように設定します。

``` puppet
apache::vhost { 'site.name.fdqn':
  …
  rewrites => [
    {
      comment      => 'redirect IE',
      rewrite_cond => ['%{HTTP_USER_AGENT} ^MSIE'],
      rewrite_rule => ['^index\.html$ welcome.html'],
    },
  ],
}
```

複数の条件を適用することもできます。たとえば、ブラウザがLynxかMozilla(バージョン1または2)の場合にのみ、index.htmlをwelcome.htmlにリライトする場合は、以下のようになります。

``` puppet
apache::vhost { 'site.name.fdqn':
  …
  rewrites => [
    {
      comment      => 'Lynx or Mozilla v1/2',
      rewrite_cond => ['%{HTTP_USER_AGENT} ^Lynx/ [OR]', '%{HTTP_USER_AGENT} ^Mozilla/[12]'],
      rewrite_rule => ['^index\.html$ welcome.html'],
    },
  ],
}
```

複数のリライトと条件を設定することも可能です。

``` puppet
apache::vhost { 'site.name.fdqn':
  …
  rewrites => [
    {
      comment      => 'Lynx or Mozilla v1/2',
      rewrite_cond => ['%{HTTP_USER_AGENT} ^Lynx/ [OR]', '%{HTTP_USER_AGENT} ^Mozilla/[12]'],
      rewrite_rule => ['^index\.html$ welcome.html'],
    },
    {
      comment      => 'Internet Explorer',
      rewrite_cond => ['%{HTTP_USER_AGENT} ^MSIE'],
      rewrite_rule => ['^index\.html$ /index.IE.html [L]'],
    },
    {
      rewrite_base => /apps/,
      rewrite_rule => ['^index\.cgi$ index.php', '^index\.html$ index.php', '^index\.asp$ index.html'],
    },
    { comment      => 'Rewrite to lower case',
      rewrite_cond => ['%{REQUEST_URI} [A-Z]'],
      rewrite_map  => ['lc int:tolower'],
      rewrite_rule => ['(.*) ${lc:$1} [R=301,L]'],
    },
  ],
}
```

リライトのルールおよび条件については、[`mod_rewrite`ドキュメント][`mod_rewrite`]を参照してください。

##### `rewrite_inherit`

バーチャルホストが全体のリライトルールを継承するかどうかを決定します。

デフォルト値: `false`。

リライトルールは、全体(`$conf_file`または`$confd_dir`で)またはバーチャルホストの`.conf`ファイル内で指定することができます。デフォルトでは、バーチャルホストは全体の設定を継承しません。継承を有効にするには、`rewrites`パラメータを指定し、`rewrite_inherit`パラメータを`true`に設定します。

``` puppet
apache::vhost { 'site.name.fdqn':
  …
  rewrites => [
    <rules>,
  ],
  rewrite_inherit => `true`,
}
```

> **注意**: この設定を有効にするには、`rewrites`パラメータが**必須**です。

バーチャルホストに以下のディレクティブが含まれている場合は、Apacheが全体の`Rewrite`ルールを有効にします。

``` ApacheConf
RewriteEngine On
RewriteOptions Inherit
```

[公式`mod_rewrite`ドキュメント](https://httpd.apache.org/docs/2.2/mod/mod_rewrite.html)のセクション"Rewriting in Virtual Hosts"を参照してください。

##### `scriptalias`

'/usr/scripts'などの、パス'/cgi-bin'のエイリアスとするCGIスクリプトのディレクトリを定義します。

デフォルト値: `undef`。

##### `scriptaliases`

> **注意**: このパラメータは廃止予定であり、`aliases`パラメータに置き換えられます。

ハッシュの配列をバーチャルホストに渡し、[`mod_alias`ドキュメント][`mod_alias`]に従ってScriptAliasまたはScriptAliasMatchステートメントのいずれかを作成します。

``` puppet
scriptaliases => [
  {
    alias => '/myscript',
    path  => '/usr/share/myscript',
  },
  {
    aliasmatch => '^/foo(.*)',
    path       => '/usr/share/fooscripts$1',
  },
  {
    aliasmatch => '^/bar/(.*)',
    path       => '/usr/share/bar/wrapper.sh/$1',
  },
  {
    alias => '/neatscript',
    path  => '/usr/share/neatscript',
  },
]
```

ScriptAliasおよびScriptAliasMatchディレクティブは、指定した順に作成されます。 [AliasおよびAliasMatch](#aliases)ディレクティブと同様、シャドーイングを避けるため、まず具体的なエイリアスを指定してから、全般的なものを指定してください。

##### `serveradmin`

エラーページの表示時にApacheが表示するEメールアドレスを指定します。 

デフォルト値: `undef`。

##### `serveraliases`

サイトの[ServerAliases](https://httpd.apache.org/docs/current/mod/core.html#serveralias)を設定します。

デフォルト値: '[]'。

##### `servername`

バーチャルホストに接続するホスト名に対応するサーバ名を設定します。

デフォルト値: リソースのタイトル。

##### `setenv`

HTTPDにより使用し、バーチャルホストの環境変数を設定します。

デフォルト値: '[]'。

例:

``` puppet
apache::vhost { 'setenv.example.com':
  setenv => ['SPECIAL_PATH /foo/bin'],
}
```

##### `setenvif`

HTTPDにより使用し、条件を用いてバーチャルホストの環境変数を設定します。

デフォルト値: '[]'。

##### `setenvifnocase`

HTTPDにより使用し、条件を用いてバーチャルホストの環境変数を設定します(大文字小文字を区別しないマッチング)。

デフォルト値: '[]'。

##### `suphp_*`

* `suphp_addhandler`
* `suphp_configpath`
* `suphp_engine`

[suPHP](http://suphp.org/DocumentationView.html?file=apache/CONFIG)によりバーチャルホストを設定します。

* `suphp_addhandler`。デフォルト値: RedHatおよびFreeBSDでは'php5-script'、DebianおよびGentooでは'x-httpd-php'。
* `suphp_configpath`。デフォルト値: RedHatおよびFreeBSDでは`undef`、DebianおよびGentooでは'/etc/php5/apache2'。
* `suphp_engine`。値: 'on'または'off'。デフォルト値: 'off'。

suPHPによるバーチャルホスト設定の例:

``` puppet
apache::vhost { 'suphp.example.com':
  port             => '80',
  docroot          => '/home/appuser/myphpapp',
  suphp_addhandler => 'x-httpd-php',
  suphp_engine     => 'on',
  suphp_configpath => '/etc/php5/apache2',
  directories      => { path => '/home/appuser/myphpapp',
    'suphp'        => { user => 'myappuser', group => 'myappgroup' },
  }
}
```

##### `vhost_name`

名前ベースのバーチャルホストを有効にします。バーチャルホストにIPではなくポートが割り当てられている場合は、バーチャルホスト名は'vhost_name:port'になります。バーチャルホストにIPもポートも割り当てられていない場合は、バーチャルホスト名はリソースのタイトルに設定されます。

デフォルト値: '*'。

##### `virtual_docroot`

同じ名前を持つディレクトリにマッピングされたワイルドカードエイリアスサブドメインにより、バーチャルホストを設定します。例えば、'http://example.com' would map to '/var/www/example.com'のようになります。

デフォルト値: `false`。

``` puppet
apache::vhost { 'subdomain.loc':
  vhost_name      => '*',
  port            => '80',
  virtual_docroot => '/var/www/%-2+',
  docroot         => '/var/www',
  serveraliases   => ['*.loc',],
}
```

##### `wsgi*`

* `wsgi_daemon_process`
* `wsgi_daemon_process_options`
* `wsgi_process_group`
* `wsgi_script_aliases`
* `wsgi_pass_authorization`

[WSGI](https://github.com/GrahamDumpleton/mod_wsgi)によりバーチャルホストを設定します。

* `wsgi_daemon_process`: WSGIデーモンの名前を設定するハッシュ。[特定のキー](http://modwsgi.readthedocs.org/en/latest/configuration-directives/WSGIDaemonProcess.html)を使用できます。デフォルト値: `undef`。
* `wsgi_daemon_process_options`。_オプション。_ デフォルト値: `undef`。
* `wsgi_process_group`: バーチャルホストが実行されるグループIDを設定します。デフォルト値: `undef`。
* `wsgi_script_aliases`: ファイルシステム.wsgiパスへのWebパスのハッシュにする必要があります。デフォルト値: `undef`。
* `wsgi_script_aliases_match`: ファイルシステム.wsgiパスへのWebパスの正規表現のハッシュにする必要があります。デフォルト値: `undef`。
* `wsgi_pass_authorization`: 'On'に設定すると、Apacheの代わりにWSGIアプリケーションを使って認証を処理します。詳細については、[mod_wsgi's WSGIPassAuthorizationドキュメント] (https://modwsgi.readthedocs.org/en/latest/configuration-directives/WSGIPassAuthorization.html)を参照してください。デフォルト値: `undef`、これにより、Apacheのデフォルト値である'Off'が使われます。
* `wsgi_chunked_request`: チャンク形式のリクエストのサポートを有効にします。デフォルト値: `undef`。

WSGIによるバーチャルホスト設定の例:

``` puppet
apache::vhost { 'wsgi.example.com':
  port                        => '80',
  docroot                     => '/var/www/pythonapp',
  wsgi_daemon_process         => 'wsgi',
  wsgi_daemon_process_options =>
    { processes    => '2',
      threads      => '15',
      display-name => '%{GROUP}',
     },
  wsgi_process_group          => 'wsgi',
  wsgi_script_aliases         => { '/' => '/var/www/demo.wsgi' },
  wsgi_chunked_request        => 'On',
}
```

#### `apache::vhost`のパラメータ`directories`

`apache::vhost`クラスの`directories`パラメータは、バーチャルホストにハッシュの配列を渡し、[Directory](https://httpd.apache.org/docs/current/mod/core.html#directory)、[File](https://httpd.apache.org/docs/current/mod/core.html#files)、[Location](https://httpd.apache.org/docs/current/mod/core.html#location)ディレクティブブロックを作成します。これらのブロックは、'< Directory /path/to/directory>...< /Directory>'の形式をとります。

`path`キーは、ディレクトリ、ファイル、ロケーションブロックのパスを設定します。この値は、'directory'、'files'、または'location'プロバイダのパスか、'directorymatch'、'filesmatch'、または 'locationmatch'プロバイダの正規表現でなければなりません。`directories`に渡される各ハッシュには、キーのひとつとして`path`が含まれていなければ**なりません**。

`provider`キーはオプションです。設定されていない場合、このキーのデフォルトは'directory'になります。値: 'directory'、'files'、'proxy'、'location'、'directorymatch'、'filesmatch'、'proxymatch'、'locationmatch'。`provider`を'directorymatch'に設定すると、 Apache設定ファイルでキーワード'DirectoryMatch'が使用されます。

`directories`の使用例:

``` puppet
apache::vhost { 'files.example.net':
  docroot     => '/var/www/files',
  directories => [
    { 'path'     => '/var/www/files',
      'provider' => 'files',
      'deny'     => 'from all',
     },
  ],
}
```

> **注意:** 少なくとも1つのディレクトリが`docroot`パラメータとマッチする必要があります。ディレクトリの宣言を開始すると、`apache::vhost`は必要なすべてのディレクトリブロックが宣言されるものと見なします。定義されない場合、`docroot`パラメータにマッチする1つのデフォルトディレクトリブロックが作成されます。

`directory`、`files`、または`location`ハッシュ内に、使用可能なハンドラを配置し、キーとして表す必要があります。以下のようになります。

``` puppet
apache::vhost { 'sample.example.net':
  docroot     => '/path/to/directory',
  directories => [ { path => '/path/to/directory', handler => value } ],
}
```

これらのハッシュで設定していないハンドラは、Puppet内で'undefined'と見なされ、バーチャルホストに追加されず、モジュールではデフォルト値が使われます。サポートされているハンドラは、次のとおりです。

##### `addhandlers`

[AddHandler](https://httpd.apache.org/docs/current/mod/mod_mime.html#addhandler)ディレクティブを設定します。これは、ファイル名の拡張子を指定されたハンドラにマッピングするものです。ハッシュのリストを使用し、`extensions`はハンドラによりマッピングされた拡張子を記述するために使用されます。`{ handler => 'handler-name', extensions => ['extension'] }`の形式をとります。

例:

``` puppet
apache::vhost { 'sample.example.net':
  docroot     => '/path/to/directory',
  directories => [
    { path        => '/path/to/directory',
      addhandlers => [{ handler => 'cgi-script', extensions => ['.cgi']}],
    },
  ],
}
```

##### `allow`

[Allow](https://httpd.apache.org/docs/2.2/mod/mod_authz_host.html#allow)ディレクティブを設定します。これは、ホスト名またはIPに基づく認証をグループ化するものです。**廃止予定:**このパラメータは、Apacheが変更されたため、廃止予定になっています。Apache 2.2以下でのみ機能します。1つのルールに対する単一の文字列としても、複数のルールに対する配列としても使用できます。

``` puppet
apache::vhost { 'sample.example.net':
  docroot     => '/path/to/directory',
  directories => [
    { path  => '/path/to/directory',
      allow => 'from example.org',
    },
  ],
}
```

##### `allow_override`

[.htaccess](https://httpd.apache.org/docs/current/mod/core.html#allowoverride)ファイルで許可されるディレクティブのタイプを設定します。配列を使用できます。

``` puppet
apache::vhost { 'sample.example.net':
  docroot      => '/path/to/directory',
  directories  => [
    { path           => '/path/to/directory',
      allow_override => ['AuthConfig', 'Indexes'],
    },
  ],
}
```

##### `auth_basic_authoritative`

[AuthBasicAuthoritative](https://httpd.apache.org/docs/current/mod/mod_auth_basic.html#authbasicauthoritative)の値を設定します。これにより、下位のApacheモジュールに権限と認証を渡すかどうかが決定されます。

##### `auth_basic_fake`

[AuthBasicFake](https://httpd.apache.org/docs/current/mod/mod_auth_basic.html#authbasicfake)の値を設定します。これにより、任意のディレクティブブロックに関する認証情報が静的に設定されます。

##### `auth_basic_provider`

[AuthBasicProvider](https://httpd.apache.org/docs/current/mod/mod_auth_basic.html#authbasicprovider)の値を設定します。これにより、任意のロケーションの認証プロバイダが設定されます。

##### `auth_digest_algorithm`

[AuthDigestAlgorithm](https://httpd.apache.org/docs/current/mod/mod_auth_digest.html#authdigestalgorithm)の値を設定します。これにより、チャレンジおよびレスポンスハッシュの計算に用いるアルゴリズムを選択します。

###### `auth_digest_domain`

[AuthDigestDomain](https://httpd.apache.org/docs/current/mod/mod_auth_digest.html#authdigestdomain)の値を設定します。これにより、ダイジェスト認証に関して、同じ保護スペースで1つまたは複数のURIを指定できます。

##### `auth_digest_nonce_lifetime`

[AuthDigestNonceLifetime](https://httpd.apache.org/docs/current/mod/mod_auth_digest.html#authdigestnoncelifetime)の値を設定します。これにより、サーバのノンスが有効になる長さを制御します。

##### `auth_digest_provider`

[AuthDigestProvider](https://httpd.apache.org/docs/current/mod/mod_auth_digest.html#authdigestprovider)の値を設定します。これにより、任意のロケーションに関する認証プロバイダを設定します。

##### `auth_digest_qop`

[AuthDigestQop](https://httpd.apache.org/docs/current/mod/mod_auth_digest.html#authdigestqop)の値を設定します。これにより、ダイジェスト認証で用いる保護品質を決定します。

##### `auth_digest_shmem_size`

[AuthAuthDigestShmemSize](https://httpd.apache.org/docs/current/mod/mod_auth_digest.html#authdigestshmemsize)の値を設定します。これにより、クライアントの追跡に関して、サーバに割り当てられる共通メモリの量を定義します。

##### `auth_group_file`

[AuthGroupFile](https://httpd.apache.org/docs/current/mod/mod_authz_groupfile.html#authgroupfile)の値を設定します。これにより、認証に関して、ユーザグループのリストを含むテキストファイルの名前を設定します。

##### `auth_name`

[AuthName](https://httpd.apache.org/docs/current/mod/mod_authn_core.html#authname)の値を設定します。これにより、認証領域の名前を設定します。

##### `auth_require`

アクセスを許可するのに必要なエンティティ名を設定します。詳細については、[Require](https://httpd.apache.org/docs/current/mod/mod_authz_host.html#requiredirectives)を参照してください。

##### `auth_type`

[AuthType](https://httpd.apache.org/docs/current/mod/mod_authn_core.html#authtype)の値を設定します。これにより、ユーザ認証のタイプをガイドします。

##### `auth_user_file`

[AuthUserFile](https://httpd.apache.org/docs/current/mod/mod_authn_file.html#authuserfile)の値を設定します。これにより、認証に関するユーザ/パスワードを含むテキストファイルの名前を設定します。

##### `auth_merging`

[AuthMerging](https://httpd.apache.org/docs/current/mod/mod_authz_core.html#authmerging)の値を設定します。これにより、認証ロジックを組み合わせるかどうかを決定します。

##### `auth_ldap_url`

[AuthLDAPURL](https://httpd.apache.org/docs/current/mod/mod_authnz_ldap.html#authldapurl)の値を設定します。これにより、AuthBasicProvider 'ldap'を使用する場合のLDAPサーバのURLを決定します。

##### `auth_ldap_bind_dn`

[AuthLDAPBindDN](https://httpd.apache.org/docs/current/mod/mod_authnz_ldap.html#authldapbinddn)の値を設定します。これにより、AuthBasicProvider 'ldap'を使用する場合に、エントリの検索時にLDAPサーバにバインドするオプションのDNを使用できるようになります。

##### `auth_ldap_bind_password`

[AuthLDAPBindPassword](https://httpd.apache.org/docs/current/mod/mod_authnz_ldap.html#authldapbindpassword)の値を設定します。これにより、AuthBasicProvider 'ldap'を使用する場合に、バインドDNとともに用いるオプションのバインドパスワードを使用できるようになります。

##### `auth_ldap_group_attribute`

[AuthLDAPGroupAttribute](https://httpd.apache.org/docs/current/mod/mod_authnz_ldap.html#authldapgroupattribute)の値の配列。ldapグループ内のユーザメンバーの確認に使用するLDAP属性を指定します。

デフォルト値: "member"および "uniquemember"。

##### `auth_ldap_group_attribute_is_dn`

[AuthLDAPGroupAttributeIsDN](https://httpd.apache.org/docs/current/mod/mod_authnz_ldap.html#authldapgroupattributeisdn)の値を設定し、ldapグループのメンバーにDNかシンプルなユーザ名のどちらを使用するかを指定します。onに設定すると、グループメンバーシップの確認時に、クライアントユーザ名の識別名が使用されます。そうでない場合は、ユーザ名が使われます。有効な値は"on"か"off"です。

##### `custom_fragment`

カスタム設定ディレクティブの文字列を渡し、ディレクトリ設定の最後に配置します。

``` puppet
apache::vhost { 'monitor':
  …
  directories => [
    {
      path => '/path/to/directory',
      custom_fragment => '
<Location /balancer-manager>
  SetHandler balancer-manager
  Order allow,deny
  Allow from all
</Location>
<Location /server-status>
  SetHandler server-status
  Order allow,deny
  Allow from all
</Location>
ProxyStatus On',
    },
  ]
}
```

##### `dav`

[Dav](http://httpd.apache.org/docs/current/mod/mod_dav.html#dav)の値を設定します。これにより、WebDAV HTTPメソッドを有効にするかどうかを決定します。値としては、'On'、'Off'、またはプロバイダの名前を使用できます。'On'に設定すると、`mod_dav_fs`モジュールにより実装されているデフォルトのファイルシステムプロバイダが有効になります。 

##### `dav_depth_infinity`

[DavDepthInfinity](http://httpd.apache.org/docs/current/mod/mod_dav.html#davdepthinfinity)の値を設定します。これは、`Depth: Infinity`ヘッダを持つ`PROPFIND`リクエストの処理を有効にするのに使用されます。

##### `dav_min_timeout`

[DavMinTimeout](http://httpd.apache.org/docs/current/mod/mod_dav.html#davmintimeout)の値を設定します。DAVリソースでサーバがロック状態を維持する時間(秒数)を指定します。

##### `deny`

[Deny](https://httpd.apache.org/docs/2.2/mod/mod_authz_host.html#deny)ディレクティブを設定し、サーバへのアクセスを否定するホストを指定します。**廃止予定:** このパラメータは、Apacheが変更されたため、廃止予定になっています。Apache 2.2以下でのみ機能します。1つのルールに対する単一の文字列としても、複数のルールに対する配列としても使用できます。

``` puppet
apache::vhost { 'sample.example.net':
  docroot     => '/path/to/directory',
  directories => [
    { path => '/path/to/directory',
      deny => 'from example.org',
    },
  ],
}
```

##### `error_documents`

ディレクトリの[ErrorDocument](https://httpd.apache.org/docs/current/mod/core.html#errordocument)設定をオーバーライドするハッシュの配列。

``` puppet
apache::vhost { 'sample.example.net':
  directories => [
    { path            => '/srv/www',
      error_documents => [
        { 'error_code' => '503',
          'document'   => '/service-unavail',
        },
      ],
    },
  ],
}
```

##### `ext_filter_options`

[ExtFilterOptions](https://httpd.apache.org/docs/current/mod/mod_ext_filter.html)ディレクティブを設定します。
このディレクティブを使用する前に、`class { 'apache::mod::ext_filter': }`を宣言する必要があります。

``` puppet
apache::vhost { 'filter.example.org':
  docroot     => '/var/www/filter',
  directories => [
    { path               => '/var/www/filter',
      ext_filter_options => 'LogStderr Onfail=abort',
    },
  ],
}
```

##### `geoip_enable`

[GeoIPEnable](http://dev.maxmind.com/geoip/legacy/mod_geoip2/#Configuration)ディレクティブを設定します。
このディレクティブを使用する前に、`class {'apache::mod::geoip': }`を宣言する必要があります。

``` puppet
apache::vhost { 'first.example.com':
  docroot     => '/var/www/first',
  directories => [
    { path         => '/var/www/first',
      geoip_enable => `true`,
    },
  ],
}
```

##### `headers`

[Header](https://httpd.apache.org/docs/current/mod/mod_headers.html#header)ディレクティブの行を追加します。

``` puppet
apache::vhost { 'sample.example.net':
  docroot     => '/path/to/directory',
  directories => {
    path    => '/path/to/directory',
    headers => 'Set X-Robots-Tag "noindex, noarchive, nosnippet"',
  },
}
```

##### `index_options`

[ディレクトリインデキシング](https://httpd.apache.org/docs/current/mod/mod_autoindex.html#indexoptions)の設定を可能にします。

``` puppet
apache::vhost { 'sample.example.net':
  docroot     => '/path/to/directory',
  directories => [
    { path           => '/path/to/directory',
      directoryindex => 'disabled', # this is needed on Apache 2.4 or mod_autoindex doesn't work
      options        => ['Indexes','FollowSymLinks','MultiViews'],
      index_options  => ['IgnoreCase', 'FancyIndexing', 'FoldersFirst', 'NameWidth=*', 'DescriptionWidth=*', 'SuppressHTMLPreamble'],
    },
  ],
}
```

##### `index_order_default`

ディレクトリインデックスの[デフォルトの順序付け](https://httpd.apache.org/docs/current/mod/mod_autoindex.html#indexorderdefault)を設定します。

``` puppet
apache::vhost { 'sample.example.net':
  docroot     => '/path/to/directory',
  directories => [
    { path                => '/path/to/directory',
      order               => 'Allow,Deny',
      index_order_default => ['Descending', 'Date'],
    },
  ],
}
```

###### `index_style_sheet`

[IndexStyleSheet](https://httpd.apache.org/docs/current/mod/mod_autoindex.html#indexstylesheet)を設定します。これにより、ディレクトリインデックスにCSSスタイルシートが追加されます。

``` puppet
apache::vhost { 'sample.example.net':
  docroot     => '/path/to/directory',
  directories => [
    { path              => '/path/to/directory',
      options           => ['Indexes','FollowSymLinks','MultiViews'],
      index_options     => ['FancyIndexing'],
      index_style_sheet => '/styles/style.css',
    },
  ],
}
```

##### `limit`

ディレクトリブロック内に[Limit](https://httpd.apache.org/docs/current/mod/core.html#limit)ブロックを作成します。`require`ディレクティブを含めることもできます。

``` puppet
apache::vhost { 'sample.example.net':
  docroot     => '/path/to/docroot',
  directories => [
    { path     => '/',
      provider => 'location',
      limit    => [
        { methods => 'GET HEAD',
          require => ['valid-user']
        },
      ],
    },
  ],
}
```

##### `limit_except`

ディレクトリブロック内に[LimitExcept](https://httpd.apache.org/docs/current/mod/core.html#limitexcept)ブロックを作成します。`require`ディレクティブを含めることもできます。

``` puppet
apache::vhost { 'sample.example.net':
  docroot     => '/path/to/docroot',
  directories => [
    { path         => '/',
      provider     => 'location',
      limit_except => [
        { methods => 'GET HEAD',
          require => ['valid-user']
        },
      ],
    },
  ],
}
```

##### `mellon_enable`

[MellonEnable][`mod_auth_mellon`]ディレクトリを設定し、 [`mod_auth_mellon`][]を有効にします。[`apache::mod::auth_mellon`][]を使って`mod_auth_mellon`をインストールできます。

``` puppet
apache::vhost { 'sample.example.net':
  docroot     => '/path/to/directory',
  directories => [
    { path                       => '/',
      provider                   => 'directory',
      mellon_enable              => 'info',
      mellon_sp_private_key_file => '/etc/certs/${::fqdn}.key',
      mellon_endpoint_path       => '/mellon',
      mellon_set_env_no_prefix   => { 'ADFS_GROUP' => 'http://schemas.xmlsoap.org/claims/Group',
                                      'ADFS_EMAIL' => 'http://schemas.xmlsoap.org/claims/EmailAddress', },
      mellon_user => 'ADFS_LOGIN',
    },
    { path          => '/protected',
      provider      => 'location',
      mellon_enable => 'auth',
      auth_type     => 'Mellon',
      auth_require  => 'valid-user',
      mellon_cond   => ['ADFS_LOGIN userA [MAP]','ADFS_LOGIN userB [MAP]'],
    },
  ]
}
```

関連するパラメータは、`mod_auth_mellon`ディレクティブの名前に従います。

- `mellon_cond`: アクセスを許可するために満たす必要のあるmellon条件の配列をとり、配列内の各アイテムについて [MellonCond][`mod_auth_mellon`]ディレクティブを作成します。
- `mellon_endpoint_path`: [MellonEndpointPath][`mod_auth_mellon`]を設定し、mellonエンドポイントパスを設定します。
- `mellon_sp_metadata_file`: SPメタデータファイルの[MellonSPMetadataFile][`mod_auth_mellon`]ロケーションを設定します。
- `mellon_idp_metadata_file`: IDPメタデータファイルの[MellonIDPMetadataFile][`mod_auth_mellon`]ロケーションを設定します。
- `mellon_saml_rsponse_dump`: [MellonSamlResponseDump][`mod_auth_mellon`]ディレクティブを設定し、SAMLのデバッグを有効にします。
- `mellon_set_env_no_prefix`:環境変数にマッピングする属性名のハッシュに関する [MellonSetEnvNoPrefix][`mod_auth_mellon`]ディレクティブを
設定します。
- `mellon_sp_private_key_file`: サービスプロバイダのプライベートキー保存場所に関する[MellonSPPrivateKeyFile][`mod_auth_mellon`]ディレクティブを設定します。
- `mellon_sp_cert_file`: サービスプロバイダの公開キー保存場所に関する[MellonSPCertFile][`mod_auth_mellon`]ディレクティブを設定します。
- `mellon_user`: ユーザ名に関して使用する[MellonUser][`mod_auth_mellon`]属性を設定します。

##### `options`

任意のディレクトリブロックに関する[オプション](https://httpd.apache.org/docs/current/mod/core.html#options)をリスト化します。

``` puppet
apache::vhost { 'sample.example.net':
  docroot     => '/path/to/directory',
  directories => [
    { path    => '/path/to/directory',
      options => ['Indexes','FollowSymLinks','MultiViews'],
    },
  ],
}
```

##### `order`

[Apacheコアドキュメント](https://httpd.apache.org/docs/2.2/mod/mod_authz_host.html#order)に従い、AllowおよびDenyステートメントの処理順序を設定します。**廃止予定:** このパラメータは、Apacheが変更されたため、廃止予定になっています。Apache 2.2以下でのみ機能します。

``` puppet
apache::vhost { 'sample.example.net':
  docroot     => '/path/to/directory',
  directories => [
    { path  => '/path/to/directory',
      order => 'Allow,Deny',
    },
  ],
}
```

##### `passenger_enabled`

[PassengerEnabled](http://www.modrails.com/documentation/Users%20guide%20Apache.html#PassengerEnabled)ディレクティブの値を'on'または'off'に設定します。`apache::mod::passenger`を含める必要があります。

``` puppet
apache::vhost { 'sample.example.net':
  docroot     => '/path/to/directory',
  directories => [
    { path              => '/path/to/directory',
      passenger_enabled => 'on',
    },
  ],
}
```

> **注意:** PassengerEnabledディレクティブをPassengerHighPerformanceディレクティブとともに使用すると、[問題](http://www.conandalton.net/2010/06/passengerenabled-off-not-working.html)が生じます。

##### `php_value`および`php_flag`

`php_value`はディレクトリの値を設定し、`php_flag`はブーリアンを用いてディレクトリを設定します。詳細は[こちら](http://php.net/manual/en/configuration.changes.php)で確認できます。

##### `php_admin_value`および`php_admin_flag`

`php_admin_value`はディレクトリの値を設定し、`php_admin_flag`はブーリアンを用いてディレクトリを設定します。詳細は[こちら](http://php.net/manual/en/configuration.changes.php)で確認できます。


##### `require`


[Apache Authzドキュメント](https://httpd.apache.org/docs/current/mod/mod_authz_core.html#require)に従い、`Require`ディレクティブを設定します。`require`が設定されていない場合、`Require all granted`がデフォルトになります。

``` puppet
apache::vhost { 'sample.example.net':
  docroot     => '/path/to/directory',
  directories => [
    { path    => '/path/to/directory',
      require => 'ip 10.17.42.23',
    }
  ],
}
```

より複雑な要件設定が必要な場合、apache >= 2.4では[RequireAll](https://httpd.apache.org/docs/2.4/mod/mod_authz_core.html#requireall)、[RequireNone](https://httpd.apache.org/docs/2.4/mod/mod_authz_core.html#requirenone)または[RequireAny](https://httpd.apache.org/docs/2.4/mod/mod_authz_core.html#requireany)ディレクティブを使用できます。'any'、'none'、'all'のみをサポートする(その他の値は無視されます)'enforce'キーを使うと、以下のように設定できます。

``` puppet
apache::vhost { 'sample.example.net':
  docroot     => '/path/to/directory',
  directories => [
    { path    => '/path/to/directory',
      require => {
        enforce  => 'any',
        requires => [
          'ip 1.2.3.4',
          'not host host.example.com',
          'user xyz',
        ],
      },
    },
  ],
}
```

`require`を`unmanaged`に設定すると、何も設定されません。これは、カスタムフラグメントで扱われる複雑な認証/権限要件に役立ちます。

``` puppet
apache::vhost { 'sample.example.net':
  docroot     => '/path/to/directory',
  directories => [
    { path    => '/path/to/directory',
      require => 'unmanaged',
    }
  ],
}
```



##### `satisfy`

[Apacheコアドキュメント](https://httpd.apache.org/docs/2.2/mod/core.html#satisfy)に従い、`Satisfy`ディレクティブを設定します。**廃止予定:** このパラメータは、Apacheが変更されたため、廃止予定になっています。Apache 2.2以下でのみ機能します。

``` puppet
apache::vhost { 'sample.example.net':
  docroot     => '/path/to/directory',
  directories => [
    { path    => '/path/to/directory',
      satisfy => 'Any',
    }
  ],
}
```

##### `sethandler`

[Apache Coreドキュメント](https://httpd.apache.org/docs/2.2/mod/core.html#sethandler)に従い、`SetHandler`ディレクティブを設定します。

``` puppet
apache::vhost { 'sample.example.net':
  docroot     => '/path/to/directory',
  directories => [
    { path       => '/path/to/directory',
      sethandler => 'None',
    }
  ],
}
```

##### `set_output_filter`

[Apache Coreドキュメント](https://httpd.apache.org/docs/current/mod/core.html#setoutputfilter)に従い、`SetOutputFilter`ディレクティブを設定します。

``` puppet
apache::vhost{ 'filter.example.net':
  docroot     => '/path/to/directory',
  directories => [
    { path              => '/path/to/directory',
      set_output_filter => puppetdb-strip-resource-params,
    },
  ],
}
```

##### `rewrites`

バーチャルホストディレクトリ内でURL [`rewrites`](#rewrites)ルールを作成します。ハッシュの配列が求められます。ハッシュキーは'comment'、'rewrite_base'、'rewrite_cond'または'rewrite_rule'のいずれかにすることができます。

``` puppet
apache::vhost { 'secure.example.net':
  docroot     => '/path/to/directory',
  directories => [
    { path        => '/path/to/directory',
      rewrites => [ { comment      => 'Permalink Rewrites',
                      rewrite_base => '/'
                    },
                    { rewrite_rule => [ '^index\.php$ - [L]' ]
                    },
                    { rewrite_cond => [ '%{REQUEST_FILENAME} !-f',
                                        '%{REQUEST_FILENAME} !-d',
                                      ],
                      rewrite_rule => [ '. /index.php [L]' ],
                    }
                  ],
    },
  ],
}
```

> **注意**: ディレクトリにリライトを含める場合は、`apache::mod::rewrite`も含めてください。また、バーチャルホストのディレクトリのリライト設定ではなく、`apache::vhost`の`rewrites`パラメータを用いたリライトの設定を考慮してください。

##### `shib_request_settings`

アプリケーションリクエストに関して、有効なコンテンツ設定の設定または変更を可能にします。このコマンドは、次の2つのパラメータをとります: コンテンツ設定の名前、およびそれについて設定する値。有効な設定については、Shibboleth [コンテンツ設定ドキュメント](https://wiki.shibboleth.net/confluence/display/SHIB2/NativeSPContentSettings)を参照してください。このキーは、`apache::mod::shib`が定義されていない場合は無効になります。詳細については、[`mod_shib`ドキュメント](https://wiki.shibboleth.net/confluence/display/SHIB2/NativeSPApacheConfig#NativeSPApacheConfig-Server/VirtualHostOptions)を参照してください。

``` puppet
apache::vhost { 'secure.example.net':
  docroot     => '/path/to/directory',
  directories => [
    { path                  => '/path/to/directory',
      shib_request_settings => { 'requiresession' => 'On' },
      shib_use_headers      => 'On',
    },
  ],
}
```

##### `shib_use_headers`

'On'に設定すると、アプリケーションに属性を公開するリクエストヘッダの使用がオンになります。このキーの値は'On'または'Off'です。デフォルト値は'Off'です。このキーは、`apache::mod::shib`が定義されていない場合は無効になります。詳細については、[`mod_shib`ドキュメント](https://wiki.shibboleth.net/confluence/display/SHIB2/NativeSPApacheConfig#NativeSPApacheConfig-Server/VirtualHostOptions)を参照してください。

##### `ssl_options`

[SSLOptions](https://httpd.apache.org/docs/current/mod/mod_ssl.html#ssloptions)の文字列またはリスト。これにより、SSLエンジンのランタイムオプションが設定されます。このハンドラは、バーチャルホストの親ブロック内のSSLOptionsセットよりも優先されます。

``` puppet
apache::vhost { 'secure.example.net':
  docroot     => '/path/to/directory',
  directories => [
    { path        => '/path/to/directory',
      ssl_options => '+ExportCertData',
    },
    { path        => '/path/to/different/dir',
      ssl_options => [ '-StdEnvVars', '+ExportCertData'],
    },
  ],
}
```

##### `suphp`

[suPHP_UserGroup](http://www.suphp.org/DocumentationView.html?file=apache/CONFIG)設定に関する'user'および'group'キーを含むハッシュ。バーチャルホスト宣言で`suphp_engine => on`とともに使用する必要があり、`directories`内でのみ渡すことができます。

``` puppet
apache::vhost { 'secure.example.net':
  docroot     => '/path/to/directory',
  directories => [
    { path  => '/path/to/directory',
      suphp => {
        user  => 'myappuser',
        group => 'myappgroup',
      },
    },
  ],
}
```
##### `additional_includes`

バーチャルホストディレクトリ内にある追加の静的な固有のApache設定ファイルのパスを指定します。値: 文字列パスの配列。

``` puppet
apache::vhost { 'sample.example.net':
  docroot     => '/path/to/directory',
  directories => [
    { path  => '/path/to/different/dir',
      additional_includes => [ '/custom/path/includes', '/custom/path/another_includes', ],
    },
  ],
}
```

#### `apache::vhost`のSSLパラメータ

`::vhost`のすべてのSSLパラメータは、基本の`apache`クラスで設定された値がデフォルトになります。以下のパラメータを使えば、特定のバーチャルホストに関する個別のSSL設定を調整できます。

##### `ssl`

バーチャルホストのSSLを有効にします。SSLバーチャルホストはHTTPSクエリにのみ応答します。値: ブーリアン。

デフォルト値: `false`。

##### `ssl_ca`

使用するSSL認証局を指定して、認証に使用するクライアントの証明書を検証します。これを使用するには、`ssl_verify_client`も設定する必要があります。

デフォルト値: `undef`。

##### `ssl_cert`

SSL証明書を指定します。

デフォルト値: オペレーティングシステムによって異なります。

* RedHat: '/etc/pki/tls/certs/localhost.crt'
* Debian: '/etc/ssl/certs/ssl-cert-snakeoil.pem'
* FreeBSD: '/usr/local/etc/apache22/server.crt'
* Gentoo: '/etc/ssl/apache2/server.crt'

##### `ssl_protocol`

[SSLProtocol](https://httpd.apache.org/docs/current/mod/mod_ssl.html#sslprotocol)を指定します。許可されるプロトコルの配列またはスペースで区切った文字列が求められます。

デフォルト値: 'all'、'-SSLv2'、'-SSLv3'。

##### `ssl_cipher`

[SSLCipherSuite](https://httpd.apache.org/docs/current/mod/mod_ssl.html#sslciphersuite)を指定します。

デフォルト値: 'HIGH:MEDIUM:!aNULL:!MD5'。

##### `ssl_honorcipherorder`

[SSLHonorCipherOrder](https://httpd.apache.org/docs/current/mod/mod_ssl.html#sslhonorcipherorder)を指定し、クライアントの優先順ではなくサーバの優先順をApacheに使用させます。値:

値: ブーリアン、'on'、'off'。

デフォルト値: `true`。

##### `ssl_certs_dir`

SSL認証ディレクトリの場所を指定してクライアントの証明書を検証します。`ssl_verify_client`も設定されていない限り使用されません(下記参照)。

デフォルト: undef

##### `ssl_chain`

SSLチェーンを指定します。このデフォルト値は設定しなくても機能しますが、本稼働環境で使用する前に、固有の証明書情報により基本の`apache`クラス内で更新する必要があります。

デフォルト値: `undef`。

##### `ssl_crl`

使用する証明書失効リストを指定します。(このデフォルト値は設定しなくても機能しますが、本稼働環境で使用する前に、固有の証明書情報により基本の`apache`クラス内で更新する必要があります。)

デフォルト値: `undef`。

##### `ssl_crl_path`

証明書失効リストの保存場所を指定して、クライアント認証の証明書を検証します(このデフォルト値は設定しなくても機能しますが、本稼働環境で使用する前に、固有の証明書情報により基本の`apache`クラス内で更新する必要があります)。

デフォルト値: `undef`。

##### `ssl_crl_check`

[SSLCARevocationCheckディレクティブ](https://httpd.apache.org/docs/current/mod/mod_ssl.html#sslcarevocationcheck)により、SSLクライアント認証の証明書失効チェックレベルを設定します。このデフォルト値は設定しなくても機能しますが、本稼働環境でCRLを使用する際に指定する必要があります。Apache 2.4以上にのみ適用され、それ以前のバージョンではこの値は無視されます。

デフォルト値: `undef`。

##### `ssl_key`

SSLキーを指定します。

デフォルト値はオペレーティングシステムによって異なります。このデフォルト値は設定しなくても機能しますが、本稼働環境で使用する前に、固有の証明書情報により基本の`apache`クラス内で更新する必要があります。

* RedHat: '/etc/pki/tls/private/localhost.key'
* Debian: '/etc/ssl/private/ssl-cert-snakeoil.key'
* FreeBSD: '/usr/local/etc/apache22/server.key'
* Gentoo: '/etc/ssl/apache2/server.key'

##### `ssl_verify_client`

[SSLVerifyClient](https://httpd.apache.org/docs/current/mod/mod_ssl.html#sslverifyclient)ディレクティブを設定します。これにより、クライアント認証に関する証明書確認レベルが設定されます。

``` puppet
apache::vhost { 'sample.example.net':
  …
  ssl_verify_client => 'optional',
}
```

値: 'none'、'optional'、'require'、'optional_no_ca'。

デフォルト値: `undef`。


##### `ssl_verify_depth`

[SSLVerifyDepth](https://httpd.apache.org/docs/current/mod/mod_ssl.html#sslverifydepth)ディレクティブを設定します。これにより、クライアント認証確認におけるCA証明書の最大深さが指定されます。これを有効にするには、`ssl_verify_client`を設定する必要があります。

``` puppet
apache::vhost { 'sample.example.net':
  …
  ssl_verify_client => 'require',
  ssl_verify_depth => 1,
}
```

デフォルト値: `undef`。

##### `ssl_proxy_protocol`

[SSLProxyProtocol](https://httpd.apache.org/docs/current/mod/mod_ssl.html#sslproxyprotocol)ディレクティブを設定します。これにより、プロキシに関するサーバ環境を確立する際に`mod_ssl`が使用すべきSSLプロトコルフレーバーを制御します。提示されたプロトコルのうちの1つのみを使用しているサーバに接続します。

デフォルト値: `undef`。

##### `ssl_proxy_verify`

[SSLProxyVerify](https://httpd.apache.org/docs/current/mod/mod_ssl.html#sslproxyverify)ディレクティブを設定します。これにより、リクエストをリモートSSLサーバに転送するようにプロキシが設定されている場合のリモートサーバの証明書確認を設定します。

デフォルト値: `undef`。

##### `ssl_proxy_verify_depth`

[SSLProxyVerifyDepth](https://httpd.apache.org/docs/current/mod/mod_ssl.html#sslproxyverifydepth)ディレクティブを設定します。これにより、リモートサーバに有効な証明書がないと判断するにあたり、mod_sslが行う確認の深さを設定します。

深さ0では、自己署名リモートサーバ証明書のみが許可されます。デフォルトの深さ 1では、リモートサーバ証明書を自己署名にすることも、サーバが直接知っているCAにより署名することもできます。

デフォルト値: `undef`。　

##### `ssl_proxy_ca_cert`

[SSLProxyCACertificateFile](https://httpd.apache.org/docs/current/mod/mod_ssl.html#sslproxycacertificatefile)ディレクティブを設定します。これにより、やりとりするリモートサーバに関する認証局(CA)の証明書を集められるオールインワンファイルを指定します。これはリモートサーバ認証に用いられます。このファイルは、PEMエンコード証明書ファイルを優先順に連結したものにする必要があります。

デフォルト値: `undef`。　

##### `ssl_proxy_machine_cert`

[SSLProxyMachineCertificateFile](https://httpd.apache.org/docs/current/mod/mod_ssl.html#sslproxymachinecertificatefile)ディレクティブを設定します。これにより、このサーバがリモートサーバの認証に用いる証明書とキーを保存するオールインワンファイルを指定します。このファイルは、PEMエンコード証明書ファイルを優先順に連結したものにする必要があります。 

``` puppet
apache::vhost { 'sample.example.net':
  …
  ssl_proxy_machine_cert => '/etc/httpd/ssl/client_certificate.pem',
}
```

デフォルト値: `undef`。　

##### `ssl_proxy_check_peer_cn`

[SSLProxyCheckPeerCN](https://httpd.apache.org/docs/current/mod/mod_ssl.html#sslproxycheckpeercn)ディレクティブを設定します。これにより、リモートサーバの証明書のCNフィールドをリクエストURLのホスト名と比較するかどうかを指定します。 

値: 'on'、'off'。　

デフォルト値: `undef`。　

##### `ssl_proxy_check_peer_name`

[SSLProxyCheckPeerName](https://httpd.apache.org/docs/current/mod/mod_ssl.html#sslproxycheckpeername)ディレクティブを設定します。これにより、リモートサーバの証明書のCNフィールドをリクエストURLのホスト名と比較するかどうかを決定します。

値: 'on'、'off'。　

デフォルト値: `undef`。　

##### `ssl_proxy_check_peer_expire`

[SSLProxyCheckPeerExpire](https://httpd.apache.org/docs/current/mod/mod_ssl.html#sslproxycheckpeerexpire)ディレクティブを設定します。これにより、リモートサーバの証明書の有効期限をチェックするかどうかを指定します。

値: 'on'、'off'。　

デフォルト値: `undef`。　

##### `ssl_options`

[SSLOptions](https://httpd.apache.org/docs/current/mod/mod_ssl.html#ssloptions)ディレクティブを設定します。これにより、各種のSSLエンジンのランタイムオプションを設定します。これは任意のバーチャルホスト全体の設定で、文字列にすることも配列にすることもできます。

文字列:

``` puppet
apache::vhost { 'sample.example.net':
  …
  ssl_options => '+ExportCertData',
}
```

配列:

``` puppet
apache::vhost { 'sample.example.net':
  …
  ssl_options => [ '+StrictRequire', '+ExportCertData' ],
}
```

デフォルト値: `undef`。

##### `ssl_openssl_conf_cmd`

[SSLOpenSSLConfCmd](https://httpd.apache.org/docs/current/mod/mod_ssl.html#sslopensslconfcmd)ディレクティブを設定します。これにより、OpenSSLパラメータを直接設定できます。

デフォルト値: `undef`。　

##### `ssl_proxyengine`

[SSLProxyEngine](https://httpd.apache.org/docs/current/mod/mod_ssl.html#sslproxyengine)を使用するかどうかを指定します。

ブーリアン。

デフォルト値: `true`。

##### `ssl_stapling`

[SSLUseStapling](http://httpd.apache.org/docs/current/mod/mod_ssl.html#sslusestapling)を使用するかどうかを指定します。デフォルトでは、全体で設定されているものを使用します。

このパラメータはApache 2.4以上にのみ適用され、それ以前のバージョンでは無視されます。　

ブーリアンまたは`undef`。

デフォルト値: `undef`。　

##### `ssl_stapling_timeout`

[SSLStaplingResponderTimeout](http://httpd.apache.org/docs/current/mod/mod_ssl.html#sslstaplingrespondertimeout)ディレクティブの設定に使用できます。

このパラメータはApache 2.4以上にのみ適用され、それ以前のバージョンでは無視されます。　

デフォルト値: なし。　

##### `ssl_stapling_return_errors`

[SSLStaplingReturnResponderErrors](http://httpd.apache.org/docs/current/mod/mod_ssl.html#sslstaplingreturnrespondererrors)ディレクティブの設定に使用できます。

このパラメータはApache 2.4以上にのみ適用され、それ以前のバージョンでは無視されます。　

デフォルト値: なし。　

#### 定義タイプ: FastCGIサーバ

このタイプは、mod\_fastcgiとともに使用します。特定のファイルタイプを扱う1つまたは複数の外部FastCGIサーバを定義することができます。

** 注意 ** Ubuntu 10.04+では、マルチバースリポジトリを手動で有効にする必要があります。

例:

``` puppet
apache::fastcgi::server { 'php':
  host        => '127.0.0.1:9000',
  timeout     => 15,
  flush       => `false`,
  faux_path   => '/var/www/php.fcgi',
  fcgi_alias  => '/php.fcgi',
  file_type   => 'application/x-httpd-php',
  pass_header => ''
}
```

その後、バーチャルホスト内で、上で指定したfastcgiサーバで扱う特定のファイルタイプを設定することができます。

``` puppet
apache::vhost { 'www':
  ...
  custom_fragment => 'AddType application/x-httpd-php .php'
  ...
}
```

##### `host`

FastCGIサーバのホスト名またはIPアドレスおよびTCPポート番号(1-65535)。

unixソケットを渡すこともできます。

``` puppet
apache::fastcgi::server { 'php':
  host        => '/var/run/fcgi.sock',
}
```

##### `timeout`

リクエストが中止され、(エラーLogLevel)にイベントが記録されるまでに、FastCGIアプリケーションが非アクティブの状態で待機する秒数。この非アクティブタイマーは、FastCGIアプリケーションとの接続が待機中の場合のみ適用されます。アプリケーションの待ち行列に入ったリクエストに対して、時間内に記述やフラッシュによる応答がないと、リクエストは中止されます。アプリケーションとの通信が完了したものの、クライアントとの通信が完了しなかった(応答がバッファリングされた)場合は、タイムアウトは適用されません。

##### `flush`

アプリケーションから受信したデータを、強制的にクライアントに書き込みます。デフォルトでは、アプリケーションをできるだけ早くフリーな状態にするために、`mod_fastcgi`はデータをバッファリングします。 

##### `faux_path`

ローカルファイルシステムに存在する必要はありません。Apacheがこのファイル名に解読するURIは、この外部FastCGIアプリケーションにより処理されます。

##### `alias`

一意のエイリアス。 アクションとFastCGIサーバをリンクさせるために内部で用いられます。

##### `file_type`

FastCGIサーバにより処理するファイルのMIMEタイプ。

##### `pass_header`

リクエスト環境で渡されるHTTPリクエストヘッダの名前。このオプションにより、通常はCGI環境で利用できないヘッダコンテンツ(認証など)が利用できるようになります。

#### 定義タイプ: `apache::vhost::custom`

`apache::vhost::custom`定義タイプは、 `apache::custom_config`定義タイプのシンラッパーで、Apacheにおいてバーチャルホストディレクトリに固有のデフォルト設定の一部をオーバーライドします。 

**`apache::vhost::custom`内のパラメータ**:

##### `content`

設定ファイルのコンテンツを設定します。

##### `ensure`

バーチャルホストファイルが存在するかどうかを指定します。

値: 'absent'、'present'。　

デフォルト値: 'present'。　

##### `priority`

Apache HTTPD VirtualHost設定ファイルに関する相対的なロード順序を設定します。

デフォルト値: '25'。

##### `verify_config`

Apacheサービスに通知する前に設定ファイルのバリデーションを行うかどうかを指定します。

ブーリアン。

デフォルト値: `true`。

### プライベート定義タイプ

#### 定義タイプ: `apache::peruser::multiplexer`　

この定義タイプは、Apacheモジュールにクラスがあるかどうかを確認します。クラスがある場合は、そのクラスを含めます。ない場合は、モジュール名を[`apache::mod`][]定義タイプに渡します。

#### 定義タイプ: `apache::peruser::multiplexer`　

FreeBSDに関してのみ、[`Peruser`][]モジュールを有効にします。　

#### 定義タイプ: `apache::peruser::processor`

FreeBSDに関してのみ、[`Peruser`][]モジュールを有効にします。　

#### 定義タイプ: `apache::security::file_link`

[`apache::mod::security`][]の`activated_rules`をディスク上のそれぞれのCRSルールにリンクします。

### テンプレート

Apacheモジュールは、[`apache::vhost`][]および[`apache::mod`][]定義タイプを有効にするにあたり、テンプレートに大きく依存しています。このテンプレートは、オペレーティングシステムに固有の[Facter][] factsをベースに構築されています。明示的にコールアウトされない限り、ほとんどのテンプレートは設定には使われません。

### 関数
#### apache_pw_hash
Apacheが読みこむhtpasswdファイルに適したフォーマットでパスワードをハッシュします。

現在はSHAハッシュを使用しています。これは、このフォーマットは安全ではないとされているものの、ほとんどのプラットフォームでサポートされているもっとも安全なフォーマットであるためです。

## 制約事項

### 全般

このモジュールは、以下に関して、[オープンソースPuppet][]および[Puppet Enterprise][]の両方でCIテストが実施されています。

- CentOS 5および6
- Ubuntu 12.04および14.04
- Debian 7
- RHEL 5、6、7

このモジュールでは、FreeBSD、Gentoo、Amazon Linuxなどの、他のディストリビューションおよびオペレーティングシステムで使用できる機能も提供されていますが、そうしたシステムについては公式なテストは実施されておらず、新たに不具合が生じる可能性があります。

### FreeBSD

FreeBSDでこのモジュールを使用するには、apache24-2.4.12 (www/apache24)以降を使用する_必要があります_。 

### Gentoo

Gentooでは、このモジュールは[`gentoo/puppet-portage`][] Puppetモジュールに依存します。Gentooに関しては、一部の機能や設定が適用または有効化されますが、このモジュールに[対応するオペレーティングシステム][]ではありません。

### RHEL/CentOS
[`apache::mod::auth_cas`][]、[`apache::mod::passenger`][]、[`apache::mod::proxy_html`][]、[`apache::mod::shib`][]クラスは、追加のリポジトリから依存関係パッケージが提供されていなければ、RH/CentOSでは機能しません。

関連するリポジトリとパッケージについては、以下の各ドキュメントを参照してください。

#### RHEL/CentOS 5

[`apache::mod::passenger`][]および[`apache::mod::proxy_html`][]クラスは、リポジトリに適合するパッケージがないため、テストされていません。

#### RHEL/CentOS 6

[`apache::mod::passenger`][]クラスは、EL6リポジトリに適合するパッケージがないため、インストールされません。

#### RHEL/CentOS 7

[`apache::mod::passenger`][]および[`apache::mod::proxy_html`][]クラスは、EL7リポジトリに適合するパッケージがないため、テストされていません。また、[`apache::vhost`][]定義タイプの[`rack_base_uris`][]パラメータも、同様の理由でテストされていません。

### SELinuxおよびカスタムパス

[SELinux][]が[適用モード][]になっていて、`logroot`、`mod_dir`、`vhost_dir`、`docroot`に関してカスタムパスを使用したい場合は、ファイルのコンテキストを各自で管理する必要があります。

これにはPuppetを使用できます。

``` puppet
exec { 'set_apache_defaults':
  command => 'semanage fcontext -a -t httpd_sys_content_t "/custom/path(/.*)?"',
  path    => '/bin:/usr/bin/:/sbin:/usr/sbin',
  require => Package['policycoreutils-python'],
}

package { 'policycoreutils-python':
  ensure => installed,
}

exec { 'restorecon_apache':
  command => 'restorecon -Rv /apache_spec',
  path    => '/bin:/usr/bin/:/sbin:/usr/sbin',
  before  => Class['Apache::Service'],
  require => Class['apache'],
}

class { 'apache': }

host { 'test.server':
  ip => '127.0.0.1',
}

file { '/custom/path':
  ensure => directory,
}

file { '/custom/path/include':
  ensure  => present,
  content => '#additional_includes',
}

apache::vhost { 'test.server':
  docroot             => '/custom/path',
  additional_includes => '/custom/path/include',
}
```

`chcon`ではなく、`semanage fcontext`を用いてコンテキストを設定する必要があります。これは、Puppetの`file`リソースでは、リソースにより指定されていない場合、その値のコンテキストがリセットされるためです。

### Ubuntu 10.04

[`apache::vhost::WSGIImportScript`][]パラメータにより、Apacheの古いバージョンではサポートされていないバーチャルホスト内のステートメントが作成され、不具合が生じます。これは今後のリファクタリングで修正される予定です。

### Ubuntu 16.04
[`apache::mod::suphp`][]クラスは、リポジトリに適合するパッケージがないため、テストされていません。

## 開発

### 貢献

[Puppet Forge][]上の[Puppet][]モジュールはオープンプロジェクトであり、その価値を維持するにはコミュニティからの貢献が欠かせません。Puppetが提供する膨大な数のプラットフォームや、無数のハードウェア、ソフトウェア、デプロイ設定に弊社がアクセスすることは不可能です。

できるだけ変更に簡単に貢献していただき、お使いの環境でモジュールが動作するようにしたいと考えています。モジュールの品質の維持と改善のため、Puppetは貢献者に守っていただくガイドラインを設けています。

詳細については、[モジュールコントリビューションガイド][]および[CONTRIBUTING.md][]を参照してください。
