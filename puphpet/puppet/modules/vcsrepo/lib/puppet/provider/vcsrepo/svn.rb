require File.join(File.dirname(__FILE__), '..', 'vcsrepo')

Puppet::Type.type(:vcsrepo).provide(:svn, parent: Puppet::Provider::Vcsrepo) do
  desc 'Supports Subversion repositories'

  commands svn: 'svn',
           svnadmin: 'svnadmin',
           svnlook: 'svnlook'

  has_features :filesystem_types, :reference_tracking, :basic_auth, :configuration, :conflict, :depth,
               :include_paths

  def create
    check_force
    if !@resource.value(:source)
      if @resource.value(:includes)
        raise Puppet::Error, 'Specifying include paths on a nonexistent repo.'
      end
      create_repository(@resource.value(:path))
    else
      checkout_repository(@resource.value(:source),
                          @resource.value(:path),
                          @resource.value(:revision),
                          @resource.value(:depth))
    end
    if @resource.value(:includes)
      validate_version
      update_includes(@resource.value(:includes))
    end
    update_owner
  end

  def working_copy_exists?
    return false unless File.directory?(@resource.value(:path))
    if @resource.value(:source)
      begin
        svn('info', @resource.value(:path))
        return true
      rescue Puppet::ExecutionFailure
        return false
      end
    else
      begin
        svnlook('uuid', @resource.value(:path))
        return true
      rescue Puppet::ExecutionFailure
        return false
      end
    end
  end

  def exists?
    working_copy_exists?
  end

  def destroy
    FileUtils.rm_rf(@resource.value(:path))
  end

  def latest?
    at_path do
      (revision >= latest) && (@resource.value(:source) == source)
    end
  end

  def buildargs
    args = ['--non-interactive']
    if @resource.value(:basic_auth_username) && @resource.value(:basic_auth_password)
      args.push('--username', @resource.value(:basic_auth_username))
      args.push('--password', @resource.value(:basic_auth_password))
      args.push('--no-auth-cache')
    end

    if @resource.value(:configuration)
      args.push('--config-dir', @resource.value(:configuration))
    end

    if @resource.value(:trust_server_cert) != :false
      args.push('--trust-server-cert')
    end

    args
  end

  def latest
    args = buildargs.push('info', '-r', 'HEAD')
    at_path do
      svn(*args)[%r{^Revision:\s+(\d+)}m, 1]
    end
  end

  def source
    args = buildargs.push('info')
    at_path do
      svn(*args)[%r{^URL:\s+(\S+)}m, 1]
    end
  end

  def source=(desired)
    args = buildargs.push('switch')
    if @resource.value(:force)
      args.push('--force')
    end
    if @resource.value(:revision)
      args.push('-r', @resource.value(:revision))
    end
    if @resource.value(:conflict)
      args.push('--accept', @resource.value(:conflict))
    end
    args.push(desired)
    at_path do
      svn(*args)
    end
    update_owner
  end

  def revision
    args = buildargs.push('info')
    at_path do
      svn(*args)[%r{^Revision:\s+(\d+)}m, 1]
    end
  end

  def revision=(desired)
    args = if @resource.value(:source)
             buildargs.push('switch', '-r', desired, @resource.value(:source))
           else
             buildargs.push('update', '-r', desired)
           end

    if @resource.value(:force)
      args.push('--force')
    end
    if @resource.value(:conflict)
      args.push('--accept', @resource.value(:conflict))
    end

    at_path do
      svn(*args)
    end
    update_owner
  end

  def includes
    return nil if Gem::Version.new(return_svn_client_version) < Gem::Version.new('1.6.0')
    get_includes('.')
  end

  def includes=(desired)
    validate_version
    exists = includes
    old_paths = exists - desired
    new_paths = desired - exists
    # Remove paths that are no longer specified
    old_paths.each { |path| delete_include(path) }
    update_includes(new_paths)
  end

  private

  def get_includes(directory)
    at_path do
      args = buildargs.push('info', directory)
      if svn(*args)[%r{^Depth:\s+(\w+)}m, 1] != 'empty'
        return directory[2..-1].gsub(File::SEPARATOR, '/')
      end
      Dir.entries(directory).map { |entry|
        next if ['.', '..', '.svn'].include?(entry)
        entry = File.join(directory, entry)
        if File.directory?(entry)
          get_includes(entry)
        elsif File.file?(entry)
          entry[2..-1].gsub(File::SEPARATOR, '/')
        end
      }.flatten.compact!
    end
  end

  def delete_include(path)
    at_path do
      # svn version 1.6 has an incorrect implementation of the `exclude`
      # parameter to `--set-depth`; it doesn't handle files, only
      # directories. I know, I rolled my eyes, too.
      svn_ver = return_svn_client_version
      if Gem::Version.new(svn_ver) < Gem::Version.new('1.7.0') && !File.directory?(path)
        # In the non-happy case, we delete the file, and check if the only
        # thing left in that directory is the .svn folder. If that's the case,
        # the loop below will take care of excluding the parent directory, and
        # we're back to a happy case. But, if that's not the case, we need to
        # fire off a warning telling the user the path can't be excluded.
        Puppet.debug "Vcsrepo[#{@resource.name}]: Need to handle #{path} removal specially"
        File.delete(path)
        if Dir.entries(File.dirname(path)).sort != ['.', '..', '.svn']
          Puppet.warning "Unable to exclude #{path} from Vcsrepo[#{@resource.name}]; update to subversion >= 1.7"
        end

      else
        Puppet.debug "Vcsrepo[#{@resource.name}]: Can remove #{path} directly using svn"
        args = buildargs.push('update', '--set-depth', 'exclude', path)
        svn(*args)
      end

      # Keep walking up the parent directories of this include until we find
      # a non-empty folder, excluding as we go.
      while (path = path.rpartition(File::SEPARATOR)[0]) != ''
        entries = Dir.entries(path).sort
        break if entries != ['.', '..'] && entries != ['.', '..', '.svn']
        args = buildargs.push('update', '--set-depth', 'exclude', path)
        svn(*args)
      end
    end
  end

  def checkout_repository(source, path, revision, depth)
    args = buildargs.push('checkout')
    if revision
      args.push('-r', revision)
    end
    if @resource.value(:includes)
      # Make root checked out at empty depth to provide sparse directories
      args.push('--depth', 'empty')
    elsif depth
      args.push('--depth', depth)
    end
    args.push(source, path)
    svn(*args)
  end

  def create_repository(path)
    args = ['create']
    if @resource.value(:fstype)
      args.push('--fs-type', @resource.value(:fstype))
    end
    args << path
    svnadmin(*args)
  end

  def update_owner
    set_ownership if @resource.value(:owner) || @resource.value(:group)
  end

  def update_includes(paths)
    at_path do
      args = buildargs.push('update')
      args.push('--depth', 'empty')
      if @resource.value(:revision)
        args.push('-r', @resource.value(:revision))
      end
      parents = paths.map { |path| File.dirname(path) }
      parents = make_include_paths(parents)
      args.push(*parents)
      svn(*args)

      args = buildargs.push('update')
      if @resource.value(:revision)
        args.push('-r', @resource.value(:revision))
      end
      if @resource.value(:depth)
        args.push('--depth', @resource.value(:depth))
      end
      args.push(*paths)
      svn(*args)
    end
  end

  def make_include_paths(includes)
    includes.map { |inc|
      prefix = nil
      inc.split('/').map do |path|
        prefix = [prefix, path].compact.join('/')
      end
    }.flatten
  end

  def return_svn_client_version
    Facter.value('vcsrepo_svn_ver').dup
  end

  def validate_version
    svn_ver = return_svn_client_version
    raise "Includes option is not available for SVN versions < 1.6. Version installed: #{svn_ver}" if Gem::Version.new(svn_ver) < Gem::Version.new('1.6.0')
  end
end
