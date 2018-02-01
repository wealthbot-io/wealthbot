# mysql

#### 目次

1. [モジュールについて - モジュールの機能とその有益性](#モジュールについて)
2. [セットアップ - mysql導入の基本](#セットアップ)
    * [mysqlの導入](#mysqlの導入)
3. [使用方法 - 設定オプションとその他の機能](#使用方法)
    * [サーバオプションのカスタマイズ](#サーバオプションのカスタマイズ)
    * [データベースの作成](#データベースの作成)
    * [設定のカスタマイズ](#設定のカスタマイズ)
    * [既存のサーバに対する操作](#既存のサーバに対する操作)
    * [パスワードの指定](#パスワードの指定)
    * [CentOSへのPerconaサーバのインストール](#centosへのperconaサーバのインストール)
    *[UbuntuへのMariaDBのインストール](#ubuntuへのmariadbのインストール)
4. [参考 - モジュールの機能と動作について](#参考)
5. [制約事項 - OSの互換性など](#制約事項)
6. [開発 - モジュール貢献についてのガイドライン](#開発)

## モジュールについて

mysqlモジュールは、MySQLサービスをインストール、設定、管理します。

このモジュールは、MySQLのインストールと設定を管理するとともに、データベース、ユーザ、GRANT権限などのMySQLリソースを管理できるようにPuppetの機能を拡張します。

## セットアップ

### mysqlの導入

デフォルトのオプションを使用してサーバをインストールするには、次のコマンドを使用します。

`include '::mysql::server'`.

ルートパスワードや`/etc/my.cnf`の設定値などのオプションをカスタマイズするには、オーバーライドハッシュも渡す必要があります。

```puppet
class { '::mysql::server':
  root_password           => 'strongpassword',
  remove_default_accounts => true,
  override_options        => $override_options
}
```

$override_options用のハッシュ構造体の例については、後述の[**サーバオプションのカスタマイズ**](#サーバオプションのカスタマイズ)を参照してください。

## 使用方法

サーバに関するすべてのインタラクションは`mysql::server`を使用して行われ、クライアントのインストールには`mysql::client`が、バインディングのインストールには`mysql::bindings`が使用されます。

### サーバオプションのカスタマイズ

サーバオプションを定義するには、`mysql::server`でオーバーライドのハッシュ構造体を作成します。このハッシュは、my.cnfファイルに含まれているハッシュと似ています。

```puppet
$override_options = {
  'section' => {
    'item' => 'thing',
  }
}
```

この形式のオプションを従来の方法で示すと次のようになります。

```
[section]
thing = X
```

ハッシュ内では`thing => true`、`thing => value`、または`thing => ""`の形でエントリを作成できます。または、`thing => ['value', 'value2']`の形で配列を渡したり、`thing => value`を独立した行に個別にリストすることもできます。

値を設定せずに変数をハッシュに含めて渡すことができます。この場合、変数にはMySQLのデフォルトの設定値が使用されます。オプションを`my.cnf`ファイルから除外するには(たとえば`override_options`を使用してデフォルト値に戻す場合など)、`thing => undef`を渡します。

オプションに複数のインスタンスが必要な場合は配列を渡します。たとえば次の例の場合は、

```puppet
$override_options = {
  'mysqld' => {
    'replicate-do-db' => ['base1', 'base2'],
  }
}
```

次のようになります。

```puppet
[mysqld]
replicate-do-db = base1
replicate-do-db = base2
```

バージョンに固有なパラメータを実装するには、[mysqld-5.5]のようにバージョンを指定します。こうすると、1つのconfigで複数の異なるバージョンのMySQLに対応できます。

### データベースの作成

ユーザおよび割り当てられたいくつかの権限を含むデータベースを作成するには、次のようにします。

```puppet
mysql::db { 'mydb':
  user     => 'myuser',
  password => 'mypass',
  host     => 'localhost',
  grant    => ['SELECT', 'UPDATE'],
}
```

エクスポートされたリソースを含む別のリソース名を使用するには、次のようにします。

```puppet
 @@mysql::db { "mydb_${fqdn}":
  user     => 'myuser',
  password => 'mypass',
  dbname   => 'mydb',
  host     => ${fqdn},
  grant    => ['SELECT', 'UPDATE'],
  tag      => $domain,
}
```

さらに、これをリモートDBサーバに集めることができます。

```puppet
Mysql::Db <<| tag == $domain |>>
```

データベースの作成時にファイルにsqlパラメータを設定する場合は、新しいデータベースにファイルがインポートされます。

サイズの大きいsqlファイルの場合は、`import_timeout`パラメータの値(デフォルト値300秒)を大きくします。

```puppet
mysql::db { 'mydb':
  user     => 'myuser',
  password => 'mypass',
  host     => 'localhost',
  grant    => ['SELECT', 'UPDATE'],
  sql      => '/path/to/sqlfile.gz',
  import_cat_cmd => 'zcat',
  import_timeout => 900,
}
```

### 設定のカスタマイズ

MySQLカスタム設定を追加するには、`includedir`にファイルを追加します。こうすると設定値をオーバーライドしたり別の設定値を追加したりすることができ、`mysql::server`で`override_options`を使用しない場合に役立ちます。`includedir`の場所は、デフォルトでは`/etc/mysql/conf.d`に設定されます。

### 既存のサーバに対する操作

既存のMySQLサーバ上にデータベースとユーザのインスタンスを作成するには、`root`のホームディレクトリに`.my.cnf`ファイルが必要です。次の例のように、このファイルでリモートサーバのアドレスと認証情報を指定する必要があります。

```puppet
[client]
user=root
host=localhost
password=secret
```

このモジュールは、`mysqld_version`ファクトから、使用されているサーバのバージョンを認識します。デフォルトでは、`mysqld_version`は`mysqld -V`の出力に設定されています。リモートMySQLサーバに対する操作を行う場合は、`mysqld_version`に対応するカスタムファクトを設定しないと正常に動作しない可能性があります。

リモートサーバに対する操作を行う際には、Puppetマニフェスト内で`mysql::server`クラスを使用*しない*でください。

### パスワードの指定

パスワードは、プレーンテキストとして渡せるだけでなく、次のようにハッシュとして入力することもできます。

```puppet
mysql::db { 'mydb':
  user     => 'myuser',
  password => '*6C8989366EAF75BB670AD8EA7A7FC1176A95CEF4',
  host     => 'localhost',
  grant    => ['SELECT', 'UPDATE'],
}
```

### CentOSへのPerconaサーバのインストール

次の例は、CentOSシステムへのPerconaサーバの最小限のインストール方法を示します。
この例では、Perconaサーバ、クライアント、バインディング(PerlとPythonのバインディングを含む)がセットアップされます。この方法をカスタマイズして必要に応じバージョンを更新することができます。

この方法は、Puppet 4.4/CentOS 7/Perconaサーバ5.7でテストされています。

**注意：** yumレポジトリのインストールはこのパッケージには含まれていません。
この例は、インストールの詳細を示したものに過ぎません。

```puppet
yumrepo { 'percona':
  descr    => 'CentOS $releasever - Percona',
  baseurl  => 'http://repo.percona.com/centos/$releasever/os/$basearch/',
  gpgkey   => 'http://www.percona.com/downloads/percona-release/RPM-GPG-KEY-percona',
  enabled  => 1,
  gpgcheck => 1,
}

class {'mysql::server':
  package_name     => 'Percona-Server-server-57',
  package_ensure   => '5.7.11-4.1.el7',
  service_name     => 'mysql',
  config_file      => '/etc/my.cnf',
  includedir       => '/etc/my.cnf.d',
  root_password    => 'PutYourOwnPwdHere',
  override_options => {
    mysqld => {
      log-error => '/var/log/mysqld.log',
      pid-file  => '/var/run/mysqld/mysqld.pid',
    },
    mysqld_safe => {
      log-error => '/var/log/mysqld.log',
    },
  }
}

# 注意：Percona-Server-server-57をインストールするとPercona-Server-client-57もインストールされます。
# 次の例は、Percona MySQLクライアントを単独でインストールする方法を示します。
class {'mysql::client':
  package_name   => 'Percona-Server-client-57',
  package_ensure => '5.7.11-4.1.el7',
}

# 通常、以下のパッケージはPercona-Server-server-57とともにインストールされます。
# バインディングもインストールする必要がある場合は、このコードでインストールできます。
class { 'mysql::bindings':
  client_dev_package_name   => 'Percona-Server-shared-57',
  client_dev_package_ensure => '5.7.11-4.1.el7',
  client_dev                => true,
  daemon_dev_package_name   => 'Percona-Server-devel-57',
  daemon_dev_package_ensure => '5.7.11-4.1.el7',
  daemon_dev                => true,
  perl_enable               => true,
  perl_package_name         => 'perl-DBD-MySQL',
  python_enable             => true,
  python_package_name       => 'MySQL-python',
}

# 依存関係の定義
Yumrepo['percona']->
Class['mysql::server']

Yumrepo['percona']->
Class['mysql::client']

Yumrepo['percona']->
Class['mysql::bindings']
```

### UbuntuへのMariaDBのインストール

#### オプション：MariaDBの公式のレポジトリのインストール

次の例では、distroレポジトリでなく公式のMariaDBレポジトリの最新の安定版(現在10.1)を使用しています。代わりに、Ubuntuレポジトリのパッケージを使用することもできます。必要に応じた正しいバージョンのレポジトリを使用してください。

**注意：** `sfo1.mirrors.digitalocean.com`は利用可能な多くのミラーの一例であり、公式のミラーであればいずれも使用できます。

```puppet
include apt

apt::source { 'mariadb':
  location => 'http://sfo1.mirrors.digitalocean.com/mariadb/repo/10.1/ubuntu',
  release  => $::lsbdistcodename,
  repos    => 'main',
  key      => {
    id     => '199369E5404BD5FC7D2FE43BCBCB082A1BB943DB',
    server => 'hkp://keyserver.ubuntu.com:80',
  },
  include => {
    src   => false,
    deb   => true,
  },
}
```

#### MariaDBサーバのインストール

次の例では、Ubuntu TrustyへのMariaDBサーバのインストール方法を示しています。`my.cnf`のバージョンとパラメータは、必要に応じて調整してください。`my.cnf`のパラメータはすべて`override_options`パラメータを使用して定義できます。

フォルダ`/var/log/mysql`と`/var/run/mysqld`は自動的に作成されますが、他のカスタムフォルダを使用する場合は、それらがコードの必須要件になります。

以下に示す値はすべて、最小限の構成にする場合の例です。

必要なパッケージのバージョンを、`package_ensure`パラメータで指定してください。

```puppet
class {'::mysql::server':
  package_name     => 'mariadb-server',
  package_ensure   => '10.1.14+maria-1~trusty',
  service_name     => 'mysql',
  root_password    => 'AVeryStrongPasswordUShouldEncrypt!',
  override_options => {
    mysqld => {
      'log-error' => '/var/log/mysql/mariadb.log',
      'pid-file'  => '/var/run/mysqld/mysqld.pid',
    },
    mysqld_safe => {
      'log-error' => '/var/log/mysql/mariadb.log',
    },
  }
}

# 依存関係の管理。レポジトリをインストールする場合は
# この例の前のステップで示されている部分だけを使用してください。
Apt::Source['mariadb'] ~>
Class['apt::update'] ->
Class['::mysql::server']

```

#### MariaDBクライアントのインストール

次の例は、MariaDBクライアントとすべてのバインディングを一度にインストールする方法を示します。このインストール操作は、サーバのインストール操作とは別に行うことができます。

必要なパッケージのバージョンを、`package_ensure`パラメータで指定してください。

```puppet
class {'::mysql::client':
  package_name    => 'mariadb-client',
  package_ensure  => '10.1.14+maria-1~trusty',
  bindings_enable => true,
}

# 依存関係の管理。レポジトリをインストールする場合はこの例の前のステップで示されている部分だけを使用してください。
Apt::Source['mariadb'] ~>
Class['apt::update'] ->
Class['::mysql::client']
```

### CentOSへのMySQL Communityサーバのインストール

MySQLモジュールおよびHieraを使用して、MySQL CommunityサーバーをCentOSにインストールすることができます。この例は以下のバージョンでテスト済みです。

* MySQL Community Server 5.6
* Centos 7.3
* Hieraを使用したPuppet 3.8.7 
* puppetlabs-mysqlモジュールv3.9.0

Puppetで：

```puppet
  include ::mysql::server

  create_resources(yumrepo, hiera('yumrepo', {}))

  Yumrepo['repo.mysql.com'] -> Anchor['mysql::server::start']
  Yumrepo['repo.mysql.com'] -> Package['mysql_client']

  create_resources(mysql::db, hiera('mysql::server::db', {}))
```

Hieraで：

```yaml
---
# mysqlモジュールはMySQLを正しく導入するために、MariaDBの代わりに多くのパラメータのフィードを必要とします。
# Centos 7.3
yumrepo:
  'repo.mysql.com':
    baseurl: "http://repo.mysql.com/yum/mysql-5.6-community/el/%{::operatingsystemmajrelease}/$basearch/"
    descr: 'repo.mysql.com'
    enabled: 1
    gpgcheck: true
    gpgkey: 'http://repo.mysql.com/RPM-GPG-KEY-mysql'

mysql::client::package_name: "mysql-community-client" # 適切なMySQL導入のために必要
mysql::server::package_name: "mysql-community-server" #適切なMySQL導入のために必要
mysql::server::package_ensure: 'installed' #ここではバージョンを指定しないでください。残念ながら、パッケージがインストールされているエラーでyumは失敗しました。
mysql::server::root_password: "change_me_i_am_insecure"
mysql::server::manage_config_file: true
mysql::server::service_name: 'mysqld' # Puppetモジュールに必要
mysql::server::override_options:
  'mysqld':
    'bind-address': '127.0.0.1'
    'log-error': /var/log/mysqld.log' # 適切なMySQL導入のために必要
  'mysqld_safe':
    'log-error': '/var/log/mysqld.log'  # 適切なMySQL導入のために必要 

# データベース+アクセスできるアカウント、暗号化されていないパスワードを作成
mysql::server::db:
  "dev":
    user: "dev"
    password: "devpass"
    host: "127.0.0.1"
    grant:
      - "ALL"

```


## 参考

### クラス

#### パブリッククラス

* [`mysql::server`](#mysqlserver)：MySQLをインストールして設定します。
* [`mysql::server::monitor`](#mysqlservermonitor)：モニタするユーザをセットアップします。
* [`mysql::server::mysqltuner`](#mysqlservermysqltuner)：MySQL tunerスクリプトをインストールします。
* [`mysql::server::backup`](#mysqlserverbackup)：cronを使用してMySQLバックアップをセットアップします。
* [`mysql::bindings`](#mysqlbindings)：さまざまなMySQL言語バインディングをインストールします。
* [`mysql::client`](#mysqlclient)：MySQLクライアントをインストールします(サーバ以外)。

#### プライベートクラス

* `mysql::server::install`：パッケージをインストールします。
* `mysql::server::installdb`：mysqldデータディレクトリ(/var/lib/mysqlなど)のセットアップを実行します。
* `mysql::server::config`：MySQLを設定します。
* `mysql::server::service`：サービスを管理します。
* `mysql::server::account_security`：デフォルトのMySQLアカウントを削除します。
* `mysql::server::root_password`：MySQLのルートパスワードを設定します。
* `mysql::server::providers`：ユーザ、GRANT権限、データベースを作成します。
* `mysql::bindings::client_dev`：MySQLクライアント開発パッケージをインストールします。
* `mysql::bindings::daemon_dev`：MySQLデーモン開発パッケージをインストールします。
* `mysql::bindings::java`：javaバインディングをインストールします。
* `mysql::bindings::perl`：Perlバインディングをインストールします。
* `mysql::bindings::php`：PHPバインディングをインストールします。
* `mysql::bindings::python`：Pythonバインディングをインストールします。
* `mysql::bindings::ruby`：Rubyバインディングをインストールします。
* `mysql::client::install`：MySQLクライアントをインストールします。
* `mysql::backup::mysqldump`：mysqldumpのバックアップを実行します。
* `mysql::backup::mysqlbackup`：Oracle MySQL Enterprise Backupを使用してバックアップを実行します。
* `mysql::backup::xtrabackup`：PerconaのXtraBackupを使用してバックアップを実行します。

### パラメータ

#### mysql::server

##### `create_root_user`

ルートユーザを作成するかどうかを指定します。

有効な値：`true`、`false`。

デフォルト値：`true`。

このパラメータは、Galeraでクラスタをセットアップする場合に役立ちます。ルートユーザの作成が必要なのは一度だけです。このパラメータを、1つのノードに対しtrueに設定し、他のすべてのノードに対してfalseに設定できます。

#####  `create_root_my_cnf`

`/root/.my.cnf`を作成するかどうかを指定します。

有効な値：`true`、`false`。

デフォルト値：`true`。

`create_root_my_cnf`を使用すると`create_root_user`に左右されずに`/root/.my.cnf`を作成できます。すべてのノードに`/root/.my.cnf`が存在するようにしたい場合に、Galeraでこの機能を使用してクラスタをセットアップできます。

#####  `root_password`

MySQLのルートパスワード。Puppetは、このパラメータを使用して、ルートパスワードの設定や`/root/.my.cnf`の更新を試みます。

`create_root_user`または`create_root_my_cnf`がtrueの場合にこのパラメータが必要です。`root_password`が'UNSET'の場合は`create_root_user`と`create_root_my_cnf`がfalseになります(MySQLルートユーザと`/root/.my.cnf`が作成されません)。

パスワード変更はサポートされますが、`/root/.my.cnf`に旧パスワードが設定されている必要があります。実際には、Puppetは`/root/.my.cnf`に設定されている旧パスワードを使用してMySQLで新しいパスワードを設定してから、`/root/.my.cnf`を新しいパスワードで更新します。

##### `old_root_password`

現在、このパラメータでは何も行わず、下位互換性を確保するためだけに存在します。ルートパスワードの変更についての詳細は、上記の`root_password`パラメータの説明を参照してください。

##### `override_options`

MySQLに渡すオーバーライドオプションを指定します。構造はmy.cnfファイルのハッシュと同様です。

```puppet
$override_options = {
  'section' => {
    'item'             => 'thing',
  }
}
```

使用方法の詳細は、上記の[**サーバオプションのカスタマイズ**](#サーバオプションのカスタマイズ)を参照してください。

##### `config_file`

MySQL設定ファイルの場所を示すパス。

##### `manage_config_file`

MySQL設定ファイルを管理するかどうかを指定します。

有効な値：`true`、`false`。

デフォルト値：`true`。

##### `includedir`

カスタム設定オーバーライド用の!includedirの場所を示すパス。

##### `install_options`

管理対象のパッケージリソースに[install_options](https://docs.puppetlabs.com/references/latest/type.html#package-attribute-install_options)配列を渡します。指定されているパッケージマネージャに対応する正しいオプションを渡す必要があります。

##### `purge_conf_dir`

`includedir`ディレクトリをパージするかどうかを指定します。

有効な値：`true`、`false`。

デフォルト値：`false`。

##### `restart`

何らかの変更があった場合にサービスを再起動するかどうかを指定します。

有効な値：`true`、`false`。

デフォルト値：`false`。

##### `root_group`

ルートに使用するグループの名前。グループ名またはグループIDのいずれかです。詳細については[`group`ファイルの属性](https://docs.puppetlabs.com/references/latest/type.html#file-attribute-group)を参照してください。

##### `mysql_group`

MySQLデーモンユーザのグループの名前。グループ名またはグループIDのいずれかです。詳細については[`group`ファイルの属性](https://docs.puppetlabs.com/references/latest/type.html#file-attribute-group)を参照してください。

##### `package_ensure`

パッケージが存在するかどうか、またはパッケージが特定のバージョンでなければならないかどうかを指定します。

有効な値：'present'、'absent'、または'x.y.z'。

デフォルト値：'present'。

##### `package_manage`

MySQLサーバパッケージを管理するかどうかを指定します。

デフォルト値：`true`。

##### `package_name`

インストールするMySQLサーバパッケージの名前。

##### `remove_default_accounts`

`mysql::server::account_security`を自動的に含めるかどうかを指定します。

有効な値：`true`、`false`。

デフォルト値：`false`。

##### `service_enabled`

サービスの有効化を指定します。

有効な値：`true`、`false`。

デフォルト値：`true`。

##### `service_manage`

サービスを管理するかどうかを指定します。

有効な値：`true`、`false`。

デフォルト値：`true`。

##### `service_name`

MySQLサーバサービスの名前。

デフォルト値はOSにより異なり、'params.pp'に定義されています。

##### `service_provider`

サービスの管理に使用するプロバイダ。

Ubuntuの場合のデフォルト値は'upstart'、Ubuntu以外の場合のデフォルト値は定義されていません。

##### `users`

作成するユーザのハッシュ(オプション)。[mysql_user](#mysql_user)に渡されます。

```puppet
users => {
  'someuser@localhost' => {
    ensure                   => 'present',
    max_connections_per_hour => '0',
    max_queries_per_hour     => '0',
    max_updates_per_hour     => '0',
    max_user_connections     => '0',
    password_hash            => '*F3A2A51A9B0F2BE2468926B4132313728C250DBF',
    tls_options              => ['NONE'],
  },
}
```

##### `grants`

[mysql_grant](#mysql_grant)に渡されるGRANT権限のハッシュ(オプション)。

```puppet
grants => {
  'someuser@localhost/somedb.*' => {
    ensure     => 'present',
    options    => ['GRANT'],
    privileges => ['SELECT', 'INSERT', 'UPDATE', 'DELETE'],
    table      => 'somedb.*',
    user       => 'someuser@localhost',
  },
}
```

##### `databases`

作成されるデータベースのハッシュ(オプション)。[mysql_database](#mysql_database)に渡されます。

```puppet
databases   => {
  'somedb'  => {
    ensure  => 'present',
    charset => 'utf8',
  },
}
```

#### mysql::server::backup

##### `backupuser`

バックアップ用に作成するMySQLユーザ。

##### `backuppassword`

バックアップ用のMySQLユーザパスワード。

##### `backupdir`

バックアップを保存するディレクトリ。

##### `backupdirmode`

バックアップディレクトリに適用されるパーミッション。このパラメータは`file`リソースに直接渡されます。

##### `backupdirowner`

バックアップディレクトリの所有者。このパラメータは`file`リソースに直接渡されます。

##### `backupdirgroup`

バックアップディレクトリのグループ所有者。このパラメータは`file`リソースに直接渡されます。

##### `backupcompress`

バックアップを圧縮するかどうかを指定します。

有効な値：`true`、`false`。

デフォルト値：`true`。

##### `backuprotate`

バックアップを保持する日数。

有効な値：整数値。

デフォルト値：30。

##### `delete_before_dump`

バックアップ前に古い.sqlファイルを削除するかどうかを設定します。trueに設定すると古いファイルがバックアップ前に削除され、falseに設定するとバックアップ後に削除されます。

有効な値：`true`、`false`。

デフォルト値：`false`。

##### `backupdatabases`

バックアップするデータベースの配列を指定します。

##### `file_per_database`

データベースごとに個別のファイルを使用するかどうかを設定します。

有効な値：`true`、`false`。

デフォルト値：`false`。

##### `include_routines`

`file_per_database`バックアップを実行する際にデータベースごとにルーチンを含めるかどうかを設定します。

デフォルト値：`false`。

##### `include_triggers`

`file_per_database`バックアップを実行する際にデータベースごとにトリガを含めるかどうかを設定します。

デフォルト値：`false`。

##### `ensure`

バックアップスクリプトを削除できます。

有効な値：'present'、'absent'。

デフォルト値：'present'。

##### `execpath`

MySQLを標準的でない場所にインストールする場合にカスタムパスを設定できます。デフォルト値：`/usr/bin:/usr/sbin:/bin:/sbin`。

##### `time`

バックアップ時刻を設定する2つの要素の配列。時刻をHH:MM形式で['23', '5'](23:05)または['3', '45'](03:45)に設定できます。

#### mysql::server::backup

##### `postscript`

バックアップ終了時に実行されるスクリプト。この機能を使用すると、バックアップを中央ストアに同期させることができます。このスクリプトは、直接実行される1つの行であっても、配列を形成する複数の行であっても構いません。あるいは、外部で管理される1つ以上の(実行可能な)ファイルにすることもできます。

##### `prescript`

バックアップ開始前に実行されるスクリプト。

##### `provider`

サーバのバックアップの実行について設定します。有効な値は以下のとおりです。

* `mysqldump`：mysqldumpを使用してバックアップを実行。バックアップのタイプ：Logical(デフォルト値)。
* `mysqlbackup`：OracleのMySQL Enterprise Backupを使用してバックアップを実行します。バックアップのタイプ：Physical。このタイプのバックアップを使用するにはOracleの`meb`パッケージが必要です。RPM形式のものとTAR形式のものがあります。Ubuntuの場合は、[meb-deb](https://github.com/dveeden/meb-deb)を使用して公式のtarballからパッケージを作成できます。
* `xtrabackup：PerconaのXtraBackupを使用してバックアップを実行します。バックアップのタイプ：Physical。

##### `maxallowedpacket`

バックアップダンプスクリプト用のSQLステートメントの最大サイズを定義ます。デフォルト値は1MBで、MySQL Serverのデフォルト値と同じです。

##### `optional_args`

バックアップツールに渡すべきオプションの引数の配列を指定します(現在はxtrabackupプロバイダでのみサポート)。

#### mysql::server::monitor

##### `mysql_monitor_username`

MySQLのモニタ用に作成するユーザ名。

##### `mysql_monitor_password`

MySQLのモニタ用に作成するパスワード。

##### `mysql_monitor_hostname`

モニタするユーザリクエストへのアクセスが許可されたホスト名。

#### mysql::server::mysqltuner

**注意**：ネットワークに接続されていないシステムでこのクラスを使用する場合は、mysqltuner.plスクリプトをダウンロードし、`http(s)://`、`puppet://`、`ftp://`、または完全修飾ファイルパスを使用して、アクセス可能な場所でホストされるようにしておく必要があります。

##### `ensure`

リソースが存在することを確認します。

有効な値：'present'、'absent'。

デフォルト値：'present'。

##### `version`

major/MySQLTuner-perl githubレポジトリからインストールするバージョン。有効なタグでなければなりません。

デフォルト値：'v1.3.0'。

##### `environment`

プロキシを使用したダウンロードなどのダウンロード中に有効な環境変数：environment => 'https_proxy=http://proxy.example.com:80'

#### mysql::bindings

##### `client_dev`

`::mysql::bindings::client_dev`を含めるかどうかを指定します。

有効な値：`true`、`false`。

デフォルト値：`false`。

##### `daemon_dev`

`::mysql::bindings::daemon_dev`を含めるかどうかを指定します。

有効な値：`true`、`false`。

デフォルト値：`false`。

##### `java_enable`

`::mysql::bindings::java`を含めるかどうかを指定します。

有効な値：`true`、`false`。

デフォルト値：`false`。

#####  `perl_enable`

`mysql::bindings::perl`を含めるかどうかを指定します。

有効な値：`true`、`false`。

デフォルト値：`false`。

##### `php_enable`

`mysql::bindings::php`を含めるかどうかを指定します。

有効な値：`true`、`false`。

デフォルト値：`false`。

##### `python_enable`

`mysql::bindings::python`を含めるかどうかを指定します。

有効な値：`true`、`false`。

デフォルト値：`false`。

##### `ruby_enable`

`mysql::bindings::ruby`を含めるかどうかを指定します。

有効な値：`true`、`false`。

デフォルト値：`false`。

##### `install_options`

管理対象のパッケージリソースに`install_options`を渡します。パッケージマネージャに対応する[正しいオプション](https://docs.puppetlabs.com/references/latest/type.html#package-attribute-install_options)を渡す必要があります。

##### `client_dev_package_ensure`

パッケージが、存在するかしないか、または特定のバージョンでなければならないかどうかを指定します。

有効な値：'present'、'absent'、または'x.y.z'。

適用されるのは`client_dev => true`の場合だけです。

##### `client_dev_package_name`

インストールするclient_devパッケージの名前。

適用されるのは`client_dev => true`の場合だけです。

##### `client_dev_package_provider`

client_devパッケージのインストールに使用するプロバイダ。

適用されるのは`client_dev => true`の場合だけです。

##### `daemon_dev_package_ensure`

パッケージが、存在するかしないか、または特定のバージョンでなければならないかどうかを指定します。

有効な値：'present'、'absent'、または'x.y.z'。

適用されるのはdaemon_dev => true`の場合だけです。

##### `daemon_dev_package_name`

インストールするdaemon_devパッケージの名前。

適用されるのはdaemon_dev => true`の場合だけです。

##### `daemon_dev_package_provider`

daemon_devパッケージのインストールに使用するプロバイダ。

適用されるのはdaemon_dev => true`の場合だけです。

##### `java_package_ensure`

パッケージが、存在するかしないか、または特定のバージョンでなければならないかどうかを指定します。

有効な値：'present'、'absent'、または'x.y.z'。

適用されるのは`java_enable => true`の場合だけです。

##### `java_package_name`

インストールするJavaパッケージの名前。

適用されるのは`java_enable => true`の場合だけです。

##### `java_package_provider`

Javaパッケージのインストールに使用するプロバイダ。

適用されるのは`java_enable => true`の場合だけです。

##### `perl_package_ensure`

パッケージが、存在するかしないか、または特定のバージョンでなければならないかどうかを指定します。

有効な値：'present'、'absent'、または'x.y.z'。

適用されるのは`perl_enable => true`の場合だけです。

##### `perl_package_name`

インストールするPerlパッケージの名前。

適用されるのは`perl_enable => true`の場合だけです。

##### `perl_package_provider`

Perlパッケージのインストールに使用するプロバイダ。

適用されるのは`perl_enable => true`の場合だけです。

##### `php_package_ensure`

パッケージが、存在するかしないか、または特定のバージョンでなければならないかどうかを指定します。

有効な値：'present'、'absent'、または'x.y.z'。

適用されるのは`php_enable => true`の場合だけです。

##### `php_package_name`

インストールするPHPパッケージの名前。

適用されるのは`php_enable => true`の場合だけです。

##### `python_package_ensure`

パッケージが、存在するかしないか、または特定のバージョンでなければならないかどうかを指定します。

有効な値：'present'、'absent'、または'x.y.z'。

適用されるのは`python_enable => true`の場合だけです。

##### `python_package_name`

インストールするPythonパッケージの名前。

適用されるのは`python_enable => true`の場合だけです。

##### `python_package_provider`

Pythonパッケージのインストールに使用するプロバイダ。

適用されるのは`python_enable => true`の場合だけです。

##### `ruby_package_ensure`

パッケージが、存在するかしないか、または特定のバージョンでなければならないかどうかを指定します。

有効な値：'present'、'absent'、または'x.y.z'。

適用されるのは`ruby_enable => true`の場合だけです。

##### `ruby_package_name`

インストールするRubyパッケージの名前。

適用されるのは`ruby_enable => true`の場合だけです。

##### `ruby_package_provider`

Rubyパッケージのインストールに使用するプロバイダ。

#### mysql::client

##### `bindings_enable`

すべてのバインディングを自動的にインストールするかどうかを指定します。

有効な値：`true`、`false`。

デフォルト値：`false`。

##### `install_options`

管理対象のパッケージリソースに関するインストールオプションの配列。パッケージマネージャに対応する正しいオプションを渡す必要があります。

##### `package_ensure`

MySQLパッケージが、存在するかしないか、または特定のバージョンでなければならないかどうかを指定します。

有効な値：'present'、'absent'、または'x.y.z'。

##### `package_manage`

MySQLクライアントパッケージを管理するかどうかを指定します。

デフォルト値：`true`。

##### `package_name`

インストールするMySQLクライアントパッケージの名前。

### 定義

#### mysql::db

```puppet
mysql_database { 'information_schema':
  ensure  => 'present',
  charset => 'utf8',
  collate => 'utf8_swedish_ci',
}
mysql_database { 'mysql':
  ensure  => 'present',
  charset => 'latin1',
  collate => 'latin1_swedish_ci',
}
```

##### `user`

作成するデータベースのユーザ。

##### `password`

作成するデータベースの$userのパスワード。

##### `dbname`

作成するデータベースの名前。

デフォルト値："$name"。

##### `charset`

データベースに使用するキャラクタセット。

デフォルト値：'utf8'。

##### `collate`

データベースの照合順序。

デフォルト値：'utf8_general_ci'。

##### `host`

GRANT権限を付与するuser@hostの一部として使用するホスト。

デフォルト値：'localhost'。

##### `grant`

データベースに対してuser@hostに付与される権限。

デフォルト値：'ALL'。

##### `sql`

実行するsqlfileへのパス。文字列として指定された1つのファイル、または文字列の配列のいずれかです。

デフォルト値：`undef`。

##### `enforce_sql`

sqlfilesを毎回実行するかどうかを指定します。falseに設定した場合はsqlfilesは1回しか実行されません。

有効な値：`true`、`false`。

デフォルト値：`false`。

##### `ensure`

データベースを作成するかどうかを指定します。

有効な値：'present'、'absent'。

デフォルト値：'present'。

##### `import_timeout`

sqlfilesをロードするときのタイムアウト(秒)。

デフォルト値：300。

##### `import_cat_cmd`

データベースをインポートするためにsqlfileを読み込むコマンド。sqlfilesが圧縮されている場合に役立ちます。たとえば.gzファイルの場合に'zcat'を使用することができます。

デフォルト値：'cat'。

### タイプ

#### mysql_database

`mysql_database`は、MySQLでデータベースを作成し、管理します。

##### `ensure`

リソースの存在を指定します。

有効な値：'present'、'absent'。

デフォルト値：'present'。

##### `name`

管理するMySQLデータベースの名前。

##### `charset`

データベースに使用するキャラクタセットの設定。

デフォルト値：'utf8'。

##### `collate`

データベースに使用する照合順序の設定。

デフォルト値：'utf8_general_ci'。

#### mysql_user

MySQLでのユーザのGRANT権限を作成し、管理します。

```puppet
mysql_user { 'root@127.0.0.1':
  ensure                   => 'present',
  max_connections_per_hour => '0',
  max_queries_per_hour     => '0',
  max_updates_per_hour     => '0',
  max_user_connections     => '0',
}
```

認証プラグインを指定することもできます。

```puppet
mysql_user{ 'myuser'@'localhost':
  ensure                   => 'present',
  plugin                   => 'unix_socket',
}
```

ユーザに対しTLSオプションを指定できます。

```puppet
mysql_user{ 'myuser'@'localhost':
  ensure                   => 'present',
  tls_options              => ['SSL'],
}
```

##### `name`

ユーザ名('username@hostname'またはusername@hostname)。

##### `password_hash`

ユーザのパスワードハッシュ。このようなハッシュを作成するには、mysql_password()を使用してください。

##### `max_user_connections`

同時に接続するユーザ数の最大値。

整数値でなければなりません。

指定値が'0'の場合は無制限(またはグローバル)になります。

##### `max_connections_per_hour`

ユーザの1時間あたりの接続回数最大値。

整数値でなければなりません。

指定値が'0'の場合は無制限(またはグローバル)になります。

##### `max_queries_per_hour`

ユーザの1時間あたりのクエリ数最大値。

整数値でなければなりません。

指定値が'0'の場合は無制限(またはグローバル)になります。

##### `max_updates_per_hour`

ユーザの1時間あたりの更新回数最大値。

整数値でなければなりません。

指定値が'0'の場合は無制限(またはグローバル)になります。

##### `tls_options`

1つ以上のtls_optionの値を使用するMySQLアカウント用のSSL関連のオプション。'NONE'の場合はアカウントにTLSオプションが指定されません。使用可能なオプションは、MySQLドキュメントに示されているとおり、'SSL'、'X509'、'CIPHER *cipher*'、'ISSUER *issuer*'、'SUBJECT *subject*'です。


#### mysql_grant

`mysql_grant`は、MySQLでデータベースにアクセスするのに必要なGRANT権限を作成します。MySQLでデータベースにアクセスするためのGRANT権限を作成するには、`username@hostname/database.table`のパターンに続けて次のようにリソースのタイトルを作成する必要があります。

```puppet
mysql_grant { 'root@localhost/*.*':
  ensure     => 'present',
  options    => ['GRANT'],
  privileges => ['ALL'],
  table      => '*.*',
  user       => 'root@localhost',
}
```

次のように、列レベルまで詳細に権限を指定することができます。

```puppet
mysql_grant { 'root@localhost/mysql.user':
  ensure     => 'present',
  privileges => ['SELECT (Host, User)'],
  table      => 'mysql.user',
  user       => 'root@localhost',
}
```

GRANT権限を取り消す場合は['NONE']を指定します。

##### `ensure`

リソースの存在を指定します。

有効な値：'present'、'absent'。

デフォルト値：'present'。

##### `name`

GRANT権限を示す名前。'user/table'の形式でなければなりません。

##### `privileges`

ユーザに許可を与える権限。

##### `table`

権限が適用されるテーブル。

##### `user`

権限が付与されるユーザ。

##### `options`

権限を付与するMySQLオプション(オプション)。

#### mysql_plugin

`mysql_plugin`を使用してMySQLサーバにプラグインをロードできます。

```puppet
mysql_plugin { 'auth_socket':
  ensure     => 'present',
  soname     => 'auth_socket.so',
}
```

##### `ensure`

リソースの存在を指定します。

有効な値：'present'、'absent'。

デフォルト値：'present'。

##### `name`

管理するMySQLプラグインの名前。

#####  `soname`

ライブラリファイルの名前。

#### `mysql_datadir`

バージョンに固有なコードでMySQLデータディレクトリを初期化します。MySQL 5.7.6より前のバージョンではmysql_install_dbを、後のバージョンではmysqld --initialize-insecureを使用します。

安全でない初期化が必要なのは、mysqldバージョン5.7で'secure by default'モードが導入されているからです。これは、MySQLがランダムなパスワードを作成してSTDOUTに書き込むことを意味します。したがって、使用可能な認証情報がないためPuppetが後でデータベースサーバにアクセスすることはできません。

このタイプは内部タイプであるため、直接呼び出すことはできません。

### ファクト

#### `mysql_version`

`mysql --version`からの出力を解析してMySQLのバージョンを判断します。

#### `mysql_server_id`

ノードのMACアドレスに基づいて、`server_id`として使用可能な一意なIDを作成します。ループバックインターフェイスしかないノードでは、このファクトは*常に*`0`を返します。これらのノードは外部に接続されていないため、これが衝突の原因になる可能性はありません。

## 制約事項

このモジュールは以下のプラットフォームでテストされています。

* RedHat Enterprise Linux 5、6、7
* Debian 6、7、8
* CentOS 5、 6、7
* Ubuntu 10.04、12.04、14.04、16.04
* Scientific Linux 5、6
* SLES 11

他のプラットフォームでは最小限のテストしか行っていないため、保証はできません。

**注意：** mysqlbackup.shは、MySQL 5.7以降では動作せず、サポートされていません。

## 開発

Puppet Forge上のPuppetモジュールはオープンプロジェクトであり、その価値を維持するにはコミュニティからの貢献が欠かせません。Puppetが提供する膨大な数のプラットフォームや、無数のハードウェア、ソフトウェア、デプロイ設定に弊社がアクセスすることは不可能です。

弊社は、できるだけ変更に貢献しやすくして、弊社のモジュールがユーザの環境で機能する状態を維持したいと考えています。弊社では、状況を把握できるよう、貢献者に従っていただくべきいくつかのガイドラインを設けています。

弊社の詳細な[モジュール貢献についてのガイドライン](https://docs.puppetlabs.com/forge/contributing.html)をご確認ください。

### 作成者

このモジュールは、David Schmittが作成したものをベースにして、以下の作成者による貢献内容が加えられています(Puppet Labsを除く)。

* Larry Ludwig
* Christian G. Warden
* Daniel Black
* Justin Ellison
* Lowe Schmidt
* Matthias Pigulla
* William Van Hevelingen
* Michael Arnold
* Chris Weyl
* Daniël van Eeden
* Jan-Otto Kröpke