# postgresql

#### 目次

1. [モジュールの概要 - モジュールの機能](#module-description)
2. [セットアップ - postgresqlモジュール導入の基本](#setup)
    * [postgresqlの影響](#what-postgresql-affects)
    * [postgresqlの導入](#getting-started-with-postgresql)
3. [使用方法 - 設定オプションと追加機能](#usage)
    * [サーバーの設定](#configure-a-server)
    * [データベースの作成](#create-a-database)
    * [ユーザ、ロール、パーミッションの管理](#manage-users-roles-and-permissions)
    * [DBオブジェクトの所有権の管理](#manage-ownership-of-db-objects)
    * [デフォルトのオーバーライド](#override-defaults)
    * [pg_hba.confのアクセスルールの作成](#create-an-access-rule-for-pg_hbaconf)
    * [pg_ident.confのユーザ名マップの作成](#create-user-name-maps-for-pg_identconf)
    * [接続の検証](#validate-connectivity)
4. [参考 - モジュールの機能と動作について](#reference)
    * [クラス](#classes)
    * [定義できるタイプ](#defined-types)
    * [タイプ](#types)
    * [関数](#functions)
5. [制約事項 - OSの互換性など](#limitations)
6. [開発 - モジュール貢献についてのガイド](#development)
    * [コントリビュータ - モジュール貢献者の一覧](#contributors)
7. [テスト](#tests)
8. [コントリビュータ - モジュール貢献者の一覧](#contributors)

## モジュールの概要

postgresqlモジュールを使用すると、PuppetでPostgreSQLを管理できます。

PostgreSQLは、高性能な無償のオープンソースリレーショナルデータベースサーバーです。postgresqlモジュールを使用すると、PostgreSQLのパッケージ、サービス、データベース、ユーザ、一般的なセキュリティ設定を管理できるようになります。

## セットアップ

### postgresqlの影響

* PostgreSQLのパッケージ、サービス、設定ファイル
* リッスンするポート
* IPおよびマスク(オプション)

### postgresqlの導入

基本的なデフォルトのPostgreSQLサーバーを設定するには、`postgresql::server`クラスを宣言します。

```puppet
class { 'postgresql::server':
}
```

## 使用方法

### サーバーの設定

デフォルト設定を使用する場合は、上記のように`postgresql::server`クラスを宣言します。PostgreSQLサーバーの設定をカスタマイズするには、次のように、変更する[パラメータ](#postgresqlserver)を指定します。

```puppet
class { 'postgresql::server':
  ip_mask_deny_postgres_user => '0.0.0.0/32',
  ip_mask_allow_all_users    => '0.0.0.0/0',
  ipv4acls                   => ['hostssl all johndoe 192.168.0.0/24 cert'],
  postgres_password          => 'TPSrep0rt!',
}
```

設定後、コマンドラインで設定をテストします。

```shell
psql -h localhost -U postgres
psql -h my.postgres.server -U
```

上記のコマンドでエラーメッセージが返ってくる場合は、パーミッションの設定によって現在の接続元からのアクセスが制限されています。その場所からの接続を許可するかどうかに応じて、パーミッション設定の変更が必要な場合があります。

サーバー設定パラメータの詳細については、[PostgreSQLランタイム設定マニュアル](http://www.postgresql.org/docs/current/static/runtime-config.html)を参照してください。

### データベースの作成

さまざまなPostgreSQLデータベースを定義タイプ`postgresql::server::db`を使用してセットアップできます。例えば、PuppetDBのデータベースをセットアップするには、次のように記述します。

```puppet
class { 'postgresql::server':
}

postgresql::server::db { 'mydatabasename':
  user     => 'mydatabaseuser',
  password => postgresql_password('mydatabaseuser', 'mypassword'),
}
```

### ユーザ、ロール、パーミッションの管理

ユーザ、ロール、パーミッションを管理するには、次のようにします。

```puppet
class { 'postgresql::server':
}

postgresql::server::role { 'marmot':
  password_hash => postgresql_password('marmot', 'mypasswd'),
}

postgresql::server::database_grant { 'test1':
  privilege => 'ALL',
  db        => 'test1',
  role      => 'marmot',
}

postgresql::server::table_grant { 'my_table of test2':
  privilege => 'ALL',
  table     => 'my_table',
  db        => 'test2',
  role      => 'marmot',
}
```

この例では、test1データベース上とtest2データベースの`my_table`テーブル上の**すべての**権限を、指定したユーザまたはグループに付与します。値がPuppetDB設定ファイルに追加されると、このデータベースは使用可能になります。

### DBオブジェクトの所有権の管理

REASSIGN OWNEDを使用して、データベース内にあるすべてのオブジェクトの所有権を変更するには、次のようにします。

```puppet
postgresql::server::reassign_owned_by { 'new owner is meerkat':
  db        => 'test_db',
  old_owner => 'marmot',
  new_owner => 'meerkat',
}
```

この例では、PostgreSQLの'REASSIGN OWNED'ステートメントを実行して所有権を更新し、現在、ロール'marmot'が所有しているすべてのテーブル、シーケンス、関数、ビューが、ロール'meerkat'に所有されるようにします。

これは、指定された'test_db'内のオブジェクトに対してのみ適用されます。

バージョン9.3以上のPostgresqlでは、データベースの所有権も更新されます。

### デフォルトのオーバーライド

`postgresql::globals`クラスを使用すると、このモジュールの主な設定をグローバルに構成できます。この設定は、他のクラスや定義済みリソースから使用できます。単独では機能しません。

例えば、すべてのクラスのデフォルトの`locale`と`encoding`をオーバーライドするには、次のように記述します。

```puppet
class { 'postgresql::globals':
  encoding => 'UTF-8',
  locale   => 'en_US.UTF-8',
}

class { 'postgresql::server':
}
```

特定のバージョンのPostgreSQLパッケージを使用するには、次のように記述します。

```puppet
class { 'postgresql::globals':
  manage_package_repo => true,
  version             => '9.2',
}

class { 'postgresql::server':
}
```

### リモートのユーザ、ロール、パーミッションの管理

リモートのSQLオブジェクトは、ローカルのSQLオブジェクトと同じPuppetリソースと、[`connect_settings`](#connect_settings)ハッシュを使用して管理します。これは、PuppetがリモートのPostgresインスタンスに接続する方法と、SQLコマンドの生成に使用されるバージョンを制御します。

`connect_settings`ハッシュには、'PGHOST'、'PGPORT'、'PGPASSWORD'、'PGSSLKEY'など、Postgresクライアント接続を制御する環境変数を含めることができます。変数の全リストについては、[PostgreSQL環境変数](http://www.postgresql.org/docs/9.4/static/libpq-envars.html)マニュアルを参照してください。

さらに、特殊値の'DBVERSION'により、ターゲットデータベースのバージョンを指定できます。`connect_settings`ハッシュが省略されているか空の場合、PuppetはローカルのPostgreSQLインスタンスに接続します。

Puppetリソースごとに`connect_settings`ハッシュを設定するか、`postgresql::globals`にデフォルトの`connect_settings`ハッシュを設定できます。リソースごとに`connect_settings`を設定すると、SQLオブジェクトが複数のユーザによって複数のデータベース上に作成できるようになります。

```puppet
$connection_settings_super2 = {
  'PGUSER'     => 'super2',
  'PGPASSWORD' => 'foobar2',
  'PGHOST'     => '127.0.0.1',
  'PGPORT'     => '5432',
  'PGDATABASE' => 'postgres',
}

include postgresql::server

# Connect with no special settings, i.e domain sockets, user postgres
postgresql::server::role { 'super2':
  password_hash    => 'foobar2',
  superuser        => true,

  connect_settings => {},
}

# Now using this new user connect via TCP
postgresql::server::database { 'db1':
  connect_settings => $connection_settings_super2,
  require          => Postgresql::Server::Role['super2'],
}
```

### pg_hba.confのアクセスルールの作成

`pg_hba.conf`のアクセスルールを作成するには、次のように記述します。

```puppet
postgresql::server::pg_hba_rule { 'allow application network to access app database':
  description => 'Open up PostgreSQL for access from 200.1.2.0/24',
  type        => 'host',
  database    => 'app',
  user        => 'app',
  address     => '200.1.2.0/24',
  auth_method => 'md5',
}
```

これにより、以下のようなルールセットが`pg_hba.conf`内に作成されます。

```
# Rule Name: allow application network to access app database
# Description: Open up PostgreSQL for access from 200.1.2.0/24
# Order: 150
host  app  app  200.1.2.0/24  md5
```

デフォルトでは、`pg_hba_rule`に`postgresql::server`を含める必要がありますが、ルールを宣言する際にtargetおよびpostgresql_versionを設定することで、その動作をオーバーライドできます。例えば次のようになります。

```puppet
postgresql::server::pg_hba_rule { 'allow application network to access app database':
  description        => 'Open up postgresql for access from 200.1.2.0/24',
  type               => 'host',
  database           => 'app',
  user               => 'app',
  address            => '200.1.2.0/24',
  auth_method        => 'md5',
  target             => '/path/to/pg_hba.conf',
  postgresql_version => '9.4',
}
```

### pg_ident.confのユーザ名マップの作成

pg_ident.confのユーザ名マップを作成するには、次のように記述します。

```puppet
postgresql::server::pg_ident_rule { 'Map the SSL certificate of the backup server as a replication user':
  map_name          => 'sslrepli',
  system_username   => 'repli1.example.com',
  database_username => 'replication',
}
```

これにより、次のようなユーザ名マップが`pg_ident.conf`に作成されます。

```
#Rule Name: Map the SSL certificate of the backup server as a replication user
#Description: none
#Order: 150
sslrepli  repli1.example.com  replication
```

### リカバリ設定の作成

リカバリ設定ファイル(`recovery.conf`)を作成するには、次のように記述します。

```puppet
postgresql::server::recovery { 'Create a recovery.conf file with the following defined parameters':
  restore_command           => 'cp /mnt/server/archivedir/%f %p',
  archive_cleanup_command   => undef,
  recovery_end_command      => undef,
  recovery_target_name      => 'daily backup 2015-01-26',
  recovery_target_time      => '2015-02-08 22:39:00 EST',
  recovery_target_xid       => undef,
  recovery_target_inclusive => true,
  recovery_target           => 'immediate',
  recovery_target_timeline  => 'latest',
  pause_at_recovery_target  => true,
  standby_mode              => 'on',
  primary_conninfo          => 'host=localhost port=5432',
  primary_slot_name         => undef,
  trigger_file              => undef,
  recovery_min_apply_delay  => 0,
}
```

これにより、次の`recovery.conf`設定ファイルが作成されます。

```
restore_command = 'cp /mnt/server/archivedir/%f %p'
recovery_target_name = 'daily backup 2015-01-26'
recovery_target_time = '2015-02-08 22:39:00 EST'
recovery_target_inclusive = true
recovery_target = 'immediate'
recovery_target_timeline = 'latest'
pause_at_recovery_target = true
standby_mode = 'on'
primary_conninfo = 'host=localhost port=5432'
recovery_min_apply_delay = 0
```

テンプレートでは、指定されたパラメータのみが認識されます。`recovery.conf`は、少なくとも1つのパラメータが設定済みで、**かつ**、[manage_recovery_conf](#manage_recovery_conf)がtrueの場合のみ作成されます。

### 接続の検証

従属タスクを開始する前に、リモートのPostgreSQLデータベースへのクライアント接続を検証するには、`postgresql_conn_validator`リソースを使用します。このリソースは、PostgreSQLクライアントソフトウェアがインストールされている任意のノード上で使用できます。アプリケーションサーバーの起動や、データベース移行の実行など、他のタスクと結合されることがよくあります。

使用例:

```puppet
postgresql_conn_validator { 'validate my postgres connection':
  host              => 'my.postgres.host',
  db_username       => 'mydbuser',
  db_password       => 'mydbpassword',
  db_name           => 'mydbname',
}->
exec { 'rake db:migrate':
  cwd => '/opt/myrubyapp',
}
```

## 参考

postgresqlモジュールには、サーバー設定用に多数のオプションがあります。以下の設定をすべて使うことはないかもしれませんが、これらを使用することで、セキュリティ設定をかなり制御することができます。

**クラス:**

* [postgresql::client](#postgresqlclient)
* [postgresql::globals](#postgresqlglobals)
* [postgresql::lib::devel](#postgresqllibdevel)
* [postgresql::lib::java](#postgresqllibjava)
* [postgresql::lib::perl](#postgresqllibperl)
* [postgresql::lib::python](#postgresqllibpython)
* [postgresql::server](#postgresqlserver)
* [postgresql::server::plperl](#postgresqlserverplperl)
* [postgresql::server::contrib](#postgresqlservercontrib)
* [postgresql::server::postgis](#postgresqlserverpostgis)

**定義できるタイプ:**

* [postgresql::server::config_entry](#postgresqlserverconfig_entry)
* [postgresql::server::database](#postgresqlserverdatabase)
* [postgresql::server::database_grant](#postgresqlserverdatabase_grant)
* [postgresql::server::db](#postgresqlserverdb)
* [postgresql::server::extension](#postgresqlserverextension)
* [postgresql::server::grant](#postgresqlservergrant)
* [postgresql::server::grant_role](#postgresqlservergrant_role)
* [postgresql::server::pg_hba_rule](#postgresqlserverpg_hba_rule)
* [postgresql::server::pg_ident_rule](#postgresqlserverpg_ident_rule)
* [postgresql::server::reassign_owned_by](#postgresqlserverreassign_owned_by)
* [postgresql::server::recovery](#postgresqlserverrecovery)
* [postgresql::server::role](#postgresqlserverrole)
* [postgresql::server::schema](#postgresqlserverschema)
* [postgresql::server::table_grant](#postgresqlservertable_grant)
* [postgresql::server::tablespace](#postgresqlservertablespace)

**タイプ:**

* [postgresql_psql](#custom-resource-postgresql_psql)
* [postgresql_replication_slot](#custom-resource-postgresql_replication_slot)
* [postgresql_conf](#custom-resource-postgresql_conf)
* [postgresql_conn_validator](#custom-resource-postgresql_conn_validator)

**関数:**

* [postgresql_password](#function-postgresql_password)
* [postgresql_acls_to_resources_hash](#function-postgresql_acls_to_resources_hashacl_array-id-order_offset)

### クラス

#### postgresql::client

PostgreSQLクライアントソフトウェアをインストールします。カスタムのバージョンをインストールするには、次のパラメータを設定します。

>**注意:** カスタムのバージョンを指定する場合、必要なyumまたはaptリポジトリを忘れずに追加してください。

##### `package_ensure`

PostgreSQLクライアントパッケージリソースが存在する必要があるかどうかを指定します。

有効な値: 'present'、'absent'。

デフォルト値: 'present'。

##### `package_name`

PostgreSQLクライアントパッケージの名前を設定します。

デフォルト値: 'file'。

#### postgresql::lib::docs

Postgres-Docs向けのPostgreSQLバインディングをインストールします。カスタムのバージョンをインストールするには、次のパラメータを設定します。

**注意:** カスタムのバージョンを指定する場合、必要なyumまたはaptリポジトリを忘れずに追加してください。

##### `package_name`

PostgreSQL docsパッケージの名前を指定します。

##### `package_ensure`

PostgreSQL docsパッケージリソースが存在する必要があるかどうかを指定します。

有効な値: 'present'、'absent'。

デフォルト値: 'present'。

#### postgresql::globals

**注意:** ほとんどのサーバー固有のデフォルト値は、`postgresql::server`クラスでオーバーライドする必要があります。このクラスは、標準以外のOSを使用している場合か、ここでしか変更できない要素(`version`や`manage_package_repo`)を変更する場合のみ使用します。

##### `bindir`

ターゲットプラットフォームのデフォルトのPostgreSQLバイナリディレクトリをオーバーライドします。

デフォルト値: OSによって異なります。

##### `client_package_name`

デフォルトのPostgreSQLクライアントパッケージ名をオーバーライドします。

デフォルト値: OSによって異なります。

##### `confdir`

ターゲットプラットフォームのデフォルトのPostgreSQL設定ディレクトリをオーバーライドします。

デフォルト値: OSによって異なります。

##### `contrib_package_name`

デフォルトのPostgreSQL contribパッケージ名をオーバーライドします。

デフォルト値: OSによって異なります。

##### `createdb_path`

**非推奨** `createdb`コマンドへのパス。

デフォルト値: '${bindir}/createdb'。

##### `datadir`

ターゲットプラットフォームのデフォルトのPostgreSQLデータディレクトリをオーバーライドします。

デフォルト値: OSによって異なります。

**注意:** インストール後にdatadirを変更すると、変更が実行される前にサーバーが完全に停止します。Red Hatシステムでは、データディレクトリはSELinuxに適切にラベル付けする必要があります。Ubuntuでは、明示的に`needs_initdb = true`に設定して、Puppetが新しいdatadir内のデータベースを初期化できるようにする必要があります(他のシステムでは、`needs_initdb`はデフォルトでtrueになっています)。

**警告:** datadirがデフォルトから変更された場合、Puppetは元のデータディレクトリのパージを管理しません。そのため、データディレクトリが元のディレクトリに戻ったときにエラーが発生します。

##### `data_checksums`

オプションです。

データタイプ: 真偽値(boolean)

データページに対してチェックサムを使用すると、その他の方法では発見の難しいI/Oシステムによる破損を検出するのに役立ちます。

有効な値: `true`、`false`。

デフォルト値: initdbのデフォルト値(`false`)。

**警告:** このオプションは、initdbによって初期化中に使用され、後から変更することはできません。設定された時点で、すべてのデータベース内のすべてのオブジェクトに対してチェックサムが計算されます。

##### `default_database`

接続するデフォルトのデータベースの名前を指定します。

デフォルト値: (ほとんどのシステムにおいて) 'postgres'。

##### `devel_package_name`

デフォルトのPostgreSQL develパッケージ名をオーバーライドします。

デフォルト値: OSによって異なります。

##### `docs_package_name`

オプションです。

デフォルトのPostgreSQL docsパッケージ名をオーバーライドします。

デフォルト値: OSによって異なります。

##### `encoding`

このモジュールで作成されるすべてのデータベースのデフォルトエンコーディングを設定します。オペレーティングシステムによっては、`template1` の初期化にも使用されます。その場合、モジュール外部のデフォルトにもなります。

デフォルト値: オペレーティングシステムのデフォルトエンコーディングによって決まります。

##### `group`

ファイルシステムの関連ファイルに使用されるデフォルトのpostgresユーザグループをオーバーライドします。

デフォルト値: 'postgres'。

##### `initdb_path`

`initdb`コマンドへのパス。

##### `java_package_name`

デフォルトのPostgreSQL javaパッケージ名をオーバーライドします。

デフォルト値: OSによって異なります。

##### `locale`

このモジュールで作成されるすべてのデータベースのデフォルトのデータベースロケールを設定します。オペレーティングシステムによっては、`template1` の初期化にも使用されます。その場合、モジュール外部のデフォルトにもなります。

デフォルト値: `undef`、実質的には'C'。

**Debianでは、PostgreSQLのフル機能が使用できるように'locales-all'パッケージがインストールされていることを確認する必要があります。**

##### `timezone`

postgresqlサーバーのデフォルトタイムゾーンを設定します。postgresqlのビルトインのデフォルト値は、システムのタイムゾーン情報を取得しています。

##### `logdir`

デフォルトのPostgreSQL logディレクトリをオーバーライドします。

デフォルト値: initdbのデフォルトパス。

##### `manage_package_repo`

`true`に設定されている場合、お使いのホスト上に公式なPostgreSQLリポジトリをセットアップします。

デフォルト値: `false`。

##### `module_workdir`

psqlコマンドを実行する作業ディレクトリを指定します。'/tmp'がnoexecオプションでマウントされたボリューム上にあるときに、指定が必要になる場合があります。

デフォルト値: '/tmp'。

##### `needs_initdb`

サーバーパッケージをインストール後、PostgreSQLサービスを開始する前に、initdb動作を明示的に呼び出します。

デフォルト値: OSによって異なります。

##### `perl_package_name`

デフォルトのPostgreSQL Perlパッケージ名をオーバーライドします。

デフォルト値: OSによって異なります。

##### `pg_hba_conf_defaults`

`false`に設定すると、`pg_hba.conf`についてモジュールに設定されたデフォルト値を無効にします。デフォルト値をオーバーライドするときに役立ちます。ただし、基本的な`psql`動作など、一定の動作を行うためには一定のアクセスが要求されるので、ここでの変更内容がその他のモジュールと矛盾しないように注意してください。

デフォルト値: `postgresql::globals::manage_pg_hba_conf`に設定されたグローバル値。デフォルトは`true`。

##### `pg_hba_conf_path`

`pg_hba.conf`ファイルへのパスを指定します。

デフォルト値: '${confdir}/pg_hba.conf'。

##### `pg_ident_conf_path`

`pg_ident.conf`ファイルへのパスを指定します。

デフォルト値: '${confdir}/pg_ident.conf'。

##### `plperl_package_name`

デフォルトのPostgreSQL PL/Perlパッケージ名をオーバーライドします。

デフォルト値: OSによって異なります。

##### `plpython_package_name`

デフォルトのPostgreSQL PL/Pythonパッケージ名をオーバーライドします。

デフォルト値: OSによって異なります。

##### `postgis_version`

PostGISをインストールする場合に、インストールするPostGISのバージョンを定義します。

デフォルト値: インストールするPostgreSQLで利用可能な最下位のバージョン。

##### `postgresql_conf_path`

`postgresql.conf`ファイルへのパスを設定します。

デフォルト値: '${confdir}/postgresql.conf'。

##### `psql_path`

`psql`コマンドへのパスを設定します。

##### `python_package_name`

デフォルトのPostgreSQL Pythonパッケージ名をオーバーライドします。

デフォルト値: OSによって異なります。

##### `recovery_conf_path`

`recovery.conf`ファイルへのパス。

##### `repo_proxy`

公式のPostgreSQL yumリポジトリのみのプロキシオプションを設定します。これは、サーバーが企業のファイアウォール内にあり、外部への接続にプロキシを使用する必要がある場合に役立ちます。

Debianは現在サポートされていません。

##### `repo_baseurl`

PostgreSQLリポジトリのbaseurlを設定します。リポジトリのミラーを所有している場合に便利です。

デフォルト値: 公式なPostgreSQLリポジトリ。

##### `server_package_name`

デフォルトのPostgreSQLサーバーパッケージ名をオーバーライドします。

デフォルト値: OSによって異なります。

##### `service_name`

デフォルトのPostgreSQLサービス名をオーバーライドします。

デフォルト値: OSによって異なります。

##### `service_provider`

デフォルトのPostgreSQLサービスプロバイダをオーバーライドします。

デフォルト値: OSによって異なります。

##### `service_status`

PostgreSQLサービスのデフォルトのステータスチェックコマンドをオーバーライドします。

デフォルト値: OSによって異なります。

##### `user`

ファイルシステム内のPostgreSQL関連ファイルのデフォルトのPostgreSQLスーパーユーザおよび所有者をオーバーライドします。

デフォルト値: 'postgres'。

##### `version`

インストールおよび管理するPostgreSQLのバージョン。

デフォルト値: OSシステムのデフォルト値。

##### `xlogdir`

デフォルトのPostgreSQL xlogディレクトリをオーバーライドします。

デフォルト値: initdbのデフォルトパス。

#### postgresql::lib::devel

PostgreSQLの開発ライブラリとシンボリックリンク`pg_config`を含むパッケージを`/usr/bin`にインストールします(`/usr/bin`または`/usr/local/bin`に存在しない場合)。

##### `link_pg_config`

PostgreSQLページが使用するbinディレクトリが`/usr/bin`でも`/usr/local/bin`でもない場合、パッケージのbinディレクトリから`usr/bin`に`pg_config`をシンボリックリンクします(Debianシステムには適用されません)。この動作を無効にするには、`false`に設定します。

有効な値: `true`、`false`。

デフォルト値: `true`。

##### `package_ensure`

パッケージのインストール中に'ensure'パラメータをオーバーライドします。

デフォルト値: 'present'。

##### `package_name`

インストール先のディストリビューションのデフォルトパッケージ名をオーバーライドします。

デフォルト値: ディストリビューションに応じて、'postgresql-devel'または'postgresql<version>-devel'。

#### postgresql::lib::java

Java (JDBC)向けのPostgreSQLバインディングをインストールします。カスタムのバージョンをインストールするには、次のパラメータを設定します。

**注意:** カスタムのバージョンを指定する場合、必要なyumまたはaptリポジトリを忘れずに追加してください。

##### `package_ensure`

パッケージが存在するかどうかを指定します。

有効な値: 'present'、'absent'。

デフォルト値: 'present'。

##### `package_name`

PostgreSQL javaパッケージの名前を指定します。

#### postgresql::lib::perl

PostgreSQL Perlライブラリをインストールします。

##### `package_ensure`

パッケージが存在するかどうかを指定します。

有効な値: 'present'、'absent'。

デフォルト値: 'present'。

##### `package_name`

インストールするPostgreSQL perlパッケージの名前を指定します。

#### postgresql::server::plpython

PostgreSQLのPL/Python手続き型言語をインストールします。

##### `package_name`

postgresql PL/Pythonパッケージの名前を指定します。

##### `package_ensure`

パッケージが存在するかどうかを指定します。

有効な値: 'present'、'absent'。

デフォルト値: 'present'。

#### postgresql::lib::python

PostgreSQL Pythonライブラリをインストールします。

##### `package_ensure`

パッケージが存在するかどうかを指定します。

有効な値: 'present'、'absent'。

デフォルト値: 'present'。

##### `package_name`

PostgreSQL Pythonパッケージの名前。

#### postgresql::server

##### `createdb_path`

**非推奨** `createdb`コマンドへのパスを指定します。

デフォルト値: '${bindir}/createdb'。

##### `data_checksums`

オプションです。

データタイプ: 真偽値(boolean)

データページに対してチェックサムを使用すると、その他の方法では発見の難しいI/Oシステムによる破損を検出するのに役立ちます。

有効な値: `true`、`false`。

デフォルト値: initdbのデフォルト値(`false`)。

**警告:** このオプションは、initdbによって初期化中に使用され、後から変更することはできません。設定された時点で、すべてのデータベース内のすべてのオブジェクトに対してチェックサムが計算されます。

##### `default_database`

接続するデフォルトのデータベースの名前を指定します。ほとんどのシステムで、'postgres'になります。

##### `default_connect_settings`

リモートサーバーに接続する際に使用される環境変数のハッシュを指定します。他の定義タイプのデフォルトとして使用されます(`postgresql::server::role`など)。

##### `encoding`

このモジュールで作成されるすべてのデータベースのデフォルトエンコーディングを設定します。オペレーティングシステムによっては、`template1` の初期化にも使用されます。その場合、モジュール外部のデフォルトにもなります。

デフォルト値: `undef`。

##### `group`

ファイルシステムの関連ファイルに使用されるデフォルトのpostgresユーザグループをオーバーライドします。

デフォルト値: OSによって異なります。

##### `initdb_path`

`initdb`コマンドへのパスを指定します。

デフォルト値: '${bindir}/initdb'。

##### `ipv4acls`

接続方法、ユーザ、データベース、IPv4アドレスのアクセス制御のための文字列を一覧表示します。

詳細については、[PostgreSQLマニュアル](http://www.postgresql.org/docs/current/static/auth-pg-hba-conf.html)の`pg_hba.conf`の項を参照してください。

##### `ipv6acls`

接続方法、ユーザ、データベース、IPv6アドレスのアクセス制御のための文字列を一覧表示します。

詳細については、[PostgreSQLマニュアル](http://www.postgresql.org/docs/current/static/auth-pg-hba-conf.html)の`pg_hba.conf`の項を参照してください。

##### `ip_mask_allow_all_users`

リモート接続に関するPostgreSQLのデフォルト動作をオーバーライドします。デフォルトでは、PostgreSQLは、データベースユーザアカウントがリモートマシンからTCP経由で接続することを許可しません。許可するには、この設定をオーバーライドします。

データベースユーザによる任意のリモートマシンからの接続を許可するには、'0.0.0.0/0'に設定します。ローカルの'192.168'サブネット内の任意のマシンからの接続を許可するには、'192.168.0.0/1'に設定します。

デフォルト値: '127.0.0.1/32'。

##### `ip_mask_deny_postgres_user`

postgresスーパーユーザについて、リモート接続を拒否するためのIPマスクを指定します。

デフォルト値: '0.0.0.0/0'。デフォルト値ではリモート接続はすべて拒否されます。

##### `locale`

このモジュールで作成されるすべてのデータベースのデフォルトのデータベースロケールを設定します。オペレーティングシステムによっては、`template1` の初期化にも使用されます。その場合、モジュール外部のデフォルトになります。

デフォルト値: `undef`、実質的には'C'。

**Debianでは、PostgreSQLの全機能を使用できるよう、'locales-all'パッケージがインストールされていることを確認してください。**

##### `manage_pg_hba_conf`

`pg_hba.conf`を管理するかどうかを指定します。

`true`に設定すると、Puppetはこのファイルを上書きします。

`false`に設定すると、Puppetはこのファイルに変更を加えません。

有効な値: `true`、`false`。

デフォルト値: `true`

##### `manage_pg_ident_conf`

pg_ident.confファイルを上書きします。

`true`に設定すると、Puppetはこのファイルを上書きします。

`false`に設定すると、Puppetはこのファイルに変更を加えません。

有効な値: `true`、`false`。

デフォルト値: `true`。

##### `manage_recovery_conf`

`recovery.conf`を管理するかどうかを指定します。

`true`に設定すると、Puppetはこのファイルを上書きします。

有効な値: `true`、`false`。

デフォルト値: `false`。

##### `needs_initdb`

サーバーパッケージをインストール後、PostgreSQLサービスを開始する前に、`initdb`動作を明示的に呼び出します。

デフォルト値: OSによって異なります。

##### `package_ensure`

サーバーインスタンスを作成するときに、`package`リソースに値を受け渡します。

デフォルト値: `undef`。

##### `package_name`

サーバーソフトウェアをインストールするときに使用するパッケージの名前を指定します。

デフォルト値: OSによって異なります。

##### `pg_hba_conf_defaults`

`false`に設定すると、`pg_hba.conf`についてモジュールに設定されたデフォルト値を無効にします。これは、デフォルト値を使用せずにオーバーライドするときに役立ちます。だし、基本的な`psql`動作などを実行するには一定のアクセスが要求されるので、ここでの変更内容がその他のモジュールと矛盾しないように注意してください。

##### `pg_hba_conf_path`

`pg_hba.conf`ファイルへのパスを指定します。

##### `pg_ident_conf_path`

`pg_ident.conf`ファイルへのパスを指定します。

デフォルト値: '${confdir}/pg_ident.conf'。

##### `plperl_package_name`

PL/Perl拡張のデフォルトパッケージ名を設定します。

デフォルト値: OSによって異なります。

##### `plpython_package_name`

PL/Python拡張のデフォルトパッケージ名を設定します。

デフォルト値: OSによって異なります。

##### `port`

PostgreSQLサーバーがリッスンするポートを指定します。**注意:** サーバーがリッスンする全IPアドレスで、同一のポート番号が使用されます。また、Red Hatシステムと初期のDebianシステムでは、ポート番号を変更するとき、変更実行前にサーバーが完全停止します。

デフォルト値: 5432。これは、PostgresサーバーがTCPポート5432をリッスンすることを意味します。

##### `postgres_password`

postgresユーザのパスワードを特定の値に設定します。デフォルトでは、この設定はPostgresデータベース内のスーパーユーザアカウント(ユーザ名`postgres`、パスワードなし)を使用します。

デフォルト値: `undef`。

##### `postgresql_conf_path`

`postgresql.conf`ファイルへのパスを指定します。

デフォルト値: '${confdir}/postgresql.conf'。

##### `psql_path`

`psql`コマンドへのパスを指定します。

デフォルト値: OSによって異なります。

##### `service_manage`

Puppetがサービスを管理するかどうかを定義します。

デフォルト値: `true`。

##### `service_name`

デフォルトのPostgreSQLサービス名をオーバーライドします。

デフォルト値: OSによって異なります。

##### `service_provider`

デフォルトのPostgreSQLサービスプロバイダをオーバーライドします。

デフォルト値: `undef`。

##### `service_reload`

PostgreSQLサービスのデフォルトのリロードコマンドをオーバーライドします。

デフォルト値: OSによって異なります。

##### `service_restart_on_change`

設定変更をアクティブにするにはサービスの再起動が必要な設定エントリが変更された場合に、PostgreSQLサービスを再起動する際のデフォルト動作をオーバーライドします。

デフォルト値: `true`。

##### `service_status`

PostgreSQLサービスのデフォルトのステータスチェックコマンドをオーバーライドします。

デフォルト値: OSによって異なります。

##### `user`

ファイルシステム内のPostgreSQL関連ファイルのデフォルトのPostgreSQLスーパーユーザおよびオーナーをオーバーライドします。

デフォルト値: 'postgres'。

#### postgresql::server::contrib

PostgreSQL contribパッケージをインストールします。

##### `package_ensure`

PostgreSQL contribパッケージリソースに受け渡されたensureパラメータを設定します。

##### `package_name`

PostgreSQL contribパッケージの名前。

#### postgresql::server::plperl

postgresqlのPL/Perl手続き型言語をインストールします。

##### `package_ensure`

PostgreSQL PL/Perlパッケージリソースに受け渡されたensureパラメータ。

##### `package_name`

PostgreSQL PL/Perlパッケージの名前。

#### postgresql::server::postgis

PostgreSQL postgisパッケージをインストールします。

### 定義できるタイプ

#### postgresql::server::config_entry

`postgresql.conf`設定ファイルを変更します。

各リソースは、次の例のようにファイル内の各行にマッピングされています。

```puppet
postgresql::server::config_entry { 'check_function_bodies':
  value => 'off',
}
```

##### `ensure`

'absent'に設定した場合、エントリを削除します。

有効な値: 'present'、'absent'。

デフォルト値: 'present'。

##### `value`

設定の値を定義します。

#### postgresql::server::db

ローカルのデータベース、ユーザを作成し、必要なパーミッションを割り当てます。

##### `comment`

PostgreSQLのCOMMENTコマンドを使用して、データベースについて保存するコメントを定義します。

##### `connect_settings`

リモートサーバーに接続する際に使用される環境変数のハッシュを指定します。

デフォルト値: ローカルのPostgresインスタンスに接続します。

##### `dbname`

作成するデータベースの名前を設定します。

デフォルト値: namevar。

##### `encoding`

データベースの作成中の文字セットをオーバーライドします。

デフォルト値: インストール時に定義されたデフォルト値。

##### `grant`

作成中に付与するパーミッションを指定します。

デフォルト値: 'ALL'。

##### `istemplate`

`true`に設定すると、そのデータベースをテンプレートとして指定します。 

デフォルト値: `false`。

##### `locale`

データベース作成中にロケールをオーバーライドします。

デフォルト値: インストール時に定義されたデフォルト値。

##### `owner`

ユーザをデータベースの所有者として設定します。

デフォルト値: `postgresql::server`または`postgresql::globals`で設定された'$user'変数。

##### `password`

**必須** 作成されたユーザのパスワードを設定します。

##### `tablespace`

作成したデータベースを割り当てるテーブル空間の名前を定義します。

デフォルト値: PostgreSQLのデフォルト値。

##### `template`

このデータベースを構築する際にテンプレートとして使用するデータベースの名前を指定します。

デフォルト値: `template0`。

##### `user`

データベースを作成し、作成後にデータベースへのアクセスを割り当てるユーザ。必須指定です。

#### postgresql::server::database

ユーザなし、パーミッションなしのデータベースを作成します。

##### `dbname`

データベースの名前を設定します。

デフォルト値: namevar。

##### `encoding`

データベースの作成中の文字セットをオーバーライドします。

デフォルト値: インストール時に定義されたデフォルト値。

##### `istemplate`

`true`に設定すると、そのデータベースをテンプレートとして定義します。

デフォルト値: `false`。

##### `locale`

データベース作成中にロケールをオーバーライドします。

デフォルト値: インストール時に定義されたデフォルト値。

##### `owner`

データベース所有者の名前を設定します。

デフォルト値: `postgresql::server`または`postgresql::globals`で設定された'$user'変数。

##### `tablespace`

このデータベースを作成するテーブル空間を設定します。

デフォルト値: インストール時に定義されたデフォルト値。

##### `template`

このデータベースを構築する際にテンプレートとして使用するデータベースの名前を指定します。

デフォルト値: 'template0'。

#### postgresql::server::database_grant

データベース固有のパーミッションについて`postgresql::server::database_grant`をラッピングして、grantベースのユーザアクセス権を管理します。詳細については、[PostgreSQLマニュアルの`grant`](http://www.postgresql.org/docs/current/static/sql-grant.html)を参照してください。

#### `connect_settings`

リモートサーバーに接続する際に使用される環境変数のハッシュを指定します。

デフォルト値: ローカルのPostgresインスタンスに接続します。

##### `db`

アクセス権を付与するデータベースを指定します。

##### `privilege`

付与する権限のコンマ区切りリストを指定します。

有効なオプション: 'ALL'、'CREATE'、'CONNECT'、'TEMPORARY'、'TEMP'。

##### `psql_db`

権限付与を実行するデータベースを定義します。

**通常、デフォルトを変更しないでください。**

デフォルト値: 'postgres'。

##### `psql_user`

`psql`を実行するOSユーザを指定します。

デフォルト値: モジュールのデフォルトユーザ。通常、'postgres'。

##### `role`

アクセスを付与するロールまたはユーザを指定します。

#### postgresql::server::extension

PostgreSQL拡張を管理します。

##### `database`

拡張を有効化するデータベースを指定します。

##### `ensure`

拡張を有効化するか無効化するかを指定します。

有効なオプション: 'present'または'absent'。

#### `extension`

有効化する拡張を指定します。空欄にした場合、リソースの名前が使用されます。

#### `version`

データベースが使用するエクステンションのバージョンを指定します。
拡張パッケージが更新された場合、各データベースで有効なバージョンを自動的に変更することはありません。

そのためには、PostgreSQLに固有のSQL `ALTER EXTENSION...`を使用して更新する必要があります

`version`は`latest`に設定できます。この場合、SQL `ALTER EXTENSION "extension" UPDATE`がこのデータベースのみに適用されます。

`version`は特定のバージョンに設定できます。この場合、拡張は`ALTER EXTENSION "extension" UPDATE TO 'version'`を使用して更新されます

例えば、拡張を`postgis`、バージョンを`2.3.3`に設定した場合、SQL `ALTER EXTENSION "postgis" UPDATE TO '2.3.3'`がこのデータベースのみに適用されます。

`version`は省略される場合もあります。この場合、SQL `ALTER EXTENSION...`は適用されません。バージョンは変更されず、そのままになります。

##### `package_name`

拡張を有効化する前にインストールするパッケージを指定します。

##### `package_ensure`

デフォルトのパッケージ削除動作をオーバーライドします。

デフォルトでは、`package_name`で指定されたパッケージが、拡張が有効のときインストールされ、拡張が無効のとき削除されます。この動作をオーバーライドするには、そのパッケージに`ensure`の値を設定してください。

#### postgresql::server::grant

ロールのgrantベースのアクセス権を管理します。詳細については、[PostgreSQLマニュアルの`grant`](http://www.postgresql.org/docs/current/static/sql-grant.html)を参照してください。

##### `db`

アクセス権を付与するデータベースを指定します。

##### `object_type`

権限を付与するオブジェクトのタイプを指定します。

有効なオプション: 'DATABASE'、'SCHEMA'、'SEQUENCE'、'ALL SEQUENCES IN SCHEMA'、'TABLE'、または'ALL TABLES IN SCHEMA'。

##### `object_name`

アクセス権を付与する`object_type`の名前を、文字列または2要素の配列で指定します。

String: 'object_name'
Array:  ['schema_name', 'object_name']

##### `port`

接続に使用するポート。

デフォルト値: `undef`。PostgreSQLのパッケージングに応じて、通常、デフォルトでポート5432になります。

##### `privilege`

付与する権限を指定します。

有効なオプション: 'ALL'、'ALL PRIVILEGES'、または'object_type'依存の文字列。

##### `psql_db`

権限付与を実行するデータベースを指定します。

**通常、デフォルトを変更しないでください。**

デフォルト値: 'postgres'。

##### `psql_user`

`psql`を実行するOSユーザを設定します。

デフォルト値: モジュールのデフォルトユーザ。通常、'postgres'。

##### `role`

アクセスを付与するロールまたはユーザを指定します。

#### postgresql::server::grant_role

ロールを(グループ)ロールに割り当てられるようにします。詳細については、[PostgreSQLマニュアルの`Role Membership`](http://www.postgresql.org/docs/current/static/role-membership.html)を参照してください。

##### `group`

ロールを割り当てるグループロールを指定します。

##### `role`

グループに割り当てるロールを指定します。空欄にした場合、リソースの名前が使用されます。

##### `ensure`

メンバーシップを付与するか、無効化するかを指定します。

有効なオプション: 'present'または'absent'。

デフォルト値: 'present'。

##### `port`

接続に使用するポート。

デフォルト値: `undef`。PostgreSQLのパッケージングに応じて、通常、デフォルトでポート5432になります。

##### `psql_db`

権限付与を実行するデータベースを指定します。

**通常、デフォルトを変更しないでください。**

デフォルト値: 'postgres'。

##### `psql_user`

`psql`を実行するOSユーザを設定します。

デフォルト値: モジュールのデフォルトユーザ。通常、`postgres`。

##### `connect_settings`

リモートサーバーへの接続時に使用する環境変数のハッシュを指定します。

デフォルト値: ローカルのPostgresインスタンスに接続します。

#### postgresql::server::pg_hba_rule

`pg_hba.conf`のアクセスルールを作成できるようにします。詳細については、[使用例](#create-an-access-rule-for-pghba.conf)および[PostgreSQLマニュアル](http://www.postgresql.org/docs/current/static/auth-pg-hba-conf.html)を参照してください。

##### `address`

タイプが'local'ではないとき、このルール一致に対するCIDRベースのアドレスを設定します。

##### `auth_method`

このルールが一致する接続の認証に使用される方法を提供します。詳細な説明は、PostgreSQL `pg_hba.conf`のマニュアルに記載されています。

##### `auth_option`

特定の`auth_method`設定については、受け渡し可能な追加オプションがあります。詳細については、PostgreSQL `pg_hba.conf`マニュアルを参照してください。

##### `database`

このルールが一致するデータベースのコンマ区切りリストを設定します。

##### `description`

必要に応じて、このルールの長めの説明を定義します。この説明は、`pg_hba.conf`のルール上部のコメント内に挿入されます。

デフォルト値: 'none'。

そのリソースを一意に識別するための方法を指定しますが、機能的には何も実行しません。

##### `order`

`pg_hba.conf`にルールを配置する順序を設定します。

デフォルト値: 150。

#### `postgresql_version`

PostgreSQLインスタンス全体を管理することなく、`pg_hba.conf`を管理します。

デフォルト値: `postgresql::server`に設定されたバージョン。

##### `target`

ルールのターゲットを提供します。通常、内部使用のみのプロパティです。

**注意して使用してください。**

##### `type`

ルールのタイプを設定します。

有効なオプション: 'local'、'host'、'hostssl'、または'hostnossl'。

##### `user`

このルールが一致するユーザのコンマ区切りリストを設定します。


#### postgresql::server::pg_ident_rule

`pg_ident.conf`のユーザ名マップを作成可能にします。詳細については、上述の[使用例](#create-user-name-maps-for-pgidentconf)および[PostgreSQLマニュアル](http://www.postgresql.org/docs/current/static/auth-username-maps.html)を参照してください。

##### `database_username`

データベースユーザのユーザ名を指定します。このユーザ名には`system_username`がマッピングされています。

##### `description`

必要に応じて、このルールの長めの説明を設定します。この説明は、`pg_ident.conf`のルール上部のコメント内に挿入されます。

デフォルト値: 'none'。

##### `map_name`

`pg_hba.conf`でこのマッピングを参照するために使用されるユーザマップの名前を設定します。

##### `order`

`pg_ident.conf`にマッピングを配置する際の順序を定義します。

デフォルト値: 150。

##### `system_username`

オペレーティングシステムのユーザ名(データベースへの接続に使用するユーザ名)を指定します。

##### `target`

ルールのターゲットを提供します。通常、内部使用のみのプロパティです。

**注意して使用してください。**

#### postgresql::server::reassign_owned_by

PostgreSQLコマンド'REASSIGN OWNED'をデータベースに対して実行し、既存オブジェクトの所有権を別のデータベースロールに移します。

##### `db`

 'REASSIGN OWNED'コマンドを適用するデータベースを指定します。

##### `old_role`

指定したデータベース内のオブジェクトを現在所有しているロールまたはユーザを指定します。

##### `new_role`

対象オブジェクトの新しい所有者となるロールまたはユーザを指定します。

##### `psql_user`

`psql`を実行するOSユーザを指定します。

デフォルト値: モジュールのデフォルトユーザ。通常、'postgres'。

##### `port`

接続に使用するポート。

デフォルト値: `undef`。PostgreSQLのパッケージングに応じて、通常、デフォルトでポート5432になります。

##### `connect_settings`

リモートサーバーへの接続時に使用する環境変数のハッシュを指定します。

デフォルト値: ローカルのPostgresインスタンスに接続します。

#### postgresql::server::recovery

`recovery.conf`の内容を作成可能にします。詳細については、[使用例](#create-recovery-configuration)および[PostgreSQLマニュアル](http://www.postgresql.org/docs/current/static/recovery-config.html)を参照してください。

`recovery_target_inclusive`、 `pause_at_recovery_target`、`standby_mode`、`recovery_min_apply_delay`を除くすべてのパラメータ値は、テンプレートに含まれる文字列セットです。

全パラメータリストの詳細な説明は、[PostgreSQLマニュアル](http://www.postgresql.org/docs/current/static/recovery-config.html)にあります。

パラメータは、次の3つのセクションにグループ分けされています。

##### [アーカイブリカバリパラメータ](http://www.postgresql.org/docs/current/static/archive-recovery-settings.html)

* `restore_command`
* `archive_cleanup_command`
* `recovery_end_command`

##### [Recovery Target Settings](http://www.postgresql.org/docs/current/static/recovery-target-settings.html)
* `recovery_target_name`
* `recovery_target_time`
* `recovery_target_xid`
* `recovery_target_inclusive`
* `recovery_target`
* `recovery_target_timeline`
* `pause_at_recovery_target`

##### [Standby Server Settings](http://www.postgresql.org/docs/current/static/standby-settings.html)
* `standby_mode`: 文字列('on'/'off')またはブール値(`true`/`false`)で指定できます。
* `primary_conninfo`
* `primary_slot_name`
* `trigger_file`
* `recovery_min_apply_delay`

##### `target`
ルールのターゲットを提供します。通常、内部使用のみのプロパティです。

**注意して使用してください。**

#### postgresql::server::role
PostgreSQLのロールまたはユーザを作成します。

##### `connection_limit`
ロールが同時に接続可能な数を指定します。

デフォルト値: '-1'。これは、無制限を意味します。

##### `connect_settings`
リモートサーバーへの接続時に使用する環境変数のハッシュを指定します。

デフォルト値: ローカルのPostgresインスタンスに接続します。

##### `createdb`
このロールに新しいデータベースを作成する能力を付与するかどうかを指定します。

デフォルト値: `false`。

##### `createrole`
このロールに新しいロールを作成する権限を付与するかどうかを指定します。

デフォルト値: `false`。

##### `inherit`
新しいロールに継承権限を付与するかどうかを指定します。

デフォルト値: `true`。

##### `login`
新しいロールにログイン権限を付与するかどうかを指定します。

デフォルト値: `true`。

##### `password_hash`
パスワード作成中に使用するハッシュを設定します。PostgreSQLがサポートする形式でパスワードが暗号化されていない場合、ここで、`postgresql_password`関数を使用して、MD5ハッシュを提供します。例は次のとおりです。

##### `update_password`
trueに設定すると、変更時にパスワードが更新されます。作成後にロールのパスワードを変更しない場合は、falseに設定してください。

```puppet
postgresql::server::role { 'myusername':
  password_hash => postgresql_password('myusername', 'mypassword'),
}
```

##### `replication`

`true`に設定すると、このロールにレプリケーション機能が提供されます。

デフォルト値: `false`。

##### `superuser`

新しいロールにスーパーユーザ権限を付与するかどうかを指定します。

デフォルト値: `false`。

##### `username`

作成するロールのユーザ名を定義します。

デフォルト値: namevar。

#### postgresql::server::schema

スキーマを作成します。

##### `connect_settings`

リモートサーバーへの接続時に使用する環境変数のハッシュを指定します。

デフォルト値: ローカルのPostgresインスタンスに接続します。

##### `db`

必須。

このスキーマを作成するデータベースの名前を設定します。

##### `owner`

スキーマのデフォルト所有者を設定します。

##### `schema`

スキーマの名前を設定します。

デフォルト値: namevar。

#### postgresql::server::table_grant

ユーザのgrantベースのアクセス権を管理します。詳細については、PostgreSQLマニュアルの`grant`の項を参照してください。

##### `connect_settings`

リモートサーバーへの接続時に使用する環境変数のハッシュを指定します。

デフォルト値: ローカルのPostgresインスタンスに接続します。

##### `db`

そのテーブルが存在するデータベースを指定します。

##### `privilege`

付与する権限のコンマ区切りリストを指定します。有効なオプション: 'ALL'、'SELECT'、'INSERT'、'UPDATE'、'DELETE'、'TRUNCATE'、'REFERENCES'、'TRIGGER'。

##### `psql_db`

権限付与を実行するデータベースを指定します。

通常、デフォルトを変更しないでください。

デフォルト値: 'postgres'。

##### `psql_user`

`psql`を実行するOSユーザを指定します。

デフォルト値: モジュールのデフォルトユーザ。通常、'postgres'。

##### `role`

アクセスを付与するロールまたはユーザを指定します。

##### `table`

アクセス権を付与するテーブルを指定します。

#### postgresql::server::tablespace

テーブル空間を作成します。必要な場合、場所も作成し、PostgreSQLサーバーと同じパーミッションを割り当てます。

##### `connect_settings`

リモートサーバーへの接続時に使用する環境変数のハッシュを指定します。

デフォルト値: ローカルのPostgresインスタンスに接続します。

##### `location`

このテーブル空間へのパスを指定します。

##### `owner`

そのテーブル空間のデフォルト所有者を指定します。

##### `spcname`

テーブル空間の名前を指定します。

デフォルト値: namevar。

### タイプ

#### postgresql_psql

Puppetがpsqlステートメントを実行できるようにします。

##### `command`

必須。

psqlを介して実行するSQLコマンドを指定します。

##### `cwd`

psqlコマンドが実行される作業ディレクトリを指定します。

デフォルト値: '/tmp'。

##### `db`

SQLコマンドを実行するデータベースの名前を指定します。

##### `environment`

SQLコマンドに対して追加の環境変数を設定する場合に指定します。複数の環境変数を使用する場合は、配列として指定します。

##### `name`

自身の参考用の任意のタグ、すなわちメッセージの名前を設定します。これはnamevarです。

##### `onlyif`

メインのコマンドの前に実行するオプションのSQLコマンドを設定します。通常、これはべき等性に基づいて、データベース内のオブジェクトの存在を確認し、メインのSQLコマンドを実行する必要があるかどうかを判断するため使用されます。

##### `port`

SQLコマンドを実行するデータベースサーバーのポートを指定します。

##### `psql_group`

psqlコマンドを実行するシステムユーザグループアカウントを指定します。

デフォルト値: 'postgres'。

##### `psql_path`

psql実行ファイルへのパスを指定します。

デフォルト値: 'psql'。

##### `psql_user`

psqlコマンドを実行するシステムユーザアカウントを指定します。

デフォルト値: 'postgres'。

##### `refreshonly`

notifyイベントまたはsubscribeイベントが発生したときのみSQLを実行するかどうかを指定します。

有効な値: `true`、`false`。

デフォルト値: `false`。

##### `search_path`

SQLコマンドを実行するときに使用するスキーマ検索パスを定義します。

##### `unless`

`onlyif`の逆です。

#### postgresql_conf

Puppetが`postgresql.conf`パラメータを管理できるようにします。

##### `name`

管理するPostgreSQLパラメータ名を指定します。

これはnamevarです。

##### `target`

`postgresql.conf`へのパスを指定します。

デフォルト値: '/etc/postgresql.conf'。

##### `value`

このパラメータに設定する値を指定します。

#### postgresql_replication_slot

PostgreSQLマスターサーバー上でウォームスタンバイレプリケーションを登録するためのレプリケーションスロットを作成および消去できるようにします。

##### `name`

作成するスロットの名前を指定します。有効なレプリケーションスロット名である必要があります。

これはnamevarです。

##### `ensure`

必須。

指定されたスロットに対して、作成または消去のいずれかのアクションを指定します。

有効な値: 'present'、'absent'。

デフォルト値: 'present'。

#### postgresql_conn_validator

このタイプを使用するローカルまたはリモートのPostgreSQLデータベースへの接続を検証します。

##### `connect_settings`

リモートサーバーへの接続時に使用する環境変数のハッシュを指定します。個々のパラメータ(`host`など)を設定する代わりに使用されますが、個々のパラメータが設定されている場合は個々のパラメータが優先されます。

デフォルト値: {}

##### `db_name`

テストするデータベースの名前を指定します。Specifies the name of the database you wish to test.

デフォルト値: ''

##### `db_password`

接続するパスワードを指定します。`.pgpass`が使用されている場合は空欄にできます。それ以外の場合、空欄にすることは推奨されません。

デフォルト値: ''

##### `db_username`

接続するユーザ名を指定します。

デフォルト値: ''

Unixソケットとident認証を使用するとき、このユーザとして実行されます。

##### `command`

接続性を検証するためにターゲットデータベースで実行されるコマンドです。

デフォルト値: 'SELECT 1'

##### `host`

テストするデータベースのホスト名を設定します。

デフォルト値: ''。これは、通常指定されたローカルUnixソケットを使用します。

**ホストがリモートの場合、ユーザ名を指定する必要があります。**

##### `port`

接続するときに使用するポートを定義します。

デフォルト値: '' 

##### `run_as`

`psql`コマンドの実行ユーザを指定します。これは、Unixソケットと`ident`認証を使用してローカルにデータベースに接続するときに重要です。リモートテストには必要ありません。

##### `sleep`

失敗した後、再試行する前にスリープする時間を秒単位で設定します。

##### `tries`

失敗した後、リソースを失敗とみなすまで再試行する回数を設定します。

### 関数

#### postgresql_password

PostgreSQL暗号化パスワードを生成します。次のように、`postgresql_password`をコマンドラインから呼び出し、暗号化されたパスワードをマニフェストにコピーペーストします。

```shell
puppet apply --execute 'notify { 'test': message => postgresql_password('username', 'password') }'
```

本番マニフェストからこの関数を呼び出すことも可能ですが、その場合、マニフェストには暗号化していない平文のパスワードを含める必要があります。

#### postgresql_acls_to_resources_hash(acl_array, id, order_offset)

この内部関数は、`pg_hba.conf`ベースのACLのリスト(文字列の配列として受け渡されたもの)を`postgresql::pg_hba_rule`リソースと互換性のある形式に変換します。

**この関数は、モジュールによる内部的な使用のみ可能です。**

## 制約事項

PostgreSQLのバージョン8.1～9.5で動作します。

現在、postgresqlモジュールは次のオペレーティングシステムでテスト済みです。

* Debian 6.x, 7.x, 8.x.
* CentOS 5.x、6.x、7.x。
* Ubuntu 10.04および12.04、14.04。

その他のシステムとも互換性がある可能性がありますが、積極的なテストは行っておりません。

### Aptモジュールのサポート

このモジュールは1.xと2.x両方のバージョンの'puppetlabs-apt'モジュールをサポートしていますが、'puppetlabs-apt'の2.0.0と2.0.1はサポートしていません。

### PostGISのサポート

PostGISは、現時点ではすべてのプラットフォームで正常に動作するわけではないため、サポート対象外の機能とみなします。

### すべてのバージョンのRHEL/CentOS

SELinuxが有効化されている場合、次の方法で`postgresql_port_t`コンテキストに使用中のカスタムポートを追加する必要があります。

```shell
semanage port -a -t postgresql_port_t -p tcp $customport
```

## 開発

Puppet Forgeに公開されているPuppet Labsモジュールはオープンプロジェクトのため、維持するにはコミュニティの貢献が不可欠です。Puppetは、現在私たちがアクセスできない無数のプラットフォームやハードウェア、ソフトウェア、デプロイ構成にも利用されることを目的としています。私たちの目標は、できる限り簡単に変更に貢献し、みなさまの環境で私たちのモジュールが機能できるようにすることです。最高の状態を維持するため、コントリビュータにはいくつかのガイドラインを守っていただく必要があります。詳細については、[モジュールコントリビューションガイド](https://docs.puppetlabs.com/forge/contributing.html)を参照してください。

### テスト

このモジュールには、2種類のテストが配布されています。`rspec-puppet`のユニットテストと、`rspec-system`を使用したシステムテストです。

ユニットテストを実行するには、以下がインストールされていることを確認してください。

* rake
* bundler

次のように、必要なgemをインストールします。

```shell
bundle install --path=vendor
```

そして、次のように記述して、ユニットテストを実行します。

```shell
bundle exec rake spec
```

ユニットテストは、Travis-CIでも実行されます。自身のテスト結果を確認するには、このプロジェクトのご自身のGitHubクローンのアカウントセクションから、Travis-CIを介してサービスフックを登録してください。

システムテストを実行するには、以下のツールもインストールされていることを確認してください。

* Vagrant > 1.2.x
* VirtualBox > 4.2.10

次の記述を使用してテストを実行します。

```shell
bundle exec rspec spec/acceptance
```

異なるオペレーティングシステムでテストを実行するには、`.nodeset.yml`で利用可能なセットを確認して、次の構文で特定のセットを実行します。

```shell
RSPEC_SET=debian-607-x64 bundle exec rspec spec/acceptance
```

### コントリビュータ

貢献してくださった方々の一覧を[Github](https://github.com/puppetlabs/puppetlabs-postgresql/graphs/contributors)でご覧いただけます。
