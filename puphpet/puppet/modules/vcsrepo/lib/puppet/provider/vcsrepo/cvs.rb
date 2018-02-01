require File.join(File.dirname(__FILE__), '..', 'vcsrepo')

Puppet::Type.type(:vcsrepo).provide(:cvs, parent: Puppet::Provider::Vcsrepo) do
  desc 'Supports CVS repositories/workspaces'

  commands cvs: 'cvs'
  has_features :gzip_compression, :reference_tracking, :modules, :cvs_rsh, :user

  def create
    check_force
    if !@resource.value(:source)
      create_repository(@resource.value(:path))
    else
      checkout_repository
    end
    update_owner
  end

  def exist?
    working_copy_exists?
  end

  def working_copy_exists?
    if @resource.value(:source)
      directory = File.join(@resource.value(:path), 'CVS')
      return false unless File.directory?(directory)
      begin
        at_path { runcvs('-nq', 'status', '-l') }
        return true
      rescue Puppet::ExecutionFailure
        return false
      end
    else
      directory = File.join(@resource.value(:path), 'CVSROOT')
      return false unless File.directory?(directory)
      config = File.join(@resource.value(:path), 'CVSROOT', 'config,v')
      return false unless File.exist?(config)
      true
    end
  end

  def destroy
    FileUtils.rm_rf(@resource.value(:path))
  end

  def latest?
    Puppet.debug "Checking for updates because 'ensure => latest'"
    at_path do
      # We cannot use -P to prune empty dirs, otherwise
      # CVS would report those as "missing", regardless
      # if they have contents or updates.
      is_current = (runcvs('-nq', 'update', '-d').strip == '')
      unless is_current then Puppet.debug "There are updates available on the checkout's current branch/tag." end
      return is_current
    end
  end

  def latest
    # CVS does not have a conecpt like commit-IDs or change
    # sets, so we can only have the current branch name (or the
    # requested one, if that differs) as the "latest" revision.
    should = @resource.value(:revision)
    current = revision
    (should != current) ? should : current
  end

  def revision
    unless @rev
      if File.exist?(tag_file)
        contents = File.read(tag_file).strip
        # Note: Doesn't differentiate between N and T entries
        @rev = contents[1..-1]
      else
        @rev = 'HEAD'
      end
      Puppet.debug "Checkout is on branch/tag '#{@rev}'"
    end
    @rev
  end

  def revision=(desired)
    at_path do
      runcvs('update', '-dr', desired, '.')
      update_owner
      @rev = desired
    end
  end

  def source
    File.read(File.join(@resource.value(:path), 'CVS', 'Root')).chomp
  end

  def source=(_desired)
    create # recreate
  end

  def module
    File.read(File.join(@resource.value(:path), 'CVS', 'Repository')).chomp
  end

  def module=(_desired)
    create # recreate
  end

  private

  def tag_file
    File.join(@resource.value(:path), 'CVS', 'Tag')
  end

  def checkout_repository
    dirname, basename = File.split(@resource.value(:path))
    Dir.chdir(dirname) do
      args = ['-d', @resource.value(:source)]
      if @resource.value(:compression)
        args.push('-z', @resource.value(:compression))
      end
      args.push('checkout')
      if @resource.value(:revision)
        args.push('-r', @resource.value(:revision))
      end
      args.push('-d', basename, module_name)
      runcvs(*args)
    end
  end

  # If no module is provided, use '.', the root of the repo
  def module_name
    @resource.value(:module) || '.'
  end

  def create_repository(path)
    runcvs('-d', path, 'init')
  end

  def update_owner
    set_ownership if @resource.value(:owner) || @resource.value(:group)
  end

  def runcvs(*args)
    if @resource.value(:cvs_rsh)
      Puppet.debug 'Using CVS_RSH = ' + @resource.value(:cvs_rsh)
      e = { CVS_RSH: @resource.value(:cvs_rsh) }
    else
      e = {}
    end

    if @resource.value(:user) && @resource.value(:user) != Facter['id'].value
      Puppet.debug 'Running as user ' + @resource.value(:user)
      Puppet::Util::Execution.execute([:cvs, *args], uid: @resource.value(:user), custom_environment: e, combine: true, failonfail: true)
    else
      Puppet::Util::Execution.execute([:cvs, *args], custom_environment: e, combine: true, failonfail: true)
    end
  end
end
