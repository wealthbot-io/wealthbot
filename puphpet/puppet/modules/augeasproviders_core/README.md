[![Puppet Forge Version](http://img.shields.io/puppetforge/v/herculesteam/augeasproviders_core.svg)](https://forge.puppetlabs.com/herculesteam/augeasproviders_core)
[![Puppet Forge Downloads](http://img.shields.io/puppetforge/dt/herculesteam/augeasproviders_core.svg)](https://forge.puppetlabs.com/herculesteam/augeasproviders_core)
[![Puppet Forge Endorsement](https://img.shields.io/puppetforge/e/herculesteam/augeasproviders_core.svg)](https://forge.puppetlabs.com/herculesteam/augeasproviders_core)
[![Build Status](https://img.shields.io/travis/hercules-team/augeasproviders_core/master.svg)](https://travis-ci.org/hercules-team/augeasproviders_core)
[![Coverage Status](https://img.shields.io/coveralls/hercules-team/augeasproviders_core.svg)](https://coveralls.io/r/hercules-team/augeasproviders_core)
[![Gemnasium](https://img.shields.io/gemnasium/hercules-team/augeasproviders_core.svg)](https://gemnasium.com/hercules-team/augeasproviders_core)

# augeasproviders\_core: library for building alternative Augeas-based providers for Puppet

This module provides a library for module authors to create new types and
providers around config files, using the Augeas configuration library to read
and modify them.

The advantage of using Augeas over the default Puppet `parsedfile`
implementations is that Augeas will go to great lengths to preserve file
formatting and comments, while also failing safely when needed.

If you're a user, you want to see the main augeasproviders project at
[augeasproviders.com](http://augeasproviders.com).

## Requirements

Ensure both Augeas and ruby-augeas 0.3.0+ bindings are installed and working as
normal.

See [Puppet/Augeas pre-requisites](http://docs.puppetlabs.com/guides/augeas.html#pre-requisites).

## Development documentation

See docs/ (run `make`) or [augeasproviders.com](http://augeasproviders.com/documentation/).

## Issues

Please file any issues or suggestions [on GitHub](https://github.com/hercules-team/augeasproviders_core/issues).
