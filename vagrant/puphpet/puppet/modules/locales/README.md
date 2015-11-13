[![Build Status](https://secure.travis-ci.org/attachmentgenie/attachmentgenie-locales.png)](http://travis-ci.org/attachmentgenie/attachmentgenie-locales)

Puppet Locales Module
=====================

Module for configuring locales.

Installation
------------

Clone this repo to a locales directory under your Puppet modules directory:

    git clone git://github.com/attachmentgenie/attachmentgenie-locales.git locales

If you don't have a Puppet Master you can create a manifest file
based on the notes below and run Puppet in stand-alone mode
providing the module directory you cloned this repo to:

    puppet apply --modulepath=modules test_locales.pp


Usage
-----

If you include the locales class the standard available locale list and
default locale will be build and configured:

    include locales

You can override the default locale and available locales by including
the module with this special syntax:

    class { locales:
      default_value  => "en_US.UTF-8,
      available      => ["en_US.UTF-8 UTF-8", "en_GB.UTF-8 UTF-8"]
    }
