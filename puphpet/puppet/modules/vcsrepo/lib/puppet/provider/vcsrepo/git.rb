require File.join(File.dirname(__FILE__), '..', 'vcsrepo')

Puppet::Type.type(:vcsrepo).provide(:git, parent: Puppet::Provider::Vcsrepo) do
  desc 'Supports Git repositories'

  has_command(:git, 'git') do
    environment('HOME' => ENV['HOME'])
  end

  has_features :bare_repositories, :reference_tracking, :ssh_identity, :multiple_remotes,
               :user, :depth, :branch, :submodules

  def create
    check_force
    if @resource.value(:revision) && ensure_bare_or_mirror?
      raise("Cannot set a revision (#{@resource.value(:revision)}) on a bare repository")
    end
    if !@resource.value(:source)
      if @resource.value(:ensure) == :mirror
        raise('Cannot init repository with mirror option, try bare instead')
      end

      init_repository
    else
      clone_repository(default_url, @resource.value(:path))
      update_remotes(@resource.value(:source))
      set_mirror if @resource.value(:ensure) == :mirror && @resource.value(:source).is_a?(Hash)

      if @resource.value(:revision)
        checkout
      end
      if !ensure_bare_or_mirror? && @resource.value(:submodules) == :true
        update_submodules
      end

    end
    update_owner_and_excludes
  end

  def destroy
    FileUtils.rm_rf(@resource.value(:path))
  end

  # Checks to see if the current revision is equal to the revision on the
  # remote (whether on a branch, tag, or reference)
  #
  # @return [Boolean] Returns true if the repo is on the latest revision
  def latest?
    revision == latest_revision
  end

  # Just gives the `should` value that we should be setting the repo to if
  # latest? returns false
  #
  # @return [String] Returns the target sha/tag/branch
  def latest
    if !@resource.value(:revision) && (branch = on_branch?)
      branch
    else
      @resource.value(:revision)
    end
  end

  # Get the current revision of the repo (tag/branch/sha)
  #
  # @return [String] Returns the branch/tag if the current sha matches the
  #                  remote; otherwise returns the current sha.
  def revision
    # HEAD is the default, but lets just be explicit here.
    get_revision('HEAD')
  end

  # Is passed the desired reference, whether a tag, rev, or branch. Should
  # handle transitions from a rev/branch/tag to a rev/branch/tag. Detached
  # heads should be treated like bare revisions.
  #
  # @param [String] desired The desired revision to which the repo should be
  #                         set.
  def revision=(desired)
    # just checkout tags and shas; fetch has already happened so they should be updated.
    checkout(desired)
    # branches require more work.
    if local_branch_revision?(desired)
      # reset instead of pull to avoid merge conflicts. assuming remote is
      # updated and authoritative.
      # TODO might be worthwhile to have an allow_local_changes param to decide
      # whether to reset or pull when we're ensuring latest.
      if @resource.value(:source)
        at_path { git_with_identity('reset', '--hard', "#{@resource.value(:remote)}/#{desired}") }
      else
        at_path { git_with_identity('reset', '--hard', desired.to_s) }
      end
    end
    # TODO: Would this ever reach here if it is bare?
    if !ensure_bare_or_mirror? && @resource.value(:submodules) == :true
      update_submodules
    end
    update_owner_and_excludes
  end

  def bare_exists?
    bare_git_config_exists? && !working_copy_exists?
  end

  def ensure_bare_or_mirror?
    [:bare, :mirror].include? @resource.value(:ensure)
  end

  # If :source is set to a hash (for supporting multiple remotes),
  # we search for the URL for :remote. If it doesn't exist,
  # we throw an error. If :source is just a string, we use that
  # value for the default URL.
  def default_url
    return @resource.value(:source) unless @resource.value(:source).is_a?(Hash)
    return @resource.value(:source)[@resource.value(:remote)] if @resource.value(:source).key?(@resource.value(:remote))
    raise("You must specify the URL for remote '#{@resource.value(:remote)}' in the :source hash")
  end

  def working_copy_exists?
    # NOTE: a change in the `default_url` will tell the type that this repo
    # doesn't exist (i.e. it triggers a "not the same repository" error).
    # Thus, changing the `source` property from a string to a string (which
    # changes the origin url), or if the @resource.value(:remote)'s url is
    # changed, the provider will require force.
    return false unless File.directory?(File.join(@resource.value(:path), '.git'))
    at_path do
      if @resource.value(:source)
        begin
          return git('config', '--get', "remote.#{@resource.value(:remote)}.url").chomp == default_url
        rescue Puppet::ExecutionFailure
          return false
        end
      else
        begin
          git('status')
          return true
        rescue Puppet::ExecutionFailure
          return false
        end
      end
    end
  end

  def exists?
    working_copy_exists? || bare_exists?
  end

  def remove_remote(remote)
    at_path do
      git_with_identity('remote', 'remove', remote)
    end
  end

  def update_remote_url(remote_name, remote_url)
    current = git_with_identity('config', '-l')

    return if remote_url.nil?
    # Check if remote exists at all, regardless of URL.
    # If remote doesn't exist, add it
    if !current.include? "remote.#{remote_name}.url"
      git_with_identity('remote', 'add', remote_name, remote_url)
      return true

    # If remote exists, but URL doesn't match, update URL
    elsif !current.include? "remote.#{remote_name}.url=#{remote_url}"
      git_with_identity('remote', 'set-url', remote_name, remote_url)
      return true
    else
      return false
    end
  end

  def source
    at_path do
      remotes = git('remote').split("\n")

      return git('config', '--get', "remote.#{remotes[0]}.url").chomp if remotes.size == 1
      Hash[remotes.map do |remote|
        [remote, git('config', '--get', "remote.#{remote}.url").chomp]
      end]
    end
  end

  def source=(desired)
    # NOTE: a change in the `default_url` will tell the type that this repo
    # doesn't exist (i.e. it triggers a "not the same repository" error).
    # Thus, a change from a string to a string (which changes the origin url),
    # or if the @resource.value(:remote)'s url is changed, the provider will
    # require force, without ever reaching this block. The recreation is
    # duplicated here in case something changes in the `working_copy_exists?`
    # logic.
    current = source
    if current.is_a?(Hash)
      current.keys.each do |remote|
        remove_remote(remote) if desired.is_a?(Hash) && !desired.key?(remote)
        remove_remote(remote) if desired.is_a?(String) && remote != @resource.value(:remote)
      end
    end
    if current.is_a?(String) && desired.is_a?(String)
      create # recreate
    else
      update_remotes(desired)
    end
  end

  def update_remotes(remotes)
    do_update = false

    # If supplied source is a hash of remote name and remote url pairs, then
    # we loop around the hash. Otherwise, we assume single url specified
    # in source property
    if remotes.is_a?(Hash)
      remotes.keys.sort.each do |remote_name|
        remote_url = remotes[remote_name]
        at_path { do_update |= update_remote_url(remote_name, remote_url) }
      end
    else
      at_path { do_update |= update_remote_url(@resource.value(:remote), remotes) }
    end

    # If at least one remote was added or updated, then we must
    # call the 'git remote update' command
    at_path { git_with_identity('remote', 'update') } if do_update == true
  end

  def update_references
    at_path do
      git_with_identity('fetch', @resource.value(:remote))
      git_with_identity('fetch', '--tags', @resource.value(:remote))
      update_owner_and_excludes
    end
  end

  # Convert working copy to bare
  #
  # Moves:
  #   <path>/.git
  # to:
  #   <path>/
  # and sets core.bare=true, and calls `set_mirror` if appropriate
  def convert_working_copy_to_bare
    return unless working_copy_exists? && !bare_exists?
    notice 'Converting working copy repository to bare repository'
    FileUtils.mv(File.join(@resource.value(:path), '.git'), tempdir)
    FileUtils.rm_rf(@resource.value(:path))
    FileUtils.mv(tempdir, @resource.value(:path))
    at_path do
      git('config', '--local', '--bool', 'core.bare', 'true')
      return unless @resource.value(:ensure) == :mirror
      raise('Cannot have empty repository that is also a mirror.') unless @resource.value(:source)
      set_mirror
    end
  end

  # Convert bare to working copy
  #
  # Moves:
  #   <path>/
  # to:
  #   <path>/.git
  # and sets core.bare=false, and calls `set_no_mirror` if appropriate
  def convert_bare_to_working_copy
    notice 'Converting bare repository to working copy repository'
    FileUtils.mv(@resource.value(:path), tempdir)
    FileUtils.mkdir(@resource.value(:path))
    FileUtils.mv(tempdir, File.join(@resource.value(:path), '.git'))
    if commits?
      at_path do
        git('config', '--local', '--bool', 'core.bare', 'false')
        reset('HEAD')
        git_with_identity('checkout', '--force')
        update_owner_and_excludes
      end
    end
    set_no_mirror if mirror?
  end

  def mirror?
    at_path do
      begin
        git('config', '--get-regexp', 'remote\..*\.mirror')
        return true
      rescue Puppet::ExecutionFailure
        return false
      end
    end
  end

  def set_mirror
    at_path do
      if @resource.value(:source).is_a?(String)
        git('config', "remote.#{@resource.value(:remote)}.mirror", 'true')
      else
        @resource.value(:source).keys.each do |remote|
          git('config', "remote.#{remote}.mirror", 'true')
        end
      end
    end
  end

  def set_no_mirror
    at_path do
      if @resource.value(:source).is_a?(String)
        begin
          git('config', '--unset', "remote.#{@resource.value(:remote)}.mirror")
        rescue Puppet::ExecutionFailure
          next
        end
      else
        @resource.value(:source).keys.each do |remote|
          begin
            git('config', '--unset', "remote.#{remote}.mirror")
          rescue Puppet::ExecutionFailure
            next
          end
        end
      end
    end
  end

  private

  # @!visibility private
  def bare_git_config_exists?
    return false unless File.exist?(File.join(@resource.value(:path), 'config'))
    begin
      at_path { git('config', '--list', '--file', 'config') }
      return true
    rescue Puppet::ExecutionFailure
      return false
    end
  end

  # @!visibility private
  def clone_repository(source, path)
    args = ['clone']
    if @resource.value(:depth) && @resource.value(:depth).to_i > 0
      args.push('--depth', @resource.value(:depth).to_s)
      if @resource.value(:revision) && !@resource.value(:branch)
        args.push('--branch', @resource.value(:revision).to_s)
      end
    end
    if @resource.value(:branch)
      args.push('--branch', @resource.value(:branch).to_s)
    end

    case @resource.value(:ensure)
    when :bare then args << '--bare'
    when :mirror then args << '--mirror'
    end

    if @resource.value(:remote) != 'origin'
      args.push('--origin', @resource.value(:remote))
    end
    if !working_copy_exists?
      args.push(source, path)
      Dir.chdir('/') do
        git_with_identity(*args)
      end
    else
      notice 'Repo has already been cloned'
    end
  end

  # @!visibility private
  def init_repository
    if @resource.value(:ensure) == :bare && working_copy_exists?
      convert_working_copy_to_bare
    elsif @resource.value(:ensure) == :present && bare_exists?
      convert_bare_to_working_copy
    else
      # normal init
      FileUtils.mkdir(@resource.value(:path))
      FileUtils.chown(@resource.value(:user), nil, @resource.value(:path)) if @resource.value(:user)
      args = ['init']
      if @resource.value(:ensure) == :bare
        args << '--bare'
      end
      at_path do
        git_with_identity(*args)
      end
    end
  end

  # @!visibility private
  def commits?
    at_path do
      begin
        commits = git_with_identity('rev-list', '--all', '--count').to_i
      rescue Puppet::ExecutionFailure
        commits = 0
      end
      return commits > 0
    end
  end

  # Will checkout a rev/branch/tag using the locally cached versions. Does not
  # handle upstream branch changes
  # @!visibility private
  def checkout(revision = @resource.value(:revision))
    if !local_branch_revision?(revision) && remote_branch_revision?(revision)
      # non-locally existant branches (perhaps switching to a branch that has never been checked out)
      at_path { git_with_identity('checkout', '--force', '-b', revision, '--track', "#{@resource.value(:remote)}/#{revision}") }
    else
      # tags, locally existant branches (perhaps outdated), and shas
      at_path { git_with_identity('checkout', '--force', revision) }
    end
  end

  # @!visibility private
  def reset(desired)
    at_path do
      git_with_identity('reset', '--hard', desired)
    end
  end

  # @!visibility private
  def update_submodules
    at_path do
      git_with_identity('submodule', 'update', '--init', '--recursive')
    end
  end

  # Determins if the branch exists at the upstream but has not yet been locally committed
  # @!visibility private
  def remote_branch_revision?(revision = @resource.value(:revision))
    # git < 1.6 returns '#{@resource.value(:remote)}/#{revision}'
    # git 1.6+ returns 'remotes/#{@resource.value(:remote)}/#{revision}'
    branch = at_path { branches.grep %r{(remotes/)?#{@resource.value(:remote)}/#{revision}$} }
    branch unless branch.empty?
  end

  # Determins if the branch is already cached locally
  # @!visibility private
  def local_branch_revision?(revision = @resource.value(:revision))
    at_path { branches.include?(revision) }
  end

  # @!visibility private
  def tag_revision?(revision = @resource.value(:revision))
    at_path { tags.include?(revision) }
  end

  # @!visibility private
  def branches
    at_path { git_with_identity('branch', '-a') }.tr('*', ' ').split(%r{\n}).map { |line| line.strip }
  end

  # git < 2.4 returns 'detached from'
  # git 2.4+ returns 'HEAD detached at'
  # @!visibility private
  def on_branch?
    at_path do
      matches = git_with_identity('branch', '-a').match %r{\*\s+(.*)}
      matches[1] unless matches[1] =~ %r{(\(detached from|\(HEAD detached at|\(no branch)}
    end
  end

  # @!visibility private
  def tags
    at_path { git_with_identity('tag', '-l') }.split(%r{\n}).map { |line| line.strip }
  end

  # @!visibility private
  def set_excludes
    # Excludes may be an Array or a String.
    at_path do
      open('.git/info/exclude', 'w') do |f|
        if @resource.value(:excludes).respond_to?(:each)
          @resource.value(:excludes).each { |ex| f.puts ex }
        else
          f.puts @resource.value(:excludes)
        end
      end
    end
  end

  # Finds the latest revision or sha of the current branch if on a branch, or
  # of HEAD otherwise.
  # @note Calls create which can forcibly destroy and re-clone the repo if
  #       force => true
  # @see get_revision
  #
  # @!visibility private
  # @return [String] Returns the output of get_revision
  def latest_revision
    # TODO: Why is create called here anyway?
    create if @resource.value(:force) && working_copy_exists?
    create unless working_copy_exists?

    branch = on_branch?
    return get_revision("#{@resource.value(:remote)}/#{branch}") if branch
    get_revision
  end

  # Returns the current revision given if the revision is a tag or branch and
  # matches the current sha. If the current sha does not match the sha of a tag
  # or branch, then it will just return the sha (ie, is not in sync)
  #
  # @!visibility private
  #
  # @param [String] rev The revision of which to check if it is current
  # @return [String] Returns the tag/branch of the current repo if it's up to
  #                  date; otherwise returns the sha of the requested revision.
  def get_revision(rev = 'HEAD')
    if @resource.value(:source)
      update_references
    else
      status = at_path { git_with_identity('status') }
      is_it_new = status =~ %r{Initial commit}
      if is_it_new
        status =~ %r{On branch (.*)}
        branch = Regexp.last_match(1)
        return branch
      end
    end
    current = at_path { git_with_identity('rev-parse', rev).strip }
    if @resource.value(:revision)
      canonical = if tag_revision?
                    # git-rev-parse will give you the hash of the tag object itself rather
                    # than the commit it points to by default. Using tag^0 will return the
                    # actual commit.
                    at_path { git_with_identity('rev-parse', "#{@resource.value(:revision)}^0").strip }
                  elsif local_branch_revision?
                    at_path { git_with_identity('rev-parse', @resource.value(:revision)).strip }
                  elsif remote_branch_revision?
                    at_path { git_with_identity('rev-parse', "#{@resource.value(:remote)}/#{@resource.value(:revision)}").strip }
                  else
                    # look for a sha (could match invalid shas)
                    at_path { git_with_identity('rev-parse', '--revs-only', @resource.value(:revision)).strip }
                  end
      raise("#{@resource.value(:revision)} is not a local or remote ref") if canonical.nil? || canonical.empty?
      current = @resource.value(:revision) if current == canonical
    end
    current
  end

  # @!visibility private
  def update_owner_and_excludes
    if @resource.value(:owner) || @resource.value(:group)
      set_ownership
    end
    set_excludes if @resource.value(:excludes)
  end

  # @!visibility private
  def git_with_identity(*args)
    if @resource.value(:identity)
      Tempfile.open('git-helper', Puppet[:statedir]) do |f|
        f.puts '#!/bin/sh'
        f.puts 'SSH_AUTH_SOCKET='
        f.puts 'export SSH_AUTH_SOCKET'
        f.puts 'exec ssh -oStrictHostKeyChecking=no -oPasswordAuthentication=no -oKbdInteractiveAuthentication=no ' \
               "-oChallengeResponseAuthentication=no -oConnectTimeout=120 -i #{@resource.value(:identity)} $*"
        f.close

        FileUtils.chmod(0o755, f.path)
        env_save = ENV['GIT_SSH']
        ENV['GIT_SSH'] = f.path

        ret = git(*args)

        ENV['GIT_SSH'] = env_save

        return ret
      end
    elsif @resource.value(:user) && @resource.value(:user) != Facter['id'].value
      env = Etc.getpwnam(@resource.value(:user))
      Puppet::Util::Execution.execute("git #{args.join(' ')}", uid: @resource.value(:user), failonfail: true, custom_environment: { 'HOME' => env['dir'] }, combine: true)
    else
      git(*args)
    end
  end
end
