# Changelog

All notable changes to this project will be documented in this file.
Each new release typically also includes the latest modulesync defaults.
These should not affect the functionality of the module.

## [v8.1.0](https://github.com/voxpupuli/puppet-rabbitmq/tree/v8.1.0) (2018-01-11)

[Full Changelog](https://github.com/voxpupuli/puppet-rabbitmq/compare/v8.0.0...v8.1.0)

**Implemented enhancements:**

- Add options for ssl verify and 'fail\_if\_no\_peer\_cert' for the managem… [\#657](https://github.com/voxpupuli/puppet-rabbitmq/pull/657) ([paebersold](https://github.com/paebersold))
- add ability to have array as package name [\#656](https://github.com/voxpupuli/puppet-rabbitmq/pull/656) ([tampakrap](https://github.com/tampakrap))

**Fixed bugs:**

- Support policy format change in v3.7.0 - \#671 \(Replaces \#674\) [\#676](https://github.com/voxpupuli/puppet-rabbitmq/pull/676) ([fatmcgav](https://github.com/fatmcgav))
- Remove `archive require` in rabbitmqadmin class [\#669](https://github.com/voxpupuli/puppet-rabbitmq/pull/669) ([lzecca78](https://github.com/lzecca78))

**Closed issues:**

- not working with rabbitmq-server 3.7 - Cannot parse invalid user line [\#671](https://github.com/voxpupuli/puppet-rabbitmq/issues/671)
- Rabbitmq crashing with config\_ranch = true [\#668](https://github.com/voxpupuli/puppet-rabbitmq/issues/668)
- puppet/rabbitmq 8.0.0 - /etc/apt/sources.list.d/rabbitmq.list not updated/created [\#662](https://github.com/voxpupuli/puppet-rabbitmq/issues/662)
- rabbitmqadmin install has no way of continuing to use staging in environments using an incompatible archive module [\#659](https://github.com/voxpupuli/puppet-rabbitmq/issues/659)
- RabbitMQ Admin Package should work with Puppet-Archive module, or explicitly depend on camptocamp's archive module [\#658](https://github.com/voxpupuli/puppet-rabbitmq/issues/658)

**Merged pull requests:**

- Do not use defaultfor to choose the only existing provider [\#672](https://github.com/voxpupuli/puppet-rabbitmq/pull/672) ([mbaldessari](https://github.com/mbaldessari))
- regenerate puppet-strings docs [\#667](https://github.com/voxpupuli/puppet-rabbitmq/pull/667) ([bastelfreak](https://github.com/bastelfreak))
- Remove EOL operatingsystems [\#666](https://github.com/voxpupuli/puppet-rabbitmq/pull/666) ([ekohl](https://github.com/ekohl))
- Update README to specify voxpupuli/archive dependency vs. staging [\#660](https://github.com/voxpupuli/puppet-rabbitmq/pull/660) ([wyardley](https://github.com/wyardley))
- release 8.0.0 [\#655](https://github.com/voxpupuli/puppet-rabbitmq/pull/655) ([bastelfreak](https://github.com/bastelfreak))

## [v8.0.0](https://github.com/voxpupuli/puppet-rabbitmq/tree/v8.0.0) (2017-10-18)

[Full Changelog](https://github.com/voxpupuli/puppet-rabbitmq/compare/v7.1.0...v8.0.0)

**Breaking changes:**

- BREAKING: Remove deprecated manage\_repos parameter and disallow strings for integer parameters [\#649](https://github.com/voxpupuli/puppet-rabbitmq/pull/649) ([wyardley](https://github.com/wyardley))

**Implemented enhancements:**

- allow installation of rabbitmqadmin via package [\#654](https://github.com/voxpupuli/puppet-rabbitmq/pull/654) ([bastelfreak](https://github.com/bastelfreak))

**Fixed bugs:**

- Error on using puppet resource rabbitmq\_binding [\#650](https://github.com/voxpupuli/puppet-rabbitmq/issues/650)
- use correct datatype for $package\_gpg\_key [\#653](https://github.com/voxpupuli/puppet-rabbitmq/pull/653) ([bastelfreak](https://github.com/bastelfreak))
- Fix 'puppet resource rabbitmq\_binding' and add tests \(\#650\) [\#651](https://github.com/voxpupuli/puppet-rabbitmq/pull/651) ([wyardley](https://github.com/wyardley))

**Closed issues:**

- admin\_enable throws Server Error: no parameter named 'allow\_insecure' [\#646](https://github.com/voxpupuli/puppet-rabbitmq/issues/646)
- Add variable to rabbitmq.config [\#644](https://github.com/voxpupuli/puppet-rabbitmq/issues/644)

**Merged pull requests:**

- use correct datatype for port param in README.md [\#652](https://github.com/voxpupuli/puppet-rabbitmq/pull/652) ([bastelfreak](https://github.com/bastelfreak))
- Make ldap\_user\_dn\_pattern optional [\#645](https://github.com/voxpupuli/puppet-rabbitmq/pull/645) ([sfhardman](https://github.com/sfhardman))
- Add tags to metadata [\#643](https://github.com/voxpupuli/puppet-rabbitmq/pull/643) ([wyardley](https://github.com/wyardley))
- 7.1.0 release [\#642](https://github.com/voxpupuli/puppet-rabbitmq/pull/642) ([wyardley](https://github.com/wyardley))

## [v7.1.0](https://github.com/voxpupuli/puppet-rabbitmq/tree/v7.1.0) (2017-10-03)

[Full Changelog](https://github.com/voxpupuli/puppet-rabbitmq/compare/v7.0.0...v7.1.0)

**Implemented enhancements:**

- Rework apt to use packagecloud repos as well \(\#640\) [\#641](https://github.com/voxpupuli/puppet-rabbitmq/pull/641) ([wyardley](https://github.com/wyardley))
- Refactor rabbitmq\_user provider \(mpolenchuk\) [\#598](https://github.com/voxpupuli/puppet-rabbitmq/pull/598) ([wyardley](https://github.com/wyardley))

**Closed issues:**

- Please switch to Package Cloud apt repository; rabbitmq.com's one becomes read-only in a few months [\#640](https://github.com/voxpupuli/puppet-rabbitmq/issues/640)

**Merged pull requests:**

- Switch back to "include foo" \(without leading colons\) syntax [\#639](https://github.com/voxpupuli/puppet-rabbitmq/pull/639) ([wyardley](https://github.com/wyardley))
- Lower required Puppet version from 4.8.0 to 4.7.1 [\#637](https://github.com/voxpupuli/puppet-rabbitmq/pull/637) ([wyardley](https://github.com/wyardley))
- 7.0.0 release [\#633](https://github.com/voxpupuli/puppet-rabbitmq/pull/633) ([wyardley](https://github.com/wyardley))

## [v7.0.0](https://github.com/voxpupuli/puppet-rabbitmq/tree/v7.0.0) (2017-09-14)

[Full Changelog](https://github.com/voxpupuli/puppet-rabbitmq/compare/v6.0.0...v7.0.0)

**Breaking changes:**

- BREAKING: Ensure python package \(adds manage\_python option\) [\#623](https://github.com/voxpupuli/puppet-rabbitmq/pull/623) ([wyardley](https://github.com/wyardley))
- BREAKING: Adds config\_ranch parameter \(default: true\) to suppress config lines \(\#618\) [\#621](https://github.com/voxpupuli/puppet-rabbitmq/pull/621) ([wyardley](https://github.com/wyardley))

**Implemented enhancements:**

- Add additional SSL configuration options \(original PR from xepa\) [\#632](https://github.com/voxpupuli/puppet-rabbitmq/pull/632) ([wyardley](https://github.com/wyardley))
- Add support for max-length-bytes as an integer \(\#557\), patch by zhianliu [\#628](https://github.com/voxpupuli/puppet-rabbitmq/pull/628) ([wyardley](https://github.com/wyardley))
- Add official support for Ubuntu 16.04 [\#624](https://github.com/voxpupuli/puppet-rabbitmq/pull/624) ([wyardley](https://github.com/wyardley))
- Move examples and params to puppet strings style docs [\#562](https://github.com/voxpupuli/puppet-rabbitmq/pull/562) ([wyardley](https://github.com/wyardley))

**Fixed bugs:**

- Extra newline in SSL section of rabbitmq.config [\#634](https://github.com/voxpupuli/puppet-rabbitmq/issues/634)
- Idempotency problems with rabbitmq 3.6.5 and puppet 4.8.2 [\#618](https://github.com/voxpupuli/puppet-rabbitmq/issues/618)
- Enabling new plugin fails [\#566](https://github.com/voxpupuli/puppet-rabbitmq/issues/566)
- Rabbitmq\_user\_permissions parser fails for empty string [\#172](https://github.com/voxpupuli/puppet-rabbitmq/issues/172)
- Error on using puppet resource rabbitmq\_user [\#147](https://github.com/voxpupuli/puppet-rabbitmq/issues/147)
- Resolve issue with "puppet resource rabbitmq\_user" failing \(\#147\) [\#629](https://github.com/voxpupuli/puppet-rabbitmq/pull/629) ([wyardley](https://github.com/wyardley))
- Switch back to using rabbitmq-plugins from system path \(\#566\) [\#570](https://github.com/voxpupuli/puppet-rabbitmq/pull/570) ([wyardley](https://github.com/wyardley))

**Closed issues:**

- Add support for max-length-bytes as a integer [\#557](https://github.com/voxpupuli/puppet-rabbitmq/issues/557)
- 'provider rabbitmqplugins not functional on this host' [\#150](https://github.com/voxpupuli/puppet-rabbitmq/issues/150)
- Doesn't require curl, fails when it isn't installed [\#145](https://github.com/voxpupuli/puppet-rabbitmq/issues/145)
- EPEL RPM does not install /usr/sbin/rabbitmq-plugins [\#134](https://github.com/voxpupuli/puppet-rabbitmq/issues/134)
- Parameter config\_mirrored\_queues has no effect [\#125](https://github.com/voxpupuli/puppet-rabbitmq/issues/125)

**Merged pull requests:**

- Switch string to symbol for erl\_ssl\_path fact definition [\#631](https://github.com/voxpupuli/puppet-rabbitmq/pull/631) ([wyardley](https://github.com/wyardley))
- Fix test cases for \#623 \(manage\_python\) [\#626](https://github.com/voxpupuli/puppet-rabbitmq/pull/626) ([wyardley](https://github.com/wyardley))
- Add back a few examples removed in \#562 [\#625](https://github.com/voxpupuli/puppet-rabbitmq/pull/625) ([wyardley](https://github.com/wyardley))
- Update 'require' statements and mock types to fix spec tests [\#620](https://github.com/voxpupuli/puppet-rabbitmq/pull/620) ([wyardley](https://github.com/wyardley))
- Update fixtures to voxpupuli/archive \(from puppet-community\) [\#619](https://github.com/voxpupuli/puppet-rabbitmq/pull/619) ([wyardley](https://github.com/wyardley))

## [v6.0.0](https://github.com/voxpupuli/puppet-rabbitmq/tree/v6.0.0) (2017-09-07)

[Full Changelog](https://github.com/voxpupuli/puppet-rabbitmq/compare/5.6.0...v6.0.0)

**Breaking changes:**

- BREAKING: Drop Ruby 1.8 support. Rubocop auto-fixes in preparation for modulesync [\#575](https://github.com/voxpupuli/puppet-rabbitmq/pull/575) ([wyardley](https://github.com/wyardley))
- BREAKING: Switch from 'UNSET' to undef, rename management\_ip to management\_ip\_address [\#559](https://github.com/voxpupuli/puppet-rabbitmq/pull/559) ([wyardley](https://github.com/wyardley))
- BREAKING: Drop Puppet 3 support. Replace validate\_\* with datatypes [\#536](https://github.com/voxpupuli/puppet-rabbitmq/pull/536) ([bastelfreak](https://github.com/bastelfreak))
- Updated app.pp to address deprecation warnings [\#497](https://github.com/voxpupuli/puppet-rabbitmq/pull/497) ([ilium007](https://github.com/ilium007))
- BREAKING: manage\_repos is now repos\_ensure \(default false\), version is now unused, switch to RabbitMQ's "packagecloud" repos [\#493](https://github.com/voxpupuli/puppet-rabbitmq/pull/493) ([wyardley](https://github.com/wyardley))

**Implemented enhancements:**

- Convert to use 'archive' instead of 'staging' for rabbitmqadmin install [\#604](https://github.com/voxpupuli/puppet-rabbitmq/pull/604) ([wyardley](https://github.com/wyardley))
- Update dependencies, name, and required Puppet version [\#589](https://github.com/voxpupuli/puppet-rabbitmq/pull/589) ([wyardley](https://github.com/wyardley))
- Add official archlinux support [\#583](https://github.com/voxpupuli/puppet-rabbitmq/pull/583) ([bastelfreak](https://github.com/bastelfreak))
- Enable configuring SSL for Erlang distribution [\#574](https://github.com/voxpupuli/puppet-rabbitmq/pull/574) ([JAORMX](https://github.com/JAORMX))
- Add FreeBSD support [\#567](https://github.com/voxpupuli/puppet-rabbitmq/pull/567) ([wyardley](https://github.com/wyardley))
- Add options for IPv6 and inetrc [\#552](https://github.com/voxpupuli/puppet-rabbitmq/pull/552) ([jeckersb](https://github.com/jeckersb))
- Extra ssl options to harden rabbitmq listener [\#547](https://github.com/voxpupuli/puppet-rabbitmq/pull/547) ([xepa](https://github.com/xepa))
- \[MODULES-4555\] allow list values to ha-params when ha-mode=nodes [\#537](https://github.com/voxpupuli/puppet-rabbitmq/pull/537) ([eserte](https://github.com/eserte))
- Add ssl\_depth and password params for configuration [\#530](https://github.com/voxpupuli/puppet-rabbitmq/pull/530) ([bostrowski13](https://github.com/bostrowski13))
- Allow Configuring Management IP Binding [\#506](https://github.com/voxpupuli/puppet-rabbitmq/pull/506) ([naftulikay](https://github.com/naftulikay))
- Support multiple routing keys for bindings using separate parameters [\#504](https://github.com/voxpupuli/puppet-rabbitmq/pull/504) ([wyardley](https://github.com/wyardley))

**Fixed bugs:**

- Look into "error while resolving custom fact" error [\#614](https://github.com/voxpupuli/puppet-rabbitmq/issues/614)
- repos\_ensure and version on RHEL 7 [\#573](https://github.com/voxpupuli/puppet-rabbitmq/issues/573)
- Avoid error when rabbitmqctl is not present\), update spec syntax \(\#614\) [\#615](https://github.com/voxpupuli/puppet-rabbitmq/pull/615) ([wyardley](https://github.com/wyardley))
- fix a couple of problems with erl\_ssl\_path fact [\#609](https://github.com/voxpupuli/puppet-rabbitmq/pull/609) ([costela](https://github.com/costela))
- Switch $releasevar to ${::os\[release\]\[major\]} [\#577](https://github.com/voxpupuli/puppet-rabbitmq/pull/577) ([wyardley](https://github.com/wyardley))
- Fix regex double escaping of rabbitmqctl list\_policies [\#561](https://github.com/voxpupuli/puppet-rabbitmq/pull/561) ([wyardley](https://github.com/wyardley))
- Update file / directory permissions [\#560](https://github.com/voxpupuli/puppet-rabbitmq/pull/560) ([wyardley](https://github.com/wyardley))
- Update regexp for rabbitmq\_nodename fact [\#545](https://github.com/voxpupuli/puppet-rabbitmq/pull/545) ([SergK](https://github.com/SergK))
- \[Bugfix\] convert $ssl\_depth from string to integer [\#539](https://github.com/voxpupuli/puppet-rabbitmq/pull/539) ([bastelfreak](https://github.com/bastelfreak))
- Extend rabbitmqadmin config template with SSL options. [\#526](https://github.com/voxpupuli/puppet-rabbitmq/pull/526) ([justahero](https://github.com/justahero))
- \[MODULES-4223\] don't set NODE\_PORT and NODE\_IP\_ADDRESS if ssl\_only [\#524](https://github.com/voxpupuli/puppet-rabbitmq/pull/524) ([JAORMX](https://github.com/JAORMX))
- \[MODULES-3733\] rabbitmq provider env\_path does not locate ruby gem installed puppet binary [\#517](https://github.com/voxpupuli/puppet-rabbitmq/pull/517) ([nzverev](https://github.com/nzverev))

**Closed issues:**

- use of new 6.0.0 module on new installation fails [\#611](https://github.com/voxpupuli/puppet-rabbitmq/issues/611)
- Problem with erl\_ssl\_path fact on RHEL 7.x, erlang 18.3 [\#610](https://github.com/voxpupuli/puppet-rabbitmq/issues/610)
- Update to support puppet/staging 2 [\#587](https://github.com/voxpupuli/puppet-rabbitmq/issues/587)
- Update to support puppetlabs/apt 3 or 4 [\#586](https://github.com/voxpupuli/puppet-rabbitmq/issues/586)
- puppet resource rabbitmq\_exchange does not work [\#174](https://github.com/voxpupuli/puppet-rabbitmq/issues/174)
- exchanges will be recreated in every puppet run if the vhost is not / [\#173](https://github.com/voxpupuli/puppet-rabbitmq/issues/173)
- Illegal cluster node name [\#163](https://github.com/voxpupuli/puppet-rabbitmq/issues/163)
- version parameter does not work [\#154](https://github.com/voxpupuli/puppet-rabbitmq/issues/154)
- Error message when creating an exchange with rabbitmq\_exchange [\#137](https://github.com/voxpupuli/puppet-rabbitmq/issues/137)
- rabbitmqadmin fails with puppet 2.7.21 with master  \(code from master branch\) [\#121](https://github.com/voxpupuli/puppet-rabbitmq/issues/121)
- Add support for exchanges [\#51](https://github.com/voxpupuli/puppet-rabbitmq/issues/51)
- invalid parameter provider [\#49](https://github.com/voxpupuli/puppet-rabbitmq/issues/49)

**Merged pull requests:**

- Replace 'anchor's with 'contain' in server.pp [\#616](https://github.com/voxpupuli/puppet-rabbitmq/pull/616) ([alexjfisher](https://github.com/alexjfisher))
- Switch to 'contain' vs. anchor pattern, and use Class\['foo'\] vs Class\['::foo'\] [\#613](https://github.com/voxpupuli/puppet-rabbitmq/pull/613) ([wyardley](https://github.com/wyardley))
- Updates for 6.0.0 release [\#612](https://github.com/voxpupuli/puppet-rabbitmq/pull/612) ([wyardley](https://github.com/wyardley))
- Fix typo \(.git =\> .github\) in README link [\#608](https://github.com/voxpupuli/puppet-rabbitmq/pull/608) ([wyardley](https://github.com/wyardley))
- Update README, and remove old CONTRIBUTING.md [\#607](https://github.com/voxpupuli/puppet-rabbitmq/pull/607) ([wyardley](https://github.com/wyardley))
- Ignore remaining multiple expectations warnings [\#602](https://github.com/voxpupuli/puppet-rabbitmq/pull/602) ([wyardley](https://github.com/wyardley))
- Eliminate more "multiple expectations" warnings and remove some redundant specs [\#601](https://github.com/voxpupuli/puppet-rabbitmq/pull/601) ([wyardley](https://github.com/wyardley))
- Migrate changelog [\#599](https://github.com/voxpupuli/puppet-rabbitmq/pull/599) ([alexjfisher](https://github.com/alexjfisher))
- Move old CHANGELOG.md to HISTORY.md [\#597](https://github.com/voxpupuli/puppet-rabbitmq/pull/597) ([wyardley](https://github.com/wyardley))
- update more lint warnings for relative classname inclusion in examples [\#595](https://github.com/voxpupuli/puppet-rabbitmq/pull/595) ([wyardley](https://github.com/wyardley))
- Remove redundant specs, rework others to avoid multiple expectation warnings [\#594](https://github.com/voxpupuli/puppet-rabbitmq/pull/594) ([wyardley](https://github.com/wyardley))
- move these tests to a context block, and use 'let\(:foo\)' syntax [\#593](https://github.com/voxpupuli/puppet-rabbitmq/pull/593) ([wyardley](https://github.com/wyardley))
- Fix stdlib requirement [\#592](https://github.com/voxpupuli/puppet-rabbitmq/pull/592) ([alexjfisher](https://github.com/alexjfisher))
- More Rubocop fixes and README.md badges [\#590](https://github.com/voxpupuli/puppet-rabbitmq/pull/590) ([alexjfisher](https://github.com/alexjfisher))
- Typo in .fixtures.yml breaking unit tests [\#588](https://github.com/voxpupuli/puppet-rabbitmq/pull/588) ([TraGicCode](https://github.com/TraGicCode))
- Rewrite the spec testing case [\#585](https://github.com/voxpupuli/puppet-rabbitmq/pull/585) ([ekohl](https://github.com/ekohl))
- Fix namevar parameter documentation in types [\#584](https://github.com/voxpupuli/puppet-rabbitmq/pull/584) ([alexjfisher](https://github.com/alexjfisher))
- Rubocop fixes: Update hash syntax and some formatting [\#582](https://github.com/voxpupuli/puppet-rabbitmq/pull/582) ([wyardley](https://github.com/wyardley))
- Switch to rspec-puppet-facts [\#581](https://github.com/voxpupuli/puppet-rabbitmq/pull/581) ([ekohl](https://github.com/ekohl))
- update various small warnings [\#580](https://github.com/voxpupuli/puppet-rabbitmq/pull/580) ([wyardley](https://github.com/wyardley))
- switch to structured facts for os\* and rabbitmq\_version [\#579](https://github.com/voxpupuli/puppet-rabbitmq/pull/579) ([wyardley](https://github.com/wyardley))
- move facter unit tests to the proper place [\#578](https://github.com/voxpupuli/puppet-rabbitmq/pull/578) ([wyardley](https://github.com/wyardley))
- Switch to 'let\(:foo\)' syntax \(resolves rubocop warnings\) [\#576](https://github.com/voxpupuli/puppet-rabbitmq/pull/576) ([wyardley](https://github.com/wyardley))
- removed package\_provider var from readme and added deprecation check … [\#571](https://github.com/voxpupuli/puppet-rabbitmq/pull/571) ([bostrowski13](https://github.com/bostrowski13))
- don't "touch" hiera.yaml in spec\_helper\_acceptance [\#558](https://github.com/voxpupuli/puppet-rabbitmq/pull/558) ([wyardley](https://github.com/wyardley))
- \(MODULES-5187\) mysnc puppet 5 and ruby 2.4 [\#554](https://github.com/voxpupuli/puppet-rabbitmq/pull/554) ([eputnam](https://github.com/eputnam))
- \(MODULES-5144\) Prep for puppet 5 [\#553](https://github.com/voxpupuli/puppet-rabbitmq/pull/553) ([hunner](https://github.com/hunner))
- Fix unit tests on \#535 [\#550](https://github.com/voxpupuli/puppet-rabbitmq/pull/550) ([hunner](https://github.com/hunner))
- Fix error text in `rabbitmq\_vhost` provider [\#549](https://github.com/voxpupuli/puppet-rabbitmq/pull/549) ([hybby](https://github.com/hybby))
- Improve distro fact handling in tests [\#548](https://github.com/voxpupuli/puppet-rabbitmq/pull/548) ([jeckersb](https://github.com/jeckersb))
- Simplify "all\_vhosts" in rabbitmq\_queue provider [\#544](https://github.com/voxpupuli/puppet-rabbitmq/pull/544) ([KarolisL](https://github.com/KarolisL))
- Upstream staging module released 2.2.0. Allow using it. [\#543](https://github.com/voxpupuli/puppet-rabbitmq/pull/543) ([vStone](https://github.com/vStone))
- Scope config\_variables for Puppet 4 [\#541](https://github.com/voxpupuli/puppet-rabbitmq/pull/541) ([jarro2783](https://github.com/jarro2783))
- \[msync\] 786266 Implement puppet-module-gems, a45803 Remove metadata.json from locales config [\#540](https://github.com/voxpupuli/puppet-rabbitmq/pull/540) ([wilson208](https://github.com/wilson208))
- \[MODULES-4528\] Replace Puppet.version.to\_f version comparison from spec\_helper.rb [\#538](https://github.com/voxpupuli/puppet-rabbitmq/pull/538) ([wilson208](https://github.com/wilson208))
- Systemd open files limit [\#535](https://github.com/voxpupuli/puppet-rabbitmq/pull/535) ([arteal](https://github.com/arteal))
- \[MODULES-4450\] don't set ssl depth if undef [\#534](https://github.com/voxpupuli/puppet-rabbitmq/pull/534) ([JAORMX](https://github.com/JAORMX))
- moved username:password to separate parameter [\#532](https://github.com/voxpupuli/puppet-rabbitmq/pull/532) ([vdmkenny](https://github.com/vdmkenny))
- \(maint\) parallel\_spec maintenance: spec\_helper [\#531](https://github.com/voxpupuli/puppet-rabbitmq/pull/531) ([eputnam](https://github.com/eputnam))
- \(MODULES-4098\) Sync the rest of the files [\#528](https://github.com/voxpupuli/puppet-rabbitmq/pull/528) ([hunner](https://github.com/hunner))
- \(MODULES-4097\) Sync travis.yml [\#527](https://github.com/voxpupuli/puppet-rabbitmq/pull/527) ([hunner](https://github.com/hunner))
- \(FM-5972\) gettext and spec.opts [\#525](https://github.com/voxpupuli/puppet-rabbitmq/pull/525) ([eputnam](https://github.com/eputnam))
- \(FM-5939\) removes spec.opts [\#523](https://github.com/voxpupuli/puppet-rabbitmq/pull/523) ([eputnam](https://github.com/eputnam))
- \(MODULES-3631\) msync Gemfile for 1.9 frozen strings [\#522](https://github.com/voxpupuli/puppet-rabbitmq/pull/522) ([hunner](https://github.com/hunner))
- Designate former tests files as examples [\#521](https://github.com/voxpupuli/puppet-rabbitmq/pull/521) ([DavidS](https://github.com/DavidS))
- Fixed lint on README.md snippets [\#520](https://github.com/voxpupuli/puppet-rabbitmq/pull/520) ([mvisonneau](https://github.com/mvisonneau))
- \(MODULES-3704\) Update gemfile template to be identical [\#519](https://github.com/voxpupuli/puppet-rabbitmq/pull/519) ([hunner](https://github.com/hunner))
- Fix sync [\#518](https://github.com/voxpupuli/puppet-rabbitmq/pull/518) ([hunner](https://github.com/hunner))
- Allows deprecation errors [\#516](https://github.com/voxpupuli/puppet-rabbitmq/pull/516) ([pmcmaw](https://github.com/pmcmaw))
- Bug fix for when queue names include spaces [\#512](https://github.com/voxpupuli/puppet-rabbitmq/pull/512) ([Bubbad](https://github.com/Bubbad))
- Do not check cert when acquiring rabbitmqadmin with wget. [\#478](https://github.com/voxpupuli/puppet-rabbitmq/pull/478) ([modax](https://github.com/modax))
- Cleanup void tcp options [\#464](https://github.com/voxpupuli/puppet-rabbitmq/pull/464) ([mpolenchuk](https://github.com/mpolenchuk))

## [5.6.0](https://github.com/voxpupuli/puppet-rabbitmq/tree/5.6.0) (2016-10-25)

[Full Changelog](https://github.com/voxpupuli/puppet-rabbitmq/compare/5.5.0...5.6.0)

**Implemented enhancements:**

- Allow ha-sync-batch-size for rabbitmq\_policy definition to be integer  [\#500](https://github.com/voxpupuli/puppet-rabbitmq/pull/500) ([mxftw](https://github.com/mxftw))
- Ability to set management\_hostname in rabbitmqadmin.conf [\#498](https://github.com/voxpupuli/puppet-rabbitmq/pull/498) ([tampakrap](https://github.com/tampakrap))

**Merged pull requests:**

- \(MODULES-3983\) Update parallel\_tests for ruby 2.0.0 moduleSync [\#514](https://github.com/voxpupuli/puppet-rabbitmq/pull/514) ([pmcmaw](https://github.com/pmcmaw))
- \[FM-5719\] Release prep for unsupported release 5.6.0 [\#513](https://github.com/voxpupuli/puppet-rabbitmq/pull/513) ([wilson208](https://github.com/wilson208))
- set $real\_package\_source to undef instead of empty [\#507](https://github.com/voxpupuli/puppet-rabbitmq/pull/507) ([bastelfreak](https://github.com/bastelfreak))
- Release 5.5.0 [\#503](https://github.com/voxpupuli/puppet-rabbitmq/pull/503) ([eputnam](https://github.com/eputnam))
- Update modulesync\_config \[a3fe424\] [\#501](https://github.com/voxpupuli/puppet-rabbitmq/pull/501) ([DavidS](https://github.com/DavidS))
- use unless instead of if, and empty? instead of !='' [\#491](https://github.com/voxpupuli/puppet-rabbitmq/pull/491) ([wyardley](https://github.com/wyardley))

## 5.5.0 (2016-08-29)
### Summary
Adds some exciting new features (listed below) for a long awaited release!

### Features
- Updates GPG signing key
- Now add additional config variables with the `config_additional_variable` parameter!
- Configure your management plugin with the new `collect_statistics_interval` parameter!
- Enjoy more robust tuning of your TCP configuration with:
  - `tcp_backlog`
  - `tcp_sndbuf`
  - `tcp_recbuf`

### Bugfixes
- MODULES-3740 - RabbitMQ template missing important config parameter

## 5.4.0 (2016-05-11)
### Summary
Adds several new parameters, features, and lots of bugfixes

#### Features
- Adds configuration for rabbitmq\_shovel plugin including static shovels
- (MODULE-2040) Add configuration of `auth_backends`
- Adds the `config_management_variables` parameter
- Adds `heartbeat` parameter
- Adds `rabbitmq_version` fact
- Adds ipv6 support to `rabbitmqadmin`
- MODULES-3148: Allow shards-per-node for rabbitmq\_policy definition to be integer
- Adds `rabbitmq_nodename` fact
- Allow passing architecture to `apt::source`

#### Bugfixes
- MODULES-2252 - fix "Command execution expired" issue
- Fixes an issue with Puppet 4+ when run from a cron job
- Updates RedHat to use yum instead of rpm
- Fixes the `$file_limit` parameter to allow integers
- MODULES-2252 - fix "Could not prefetch rabbitmq\_exchange provider 'rabbitmqadmin': 757: unexpected token at 'fanout'" issue
- Improves error message when policy definition value is not a string
- MODULES-2645 add apt::update requirement for Debian
- Fixes pinning for apt on Debian based distros
- Updates install.pp to ensure that mnesia\_base directory exists
- Fixes rabbitmqadmin url
- Updates default `$package_gpg_key` to https
- Fixes `curl --noproxy` command to set host dynamically
- Ignore system umask when generating enabled\_plugins file
- Fix to skip federated queues in the output
- Updates module dependencies to use `puppet-staging` instead of `nanliu-staging`
- Fixes bug where `rabbitmq_management` block is created twice
- Fixes `rabbitmq_parameter` type check for `add-forward-headers` to require boolean.
- Fixes an issue when `$node_ip_address` is 'UNSET'
- Fixes package installation on OpenBSD
- Fixes bug that shows new user password changes on noop runs
- (MODULES-3295) Allow ssl => false without warning

## 5.3.1 (2015-10-07)
### Summary
Adds a new resource type and a few ssl management parameters

#### Features
- Add rabbitmq\_parameter type
- Add management\_ssl parameter to rabbitmq class
- Add stomp\_ssl\_only parameter to rabbitmq class

#### Bugfixes
- file\_limit validation and el7 management
- Fix mnesia dir not getting wiped
- Fix message-ttl and max-length integer conversion in rabbitmq\_policy
- Allow managing erlang cookie without config\_cluster

## 5.3.0 (2015-05-26) [YANKED]
### Summary
This is a deleted release. It did not follow semver.

## 5.2.3 (2015-06-23)
### Summary
This is a patch release that updates the dependency requirements in the metadata.

## 5.2.2 (2015-06-09)
### Summary
This is a bugfix to allow the rabbitmq\_exchange type's internal/durable/auto\_delete attributes work when they are not explicitly passed.

### Bugfixes
- Fix rabbitmq\_exchange create when internal/durable/auto\_delete are not specified
- Start unit testing on puppet 4
- Add default value to tcp\_listen\_options

## 5.2.1 (2015-05-26)
### Summary
This release includes a fix for idempotency between puppet runs, as well as Readme updates

#### Features
- Readme updates
- Testing updates

#### Bugfixes
- Ensure idempotency between Puppet runs

## 5.2.0 (2015-04-28)
### Summary
This release adds several new features for expanded configuration, support for SSL Ciphers, several bugfixes, and improved tests.

#### Features
- New parameters to class `rabbitmq`
  - `ssl_ciphers`
- New parameters to class `rabbitmq::config`
  - `interface`
  - `ssl_interface`
- New parameters to type `rabbitmq_exchange`
  - `internal`
  - `auto_delete`
  - `durable`
- Adds syncing with Modulesync
- Adds support for SSL Ciphers
- Adds `file_limit` support for RedHat platforms

#### Bugfixes
- Will not create `rabbitmqadmin.conf` if admin is disabled
- Fixes `check_password`
- Fix to allow bindings and queues to be created when non-default management port is being used by rabbitmq. (MODULES-1856)
- `rabbitmq_policy` converts known parameters to integers
- Updates apt key for full fingerprint compliance.
- Adds a missing `routing_key` param to rabbitmqadmin absent binding call.

## 5.1.0 (2015-03-10)
### Summary
This release adds several features for greater flexibility in configuration of rabbitmq, includes a number of bug fixes, and bumps the minimum required version of puppetlabs-stdlib to 3.0.0.

#### Changes to defaults
- The default environment variables in `rabbitmq::config` have been renamed from `RABBITMQ_NODE_PORT` and `RABBITMQ_NODE_IP_ADDRESS` to `NODE_PORT` and `NODE_IP_ADDRESS` (MODULES-1673)

#### Features
- New parameters to class `rabbitmq`
  - `file_limit`
  - `interface`
  - `ldap_other_bind`
  - `ldap_config_variables`
  - `ssl_interface`
  - `ssl_versions`
  - `rabbitmq_group`
  - `rabbitmq_home`
  - `rabbitmq_user`
- Add `rabbitmq_queue` and `rabbitmq_binding` types
- Update the providers to be able to retry commands

#### Bugfixes
- Cleans up the formatting for rabbitmq.conf for readability
- Update tag splitting in the `rabbitmqctl` provider for `rabbitmq_user` to work with comma or space separated tags
- Do not enforce the source value for the yum provider (MODULES-1631)
- Fix conditional around `$pin`
- Remove broken SSL option in rabbitmqadmin.conf (MODULES-1691)
- Fix issues in `rabbitmq_user` with admin and no tags
- Fix issues in `rabbitmq_user` with tags not being sorted
- Fix broken check for existing exchanges in `rabbitmq_exchange`

## 5.0.0 (2014-12-22)
### Summary

This release fixes a longstanding security issue where the rabbitmq
erlang cookie was exposed as a fact by managing the cookie with a
provider. It also drops support for Puppet 2.7, adds many features
and fixes several bugs.

#### Backwards-incompatible Changes

- Removed the rabbitmq\_erlang\_cookie fact and replaced the logic to
  manage that cookie with a provider.
- Dropped official support for Puppet 2.7 (EOL 9/30/2014
  https://groups.google.com/forum/#!topic/puppet-users/QLguMcLraLE )
- Changed the default value of $rabbitmq::params::ldap\_user\_dn\_pattern
  to not contain a variable
- Removed deprecated parameters: $rabbitmq::cluster\_disk\_nodes,
  $rabbitmq::server::manage\_service, and
  $rabbitmq::server::config\_mirrored\_queues

#### Features

- Add tcp\_keepalive parameter to enable TCP keepalive
- Use https to download rabbitmqadmin tool when $rabbitmq::ssl is true
- Add key\_content parameter for offline Debian package installations
- Use 16 character apt key to avoid potential collisions
- Add rabbitmq\_policy type, including support for rabbitmq <3.2.0
- Add rabbitmq::ensure\_repo parameter
- Add ability to change rabbitmq\_user password
- Allow disk as a valid cluster node type

#### Bugfixes

- Avoid attempting to install rabbitmqadmin via a proxy (since it is
  downloaded from localhost)
- Optimize check for RHEL GPG key
- Configure ssl\_listener in stomp only if using ssl
- Use rpm as default package provider for RedHat, bringing the module in
  line with the documented instructions to manage erlang separately and allowing
  the default version and source parameters to become meaningful
- Configure cacertfile only if verify\_none is not set
- Use -q flag for rabbitmqctl commands to avoid parsing inconsistent
  debug output
- Use the -m flag for rabbitmqplugins commands, again to avoid parsing
  inconsistent debug output
- Strip backslashes from the rabbitmqctl output to avoid parsing issues
- Fix limitation where version parameter was ignored
- Add /etc/rabbitmq/rabbitmqadmin.conf to fix rabbitmqadmin port usage
  when ssl is on
- Fix linter errors and warnings
- Add, update, and fix tests
- Update docs

## 4.1.0 (2014-08-20)
### Summary

This release adds several new features, fixes bugs, and improves tests and
documentation.

#### Features
- Autorequire the rabbitmq-server service in the rabbitmq\_vhost type
- Add credentials to rabbitmqadmin URL
- Added $ssl\_only parameter to rabbitmq, rabbitmq::params, and
rabbitmq::config
- Added property tags to rabbitmq\_user provider

#### Bugfixes
- Fix erroneous commas in rabbitmq::config
- Use correct ensure value for the rabbitmq\_stomp rabbitmq\_plugin
- Set HOME env variable to nil when leveraging rabbitmq to remove type error
from Python script
- Fix location for rabbitmq-plugins for RHEL
- Remove validation for package\_source to allow it to be set to false
- Allow LDAP auth configuration without configuring stomp
- Added missing $ssl\_verify and $ssl\_fail\_if\_no\_peer\_cert to rabbitmq::config

## 4.0.0 (2014-05-16)
### Summary

This release includes many new features and bug fixes.  With the exception of
erlang management this should be backwards compatible with 3.1.0.

#### Backwards-incompatible Changes
- erlang\_manage was removed.  You will need to manage erlang separately. See
the README for more information on how to configure this.

#### Features
- Improved SSL support
- Add LDAP support
- Add ability to manage RabbitMQ repositories
- Add ability to manage Erlang kernel configuration options
- Improved handling of user tags
- Use nanliu-staging module instead of hardcoded 'curl'
- Switch to yum or zypper provider instead of rpm
- Add ability to manage STOMP plugin installation.
- Allow empty permission fields
- Convert existing system tests to beaker acceptance tests.

#### Bugfixes
- exchanges no longer recreated on each puppet run if non-default vhost is used
- Allow port to be UNSET
- Re-added rabbitmq::server class
- Deprecated previously unused manage\_service variable in favor of 
  service\_manage
- Use correct key for rabbitmq apt::source
- config\_mirrored\_queues variable removed
  - It previously did nothing, will now at least throw a warning if you try to
    use it
- Remove unnecessary dependency on Class['rabbitmq::repo::rhel'] in
  rabbitmq::install


## 3.1.0 (2013-09-14)
### Summary

This release focuses on a few small (but critical) bugfixes as well as extends
the amount of custom RabbitMQ configuration you can do with the module.

#### Features
- You can now change RabbitMQ 'Config Variables' via the parameter `config_variables`.
- You can now change RabbitMQ 'Environment Variables' via the parameter `environment_variables`.
- ArchLinux support added.

#### Fixes
- Make use of the user/password parameters in rabbitmq\_exchange{}
- Correct the read/write parameter order on set\_permissions/list\_permissions as
  they were reversed.
- Make the module pull down 3.1.5 by default.

## 3.0.0 (2013-07-18)
### Summary

This release heavily refactors the RabbitMQ and changes functionality in
several key ways.  Please pay attention to the new README.md file for
details of how to interact with the class now.  Puppet 3 and RHEL are
now fully supported.  The default version of RabbitMQ has changed to
a 3.x release.

#### Bugfixes

- Improve travis testing options.
- Stop reimporting the GPG key on every run on RHEL and Debian.
- Fix documentation to make it clear you don't have to set provider => each time.
- Reference the standard rabbitmq port in the documentation instead of a custom port.
- Fixes to the README formatting.

#### Features
- Refactor the module to fix RHEL support.  All interaction with the module
is now done through the main rabbitmq class.
- Add support for mirrored queues (Only on Debian family distributions currently)
- Add rabbitmq\_exchange provider (using rabbitmqadmin)
- Add new `rabbitmq` class parameters:
  - `manage_service`: Boolean to choose if Puppet should manage the service. (For pacemaker/HA setups)
- Add SuSE support.

#### Incompatible Changes

- Rabbitmq::server has been removed and is now rabbitmq::config.  You should
not use this class directly, only via the main rabbitmq class.

## 2.1.0 (2013-04-11)

- remove puppetversion from rabbitmq.config template
- add cluster support
- escape resource names in regexp

## 2.0.2 (2012-07-31)
- Re-release 2.0.1 with $EDITOR droppings cleaned up

## 2.0.0 (2012-05-03)
- added support for new-style admin users
- added support for rabbitmq 2.7.1

## 2.0.0rc1 (2011-06-14)
- Massive refactor:
  - added native types for user/vhost/user\_permissions
  - added apt support for vendor packages
  - added smoke tests

## 1.0.4 (2011-04-08)
- Update module for RabbitMQ 2.4.1 and rabbitmq-plugin-stomp package.

## 1.0.3 (2011-03-24)
- Initial release to the forge.  Reviewed by Cody.  Whitespace is good.

## 1.0.2 (2011-03-22)
- Whitespace only fix again...  ack '\t' is my friend...

## 1.0.1 (2011-03-22)
- Whitespace only fix.

## 1.0.0 (2011-03-22)
- Initial Release.  Manage the package, file and service.


\* *This Change Log was automatically generated by [github_changelog_generator](https://github.com/skywinder/Github-Changelog-Generator)*