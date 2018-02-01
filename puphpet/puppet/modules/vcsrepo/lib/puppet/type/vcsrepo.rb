require 'pathname'
require 'puppet/parameter/boolean'

Puppet::Type.newtype(:vcsrepo) do
  desc 'A local version control repository'

  feature :gzip_compression,
          'The provider supports explicit GZip compression levels'
  feature :basic_auth,
          'The provider supports HTTP Basic Authentication'
  feature :bare_repositories,
          "The provider differentiates between bare repositories
          and those with working copies",
          methods: [:bare_exists?, :working_copy_exists?]

  feature :filesystem_types,
          'The provider supports different filesystem types'

  feature :reference_tracking,
          "The provider supports tracking revision references that can change
           over time (eg, some VCS tags and branch names)"

  feature :ssh_identity,
          'The provider supports a configurable SSH identity file'

  feature :user,
          'The provider can run as a different user'

  feature :modules,
          'The repository contains modules that can be chosen of'

  feature :multiple_remotes,
          'The repository tracks multiple remote repositories'

  feature :configuration,
          'The configuration directory to use'

  feature :cvs_rsh,
          'The provider understands the CVS_RSH environment variable'

  feature :depth,
          'The provider can do shallow clones or set scope limit'

  feature :branch,
          'The name of the branch'

  feature :p4config,
          'The provider understands Perforce Configuration'

  feature :submodules,
          'The repository contains submodules which can be optionally initialized'

  feature :conflict,
          'The provider supports automatic conflict resolution'

  feature :include_paths,
          'The provider supports checking out only specific paths'

  ensurable do
    attr_accessor :latest

    def insync?(is)
      @should ||= []

      case should
      when :present
        return true unless [:absent, :purged, :held].include?(is)
      when :latest
        return true if is == :latest
        return false
      when :bare
        return is == :bare
      when :mirror
        return is == :mirror
      when :absent
        return is == :absent
      end
    end

    newvalue :present do
      if !provider.exists?
        provider.create
      elsif provider.class.feature?(:bare_repositories) && provider.bare_exists?
        provider.convert_bare_to_working_copy
      end
    end

    newvalue :bare, required_features: [:bare_repositories] do
      if !provider.exists?
        provider.create
      elsif provider.working_copy_exists?
        provider.convert_working_copy_to_bare
      elsif provider.mirror?
        provider.set_no_mirror
      end
    end

    newvalue :mirror, required_features: [:bare_repositories] do
      if !provider.exists?
        provider.create
      elsif provider.working_copy_exists?
        provider.convert_working_copy_to_bare
      elsif !provider.mirror?
        provider.set_mirror
      end
    end

    newvalue :absent do
      provider.destroy
    end

    newvalue :latest, required_features: [:reference_tracking] do
      if provider.exists? && !@resource.value(:force)
        if provider.class.feature?(:bare_repositories) && provider.bare_exists?
          provider.convert_bare_to_working_copy
        end
        if provider.respond_to?(:update_references)
          provider.update_references
        end
        reference = if provider.respond_to?(:latest?)
                      provider.latest || provider.revision
                    else
                      resource.value(:revision) || provider.revision
                    end
        notice "Updating to latest '#{reference}' revision"
        provider.revision = reference
      else
        notice 'Creating repository from latest'
        provider.create
      end
    end

    def retrieve
      prov = @resource.provider
      raise Puppet::Error, 'Could not find provider' unless prov
      if prov.working_copy_exists?
        (@should.include?(:latest) && prov.latest?) ? :latest : :present
      elsif prov.class.feature?(:bare_repositories) && prov.bare_exists?
        if prov.mirror?
          :mirror
        else
          :bare
        end
      else
        :absent
      end
    end
  end

  newparam :path do
    desc 'Absolute path to repository'
    isnamevar
    validate do |value|
      path = Pathname.new(value)
      unless path.absolute?
        raise ArgumentError, "Path must be absolute: #{path}"
      end
    end
  end

  newproperty :source do
    desc 'The source URI for the repository'
    # Tolerate versions/providers that strip/add trailing slashes
    def insync?(is)
      # unwrap @should
      should = @should[0]
      return true if is == should
      begin
        if should[-1] == '/'
          return true if is == should[0..-2]
        elsif is[-1] == '/'
          return true if is[0..-2] == should
        end
      rescue
        return
      end
      false
    end
  end

  newparam :fstype, required_features: [:filesystem_types] do
    desc 'Filesystem type'
  end

  newproperty :revision do
    desc 'The revision of the repository'
    newvalue(%r{^\S+$})
  end

  newparam :owner do
    desc 'The user/uid that owns the repository files'
  end

  newparam :group do
    desc 'The group/gid that owns the repository files'
  end

  newparam :user do
    desc 'The user to run for repository operations'
  end

  newparam :excludes do
    desc "Local paths which shouldn't be tracked by the repository"
  end

  newproperty :includes, required_features: [:include_paths], array_matching: :all do
    desc 'Paths to be included from the repository'
    def insync?(is)
      if is.is_a?(Array) && @should.is_a?(Array)
        is.sort == @should.sort
      else
        is == @should
      end
    end
    validate do |path|
      raise Puppet::Error, "Include path '#{path}' starts with a '/'; remove it" if path[0..0] == '/'
      super(path)
    end
  end

  newparam(:force, boolean: true, parent: Puppet::Parameter::Boolean) do
    desc 'Force repository creation, destroying any files on the path in the process.'
    defaultto false
  end

  newparam :compression, required_features: [:gzip_compression] do
    desc 'Compression level'
    validate do |amount|
      unless Integer(amount).between?(0, 6)
        raise ArgumentError, "Unsupported compression level: #{amount} (expected 0-6)"
      end
    end
  end

  newparam :basic_auth_username, required_features: [:basic_auth] do
    desc 'HTTP Basic Auth username'
  end

  newparam :basic_auth_password, required_features: [:basic_auth] do
    desc 'HTTP Basic Auth password'
  end

  newparam :identity, required_features: [:ssh_identity] do
    desc 'SSH identity file'
  end

  newproperty :module, required_features: [:modules] do
    desc 'The repository module to manage'
  end

  newparam :remote, required_features: [:multiple_remotes] do
    desc 'The remote repository to track'
    defaultto 'origin'
  end

  newparam :configuration, required_features: [:configuration] do
    desc 'The configuration directory to use'
  end

  newparam :cvs_rsh, required_features: [:cvs_rsh] do
    desc 'The value to be used for the CVS_RSH environment variable.'
  end

  newparam :depth, required_features: [:depth] do
    desc 'The value to be used to do a shallow clone.'
  end

  newparam :branch, required_features: [:branch] do
    desc 'The name of the branch to clone.'
  end

  newparam :p4config, required_features: [:p4config] do
    desc 'The Perforce P4CONFIG environment.'
  end

  newparam :submodules, required_features: [:submodules] do
    desc 'Initialize and update each submodule in the repository.'
    newvalues(:true, :false)
    defaultto true
  end

  newparam :conflict do
    desc 'The action to take if conflicts exist between repository and working copy'
  end

  newparam :trust_server_cert do
    desc 'Trust server certificate'
    newvalues(:true, :false)
    defaultto :false
  end

  autorequire(:package) do
    ['git', 'git-core', 'mercurial', 'subversion']
  end
end
