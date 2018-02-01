require 'rspec-puppet-facts'
include RspecPuppetFacts
#                                                                       # Original fact sources:
add_custom_fact :puppetversion, Puppet.version                          # Facter, but excluded from rspec-puppet-facts
add_custom_fact :rabbitmq_version, '3.6.1'                              # puppet-rabbitmq
add_custom_fact :erl_ssl_path, '/usr/lib64/erlang/lib/ssl-7.3.3.1/ebin' # puppet-rabbitmq
