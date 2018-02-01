# Contributing to augeasproviders

[![Build Status](https://secure.travis-ci.org/hercules-team/augeasproviders.png?branch=master)](http://travis-ci.org/hercules-team/augeasproviders)

## Writing tests

Tests for a `typename` provider live at `spec/unit/puppet/typename_spec.rb` and
their corresponding fixture (starting file) under
`spec/fixture/unit/puppet/typename/`.

Use an rspec context section per fixture and have multiple examples within the
section using it.

Tests use real resources which are applied via the
`AugeasSpec::Fixtures::apply` method to a temporary file created from the
original fixture.  Once applied, the temporary file is tested using one of
two methods:

1. Load file with ruby-augeas (`aug_open` helper) and perform match/get queries
to test particular features of the tree.
1. Define whole or part of the tree in augparse `{ }` syntax and use `augparse`
or `augparse_filter` helpers to compare the file against the expected tree.

The latter will be much easier and more robust as it will compare all aspects
of the tree, while the first might be needed for some edge cases (empty files
etc).

See also this writeup on [testing techniques for Puppet providers using
Augeas](http://m0dlx.com/blog/Testing_techniques_for_Puppet_providers_using_Augeas.html)
which shows this process for augeasproviders.

Execute `rake spec` in the root directory to run all tests.

## Thoughts about testing methods

After applying the resource, there are a few ways we could test the results of
the file.

* use augparse?  No API today, could generate module file and shell out.
* use Config::Augeas::Validator?  Need to write separate rules, no rootdir
  support and is Perl, not Ruby.
* use XML comparison?  No ruby-augeas support for aug_to_xml.
* use ruby-augeas?  Using this as we can test for specific nodes, values etc
  and compare with rspec.
* use File.read + rspec?  Comparing the whole file will be a problem if Augeas
  lenses change whitespace.

## Requirements

Install bundler and run `bundle install` to get all gems required for
development or see the contents of Gemfile.

## Patches

Please send pull requests via GitHub, or patches via git send-email to the
author.
