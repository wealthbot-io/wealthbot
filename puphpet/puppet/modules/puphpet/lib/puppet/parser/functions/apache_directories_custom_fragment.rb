#
# apache_directories_custom_fragment.rb
#

module Puppet::Parser::Functions

  newfunction(:apache_directories_custom_fragment, :type => :rvalue, :doc => <<-'ENDHEREDOC') do |args|

    Takes 'directories' hash match puppet-apache vhosts requirement. Explodes custom_fragment list into
    \n separated string.
    ENDHEREDOC

    unless args.length == 1
      raise Puppet::ParseError, ("apache_directories_custom_fragment(): wrong number of arguments (#{args.length}; must be 1)")
    end

    directories = args[0]

    unless directories.is_a?(Hash)
      raise Puppet::ParseError, ("apache_directories_custom_fragment(): expects a hash)")
    end

    directories.each do |index, directory|
      next if !directory.key?('custom_fragment')
      next if !directory['custom_fragment'].is_a?(Array)

      directories[index]['custom_fragment'] = directory['custom_fragment'].join("\n    ")
    end

    return directories

  end
end
