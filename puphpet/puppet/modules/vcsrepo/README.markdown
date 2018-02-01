# vcsrepo

#### Table of contents

1. [Overview](#overview)
2. [Module Description - What the module does and why it is useful](#module-description)
3. [Setup - The basics of getting started with vcsrepo](#setup)
    * [Setup requirements](#setup-requirements)
    * [Beginning with vcsrepo](#beginning-with-vcsrepo)
4. [Usage - Configuration options and additional functionality](#usage)
    * [Git](#git)
    * [Bazaar](#bazaar)
    * [CVS](#cvs)
    * [Mercurial](#mercurial)
    * [Perforce](#perforce)
    * [Subversion](#subversion)
5. [Reference - An under-the-hood peek at what the module is doing and how](#reference)
    * [Type: vcsrepo](#type-vcsrepo)
        * [Providers](#providers)
        * [Features](#features)
        * [Parameters](#parameters)
5. [Limitations - OS compatibility, etc.](#limitations)
6. [Development - Guide for contributing to the module](#development)

## Overview

The vcsrepo module lets you use Puppet to easily deploy content from your version control system (VCS).

## Module description

The vcsrepo module provides a single type with providers to support the following version control systems:

* [Git](#git)
* [Bazaar](#bazaar)
* [CVS](#cvs)
* [Mercurial](#mercurial)
* [Perforce](#perforce)
* [Subversion](#subversion)

**Note:** `git` is the only vcs provider officially [supported by Puppet Inc.](https://forge.puppet.com/supported)

## Setup

### Setup requirements

The vcsrepo module does not install any VCS software for you. You must install a VCS before you can use this module.

Like Puppet in general, the vcsrepo module does not automatically create parent directories for the files it manages. Set up any needed directory structures before you start.

### Beginning with vcsrepo

To create and manage a blank repository, define the type `vcsrepo` with a path to your repository and supply the `provider` parameter based on the [VCS you're using](#usage).

~~~ puppet
vcsrepo { '/path/to/repo':
  ensure   => present,
  provider => git,
}
~~~

## Usage

**Note:** `git` is the only vcsrepo provider officially [supported by Puppet Inc.](https://forge.puppet.com/supported)

### Git

#### Create a blank repository

To create a blank repository suitable for use as a central repository, define `vcsrepo` without `source` or `revision`:

~~~ puppet
vcsrepo { '/path/to/repo':
  ensure   => present,
  provider => git,
}
~~~

If you're managing a central or official repository, you might want to make it a bare repository. To do this, set `ensure` to 'bare':

~~~ puppet
vcsrepo { '/path/to/repo':
  ensure   => bare,
  provider => git,
}
~~~

#### Clone/pull a repository

~~~ puppet
vcsrepo { '/path/to/repo':
  ensure   => present,
  provider => git,
  source   => 'git://example.com/repo.git',
}
~~~

To clone your repository as bare or mirror, you can set `ensure` to 'bare' or 'mirror':

~~~ puppet
vcsrepo { '/path/to/repo':
  ensure   => mirror,
  provider => git,
  source   => 'git://example.com/repo.git',
}
~~~

By default, `vcsrepo` will use the HEAD of the source repository's master branch. To use another branch or a specific commit, set `revision` to either a branch name or a commit SHA or tag.

Branch name:

~~~ puppet
vcsrepo { '/path/to/repo':
  ensure   => present,
  provider => git,
  source   => 'git://example.com/repo.git',
  revision => 'development',
}
~~~

SHA:

~~~ puppet
vcsrepo { '/path/to/repo':
  ensure   => present,
  provider => git,
  source   => 'git://example.com/repo.git',
  revision => '0c466b8a5a45f6cd7de82c08df2fb4ce1e920a31',
}
~~~

Tag:

~~~ puppet
vcsrepo { '/path/to/repo':
  ensure   => present,
  provider => git,
  source   => 'git://example.com/repo.git',
  revision => '1.1.2rc1',
}
~~~

To check out a branch as a specific user, supply the `user` parameter:

~~~ puppet
vcsrepo { '/path/to/repo':
  ensure   => present,
  provider => git,
  source   => 'git://example.com/repo.git',
  revision => '0c466b8a5a45f6cd7de82c08df2fb4ce1e920a31',
  user     => 'someUser',
}
~~~

To keep the repository at the latest revision, set `ensure` to 'latest'.

**WARNING:** This overwrites any local changes to the repository.

~~~ puppet
vcsrepo { '/path/to/repo':
  ensure   => latest,
  provider => git,
  source   => 'git://example.com/repo.git',
  revision => 'master',
}
~~~

To clone the repository but skip initializing submodules, set `submodules` to 'false':

~~~ puppet
vcsrepo { '/path/to/repo':
  ensure     => latest,
  provider   => git,
  source     => 'git://example.com/repo.git',
  submodules => false,
}
~~~

#### Use multiple remotes with a repository

In place of a single string, you can set `source` to a hash of one or more name => URL pairs:

~~~ puppet
vcsrepo { '/path/to/repo':
  ensure   => present,
  provider => git,
  remote   => 'origin'
  source   => {
    'origin'       => 'https://github.com/puppetlabs/puppetlabs-vcsrepo.git',
    'other_remote' => 'https://github.com/other_user/puppetlabs-vcsrepo.git'
  },
}
~~~

**Note:** If you set `source` to a hash, one of the names you specify must match the value of the `remote` parameter. That remote serves as the upstream of your managed repository.

#### Connect via SSH

To connect to your source repository via SSH (such as 'username@server:…'), we recommend managing your SSH keys with Puppet and using the [`require`](http://docs.puppet.com/references/stable/metaparameter.html#require) metaparameter to make sure they are present before the `vcsrepo` resource is applied.

To use SSH keys associated with a user, specify the username in the `user` parameter:

~~~ puppet
vcsrepo { '/path/to/repo':
  ensure   => latest,
  provider => git,
  source   => 'ssh://username@example.com/repo.git',
  user     => 'toto', #uses toto's $HOME/.ssh setup
  require  => File['/home/toto/.ssh/id_rsa'],
}
~~~

To use SSH over a nonstandard port, use the full SSH scheme and include the port number:

~~~ puppet
vcsrepo { '/path/to/repo':
  ensure   => latest,
  provider => git,
  source   => 'ssh://username@example.com:7999/repo.git',
}
~~~


### Bazaar

#### Create a blank repository

To create a blank repository, suitable for use as a central repository, define `vcsrepo` without `source` or `revision`:

~~~ puppet
vcsrepo { '/path/to/repo':
  ensure   => present,
  provider => bzr,
}
~~~

#### Branch from an existing repository

~~~ puppet
vcsrepo { '/path/to/repo':
  ensure   => present,
  provider => bzr,
  source   => '/some/path',
}
~~~

To branch from a specific revision, set `revision` to a valid [Bazaar revision spec](http://wiki.bazaar.canonical.com/BzrRevisionSpec):

~~~ puppet
vcsrepo { '/path/to/repo':
  ensure   => present,
  provider => bzr,
  source   => '/some/path',
  revision => 'menesis@pov.lt-20100309191856-4wmfqzc803fj300x',
}
~~~

#### Connect via SSH

To connect to your source repository via SSH (such as `'bzr+ssh://...'` or `'sftp://...,'`), we recommend using the [`require`](http://docs.puppet.com/references/stable/metaparameter.html#require) metaparameter to make sure your SSH keys are present before the `vcsrepo` resource is applied:

~~~ puppet
vcsrepo { '/path/to/repo':
  ensure   => latest,
  provider => bzr,
  source   => 'bzr+ssh://bzr.example.com/some/path',
  user     => 'toto', #uses toto's $HOME/.ssh setup
  require  => File['/home/toto/.ssh/id_rsa'],
}
~~~

### CVS

#### Create a blank repository

To create a blank repository suitable for use as a central repository, define `vcsrepo` without `source` or `revision`:

~~~ puppet
vcsrepo { '/path/to/repo':
  ensure   => present,
  provider => cvs,
}
~~~

#### Checkout/update from a repository

~~~ puppet
vcsrepo { '/path/to/workspace':
  ensure   => present,
  provider => cvs,
  source   => ':pserver:anonymous@example.com:/sources/myproj',
}
~~~

To get a specific module on the current mainline, supply the `module` parameter:

~~~ puppet
vcsrepo { '/vagrant/lockss-daemon-source':
  ensure   => present,
  provider => cvs,
  source   => ':pserver:anonymous@lockss.cvs.sourceforge.net:/cvsroot/lockss',
  module   => 'lockss-daemon',
}
~~~

To set the GZIP compression levels for your repository history, use the `compression` parameter:

~~~ puppet
vcsrepo { '/path/to/workspace':
  ensure      => present,
  provider    => cvs,
  compression => 3,
  source      => ':pserver:anonymous@example.com:/sources/myproj',
}
~~~

To get a specific revision, set `revision` to the revision number.

~~~ puppet
vcsrepo { '/path/to/workspace':
  ensure      => present,
  provider    => cvs,
  compression => 3,
  source      => ':pserver:anonymous@example.com:/sources/myproj',
  revision    => '1.2',
}
~~~

You can also set `revision` to a tag:

~~~ puppet
vcsrepo { '/path/to/workspace':
  ensure      => present,
  provider    => cvs,
  compression => 3,
  source      => ':pserver:anonymous@example.com:/sources/myproj',
  revision    => 'SOMETAG',
}
~~~

#### Connect via SSH

To connect to your source repository via SSH, we recommend using the [`require`](http://docs.puppet.com/references/stable/metaparameter.html#require) metaparameter to make sure your SSH keys are present before the `vcsrepo` resource is applied:

~~~ puppet
vcsrepo { '/path/to/repo':
  ensure   => latest,
  provider => cvs,
  source   => ':pserver:anonymous@example.com:/sources/myproj',
  user     => 'toto', #uses toto's $HOME/.ssh setup
  require  => File['/home/toto/.ssh/id_rsa'],
}
~~~

### Mercurial

#### Create a blank repository

To create a blank repository suitable for use as a central repository, define `vcsrepo` without `source` or `revision`:

~~~ puppet
vcsrepo { '/path/to/repo':
  ensure   => present,
  provider => hg,
}
~~~

#### Clone/pull and update a repository

To get the default branch tip:

~~~ puppet
vcsrepo { '/path/to/repo':
  ensure   => present,
  provider => hg,
  source   => 'http://hg.example.com/myrepo',
}
~~~

For a specific changeset, use `revision`:

~~~ puppet
vcsrepo { '/path/to/repo':
  ensure   => present,
  provider => hg,
  source   => 'http://hg.example.com/myrepo',
  revision => '21ea4598c962',
}
~~~

You can also set `revision` to a tag:

~~~ puppet
vcsrepo { '/path/to/repo':
  ensure   => present,
  provider => hg,
  source   => 'http://hg.example.com/myrepo',
  revision => '1.1.2',
}
~~~

To check out as a specific user:

~~~ puppet
vcsrepo { '/path/to/repo':
  ensure   => present,
  provider => hg,
  source   => 'http://hg.example.com/myrepo',
  user     => 'user',
}
~~~

To specify an SSH identity key:

~~~ puppet
vcsrepo { '/path/to/repo':
  ensure   => present,
  provider => hg,
  source   => 'ssh://hg@hg.example.com/myrepo',
  identity => '/home/user/.ssh/id_dsa1',
}
~~~

To specify a username and password for HTTP Basic authentication:

~~~ puppet
vcsrepo { '/path/to/repo':
  ensure              => latest,
  provider            => hg,
  source              => 'http://hg.example.com/myrepo',
  basic_auth_username => 'hgusername',
  basic_auth_password => 'hgpassword',
}
~~~

#### Connect via SSH

To connect to your source repository via SSH (such as `'ssh://...'`), we recommend using the [`require` metaparameter](http://docs.puppet.com/references/stable/metaparameter.html#require) to make sure your SSH keys are present before the `vcsrepo` resource is applied:

~~~ puppet
vcsrepo { '/path/to/repo':
  ensure   => latest,
  provider => hg,
  source   => 'ssh://hg.example.com//path/to/myrepo',
  user     => 'toto', #uses toto's $HOME/.ssh setup
  require  => File['/home/toto/.ssh/id_rsa'],
}
~~~

### Perforce

#### Create an empty workspace

To set up the connection to your Perforce service, set `p4config` to the location of a valid Perforce [config file](http://www.perforce.com/perforce/doc.current/manuals/p4guide/chapter.configuration.html#configuration.process.configfiles) stored on the node:

~~~ puppet
vcsrepo { '/path/to/repo':
  ensure   => present,
  provider => p4,
  p4config => '/root/.p4config'
}
~~~

**Note:** If you don't include the `P4CLIENT` setting in your config file, the provider generates a workspace name based on the digest of `path` and the node's hostname (such as `puppet-91bc00640c4e5a17787286acbe2c021c`).

#### Create/update and sync a Perforce workspace

To sync a depot path to head, set `ensure` to 'latest':

~~~ puppet
vcsrepo { '/path/to/repo':
  ensure   => latest,
  provider => p4,
  source   => '//depot/branch/...'
}
~~~

To sync to a specific changelist, specify its revision number with the `revision` parameter:

~~~ puppet
vcsrepo { '/path/to/repo':
  ensure   => present,
  provider => p4,
  source   => '//depot/branch/...',
  revision => '2341'
}
~~~

You can also set `revision` to a label:

~~~ puppet
vcsrepo { '/path/to/repo':
  ensure   => present,
  provider => p4,
  source   => '//depot/branch/...',
  revision => 'my_label'
}
~~~

### Subversion

#### Create a blank repository

~~~ puppet
vcsrepo { '/path/to/repo':
  ensure   => present,
  provider => svn,
}
~~~

#### Check out from an existing repository

Provide a `source` pointing to the branch or tag you want to check out:

~~~ puppet
vcsrepo { '/path/to/repo':
  ensure   => present,
  provider => svn,
  source   => 'svn://svnrepo/hello/branches/foo',
}
~~~

You can also designate a specific revision:

~~~ puppet
vcsrepo { '/path/to/repo':
  ensure   => present,
  provider => svn,
  source   => 'svn://svnrepo/hello/branches/foo',
  revision => '1234',
}
~~~

####Checking out only specific paths

**Note:** The `includes` param is only supported when subversion client version is >= 1.6.

You can check out only specific paths in a particular repository by providing their relative paths to the `includes` parameter, like so:

~~~
vcsrepo { '/path/to/repo':
  ensure   => present,
  provider => svn,
  source   => 'http://svnrepo/hello/trunk',
  includes => [
    'root-file.txt',
    'checkout-folder',
    'file/this-file.txt',
    'folder/this-folder/',
  ]
}
~~~

This will create files `/path/to/repo/file-at-root-path.txt` and `/path/to/repo/file/nested/within/repo.jmx`, with folders `/path/to/repo/some-folder` and `/path/to/repo/nested/folder/to/checkout` completely recreating their corresponding working tree path.

When specified, the `depth` parameter will also be applied to the `includes` -- the root directory will be checked out using an `empty` depth, and the `includes` you specify will be checked out using the `depth` you provide.

To illustrate this point, using the above snippet (with the specified `includes`) and a remote repository layout like this:

~~~
.
├── checkout-folder
│   ├── file1
│   └── nested-1
│       ├── nested-2
│       │   └── nested-file-2
│       └── nested-file-1
├── file
│   ├── NOT-this-file.txt
│   └── this-file.txt
├── folder
│   ├── never-checked-out
│   └── this-folder
│       ├── deep-nested-1
│       │   ├── deep-nested-2
│       │   │   └── deep-nested-file-2
│       │   └── deep-nested-file-1
│       └── this-file.txt
├── NOT-this-file.txt
├── NOT-this-folder
│   ├── NOT-this-file.txt
│   └── NOT-this-one-either.txt
└── root-file.txt
~~~

With no `depth` given, your local folder `/path/to/repo` will look like this:

~~~
.
├── checkout-folder
│   ├── file1
│   └── nested-1
│       ├── nested-2
│       │   └── nested-file-2
│       └── nested-file-1
├── file
│   └── this-file.txt
├── folder
│   └── this-folder
│       ├── deep-nested-1
│       │   ├── deep-nested-2
│       │   │   └── deep-nested-file-2
│       │   └── deep-nested-file-1
│       └── this-file.txt
└── root-file.txt
~~~

And with a `depth` of `files` will look like this:

~~~
.
├── checkout-folder
│   └── file1
├── file
│   └── this-file.txt
├── folder
│   └── this-folder
│       └── this-file.txt
└── root-file.txt
~~~


####Use a specific Subversion configuration directory 

Use the `configuration` parameter to designate the directory that contains your Subversion configuration files (typically, '/path/to/.subversion'):

~~~ puppet
vcsrepo { '/path/to/repo':
  ensure        => present,
  provider      => svn,
  source        => 'svn://svnrepo/hello/branches/foo',
  configuration => '/path/to/.subversion',
}
~~~

#### Connect via SSH

To connect to your source repository via SSH (such as `'svn+ssh://...'`), we recommend using the [`require` metaparameter](http://docs.puppet.com/references/stable/metaparameter.html#require) to make sure your SSH keys are present before the `vcsrepo` resource is applied:

~~~ puppet
vcsrepo { '/path/to/repo':
  ensure   => latest,
  provider => svn,
  source   => 'svn+ssh://svnrepo/hello/branches/foo',
  user     => 'toto', #uses toto's $HOME/.ssh setup
  require  => File['/home/toto/.ssh/id_rsa'],
}
~~~

## Reference

### Type: vcsrepo

The vcsrepo module adds only one type with several providers. Each provider abstracts a different VCS, and each provider includes a set of features according to its needs.

#### Providers

**Note:** Not all features are available with all providers.

##### `git` - Supports the Git VCS.

Features: `bare_repositories`, `depth`, `multiple_remotes`, `reference_tracking`, `ssh_identity`, `submodules`, `user`

Parameters: `depth`, `ensure`, `excludes`, `force`, `group`, `identity`, `owner`, `path`, `provider`, `remote`, `revision`, `source`, `user`

##### `bzr` - Supports the Bazaar VCS.

Features: `reference_tracking`

Parameters: `ensure`, `excludes`, `force`, `group`, `owner`, `path`, `provider`, `revision`, `source`

##### `cvs` - Supports the CVS VCS.

Features: `cvs_rsh`, `gzip_compression`, `modules`, `reference_tracking`, `user`

Parameters: `compression`, `cvs_rsh`, `ensure`, `excludes`, `force`, `group`, `module`, `owner`, `path`, `provider`

##### `hg` - Supports the Mercurial VCS.

Features: `reference_tracking`, `ssh_identity`, `user`

Parameters: `ensure`, `excludes`, `force`, `group`, `identity`, `owner`, `path`, `provider`, `revision`, `source`, `user`

##### `p4` - Supports the Perforce VCS.

Features: `p4config`, `reference_tracking`

Parameters: `ensure`, `excludes`, `force`, `group`, `owner`, `p4config`, `path`, `provider`, `revision`, `source`

##### `svn` - Supports the Subversion VCS.

Features: `basic_auth`, `configuration`, `conflict`, `depth`, `filesystem_types`, `reference_tracking`

Parameters: `basic_auth_password`, `basic_auth_username`, `configuration`, `conflict`, `ensure`, `excludes`, `force`, `fstype`, `group`, `includes`, `owner`, `path`, `provider`, `revision`, `source`, `trust_server_cert`

#### Features

**Note:** Not all features are available with all providers.

* `bare_repositories` - Differentiates between bare repositories and those with working copies. (Available with `git`.)
* `basic_auth` - Supports HTTP Basic authentication. (Available with `svn`.)
* `conflict` - Lets you decide how to resolve any conflicts between the source repository and your working copy. (Available with `svn`.)
* `configuration` - Lets you specify the location of your configuration files. (Available with `svn`.)
* `cvs_rsh` - Understands the `CVS_RSH` environment variable. (Available with `cvs`.)
* `depth` - Supports shallow clones in `git` or sets the scope limit in `svn`. (Available with `git` and `svn`.)
* `filesystem_types` - Supports multiple types of filesystem. (Available with `svn`.)
* `gzip_compression` - Supports explicit GZip compression levels. (Available with `cvs`.)
* `include_paths` - Lets you checkout only certain paths. (Available with `svn`.)
* `modules` - Lets you choose a specific repository module. (Available with `cvs`.)
* `multiple_remotes` - Tracks multiple remote repositories. (Available with `git`.)
* `reference_tracking` - Lets you track revision references that can change over time (e.g., some VCS tags and branch names). (Available with all providers)
* `ssh_identity` - Lets you specify an SSH identity file. (Available with `git` and `hg`.)
* `user` - Can run as a different user. (Available with `git`, `hg` and `cvs`.)
* `p4config` - Supports setting the `P4CONFIG` environment. (Available with `p4`.)
* `submodules` - Supports repository submodules which can be optionally initialized. (Available with `git`.)

#### Parameters

All parameters are optional, except where specified otherwise.

##### `basic_auth_password`

Specifies the password for HTTP Basic authentication. (Requires the `basic_auth` feature.) Valid options: a string. Default: none.

##### `basic_auth_username`

Specifies the username for HTTP Basic authentication. (Requires the `basic_auth` feature.) Valid options: a string. Default: none.

##### `compression`

Sets the GZIP compression level for the repository history. (Requires the `gzip_compression` feature.) Valid options: an integer between 0 and 6. Default: none.

##### `configuration`

Sets the configuration directory to use. (Requires the `configuration` feature.) Valid options: a string containing an absolute path. Default: none.

##### `conflict`

Tells Subversion how to resolve any conflicts between the source repository and your working copy. (Requires the `conflict` feature.) Valid options: 'base', 'mine-full', 'theirs-full', and 'working'. Default: none.

##### `cvs_rsh`

Provides a value for the `CVS_RSH` environment variable. (Requires the `cvs_rsh` feature.) Valid options: a string. Default: none.

##### `depth`

In `git`, `depth` sets the number of commits to include when creating a shallow clone. (Requires the `depth` feature.) Valid options: an integer. Default: none.

In `svn`, `depth` limits the scope of an operation to the specified tree depth. (Requires the `depth` feature.) Valid options: 'empty', 'files', 'immediates', 'infinity'. Default: none.

##### `ensure`

Specifies whether the repository should exist. Valid options: 'present', 'bare', 'absent', and 'latest'. Default: 'present'.

##### `excludes`

Lists any files the repository shouldn't track (similar to .gitignore). Valid options: a string (separate multiple values with the newline character). Default: none.

##### `force`

Specifies whether to delete any existing files in the repository path if creating a new repository. **Use with care.** Valid options: 'true' and 'false'. Default: 'false'.

##### `fstype`

Sets the filesystem type. (Requires the `filesystem_types` feature.) Valid options: 'fsfs' or 'bdb'. Default: none.

##### `group`

Specifies a group to own the repository files. Valid options: a string containing a group name or GID. Default: none.

##### `identity`

Specifies an identity file to use for SSH authentication. (Requires the `ssh_identity` feature.) Valid options: a string containing an absolute path. Default: none.

##### `includes`

Tells Subversion which paths should be checked out at the specified depth; all other paths are not checked out. Default: none (checkout all paths).

##### `module`

Specifies the repository module to manage. (Requires the `modules` feature.) Valid options: a string containing the name of a CVS module. Default: none.

##### `owner`

Specifies a user to own the repository files. Valid options: a string containing a username or UID. Default: none.

##### `p4config`

Specifies a config file that contains settings for connecting to the Perforce service. (Requires the `p4config` feature.) Valid options: a string containing the absolute path to a valid [Perforce config file](http://www.perforce.com/perforce/doc.current/manuals/p4guide/chapter.configuration.html#configuration.process.configfiles). Default: none.

##### `path`

Specifies a location for the managed repository. Valid options: a string containing an absolute path. Default: the title of your declared resource.

##### `provider`

*Required.* Specifies the backend to use for this vcsrepo resource. Valid options: 'bzr', 'cvs', 'git', 'hg', 'p4', and 'svn'.

##### `remote`

Specifies the remote repository to track. (Requires the `multiple_remotes` feature.) Valid options: a string containing one of the remote names specified in `source`. Default: 'origin'.

##### `revision`

Sets the revision of the repository. Valid options vary by provider:

* `git` - A string containing a Git branch name, or a commit SHA or tag.
* `bzr` - A string containing a Bazaar [revision spec](http://wiki.bazaar.canonical.com/BzrRevisionSpec).
* `cvs` - A string containing a CVS [tag or revision number](http://www.thathost.com/wincvs-howto/cvsdoc/cvs_4.html).
* `hg` - A string containing a Mercurial [changeset ID](https://www.mercurial-scm.org/wiki/ChangeSetID) or [tag](https://www.mercurial-scm.org/wiki/Tag).
* `p4` - A string containing a Perforce [change number, label name, client name, or date spec](http://www.perforce.com/perforce/r12.1/manuals/cmdref/o.fspecs.html).
* `svn` - A string containing a Subversion [revision number](http://svnbook.red-bean.com/en/1.7/svn.basic.in-action.html#svn.basic.in-action.revs), [revision keyword, or revision date](http://svnbook.red-bean.com/en/1.7/svn.tour.revs.specifiers.html).

Default: none.

##### `source`

Specifies a source repository to serve as the upstream for your managed repository. Default: none. Valid options vary by provider:

* `git` - A string containing a [Git repository URL](https://www.kernel.org/pub/software/scm/git/docs/git-clone.html#_git_urls_a_id_urls_a) or a hash of `name => URL` mappings. See also [`remote`](#remote).
* `bzr` - A string containing a Bazaar branch location.
* `cvs` - A string containing a CVS root.
* `hg` - A string containing the local path or URL of a Mercurial repository.
* `p4` - A string containing a Perforce depot path.
* `svn` - A string containing a Subversion repository URL.

Default: none.

##### `submodules`

Specifies whether to initialize and update each submodule in the repository. (Requires the `submodules` feature.) Valid options: 'true' and 'false'. Default: 'true'.

##### `trust_server_cert`

Instructs Subversion to accept SSL server certificates issued by unknown certificate authorities. Valid options: 'true' and 'false'. Default: 'false'.

##### `user`

Specifies the user to run as for repository operations. (Requires the `user` feature.) Valid options: a string containing a username or UID. Default: none.

## Limitations

Git is the only VCS provider officially [supported by Puppet Inc.](https://forge.puppet.com/supported)

The includes parameter is only supported when SVN client version is >= 1.6.

This module has been tested with Puppet 2.7 and higher.

The module has been tested on:

* CentOS 5/6/7
* Debian 6/7/8
* Oracle 5/6/7
* Red Hat Enterprise Linux 5/6/7
* Scientific Linux 5/6/7
* SLES 10/11/12
* Ubuntu 10.04/12.04/14.04/16.04

Testing on other platforms has been light and cannot be guaranteed.

## Development

Puppet Inc. modules on the Puppet Forge are open projects, and community contributions are essential for keeping them great. We can't access the huge number of platforms and myriad of hardware, software, and deployment configurations that Puppet is intended to serve.

We want to keep it as easy as possible to contribute changes so that our modules work in your environment. There are a few guidelines that we need contributors to follow so that we can have a chance of keeping on top of things.

You can read the complete module contribution guide [on the Puppet documentation site.](https://docs.puppet.com/guides/module_guides/bgtm.html)
