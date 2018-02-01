require File.join(File.dirname(__FILE__), '..', 'vcsrepo')

Puppet::Type.type(:vcsrepo).provide(:bzr, parent: Puppet::Provider::Vcsrepo) do
  desc 'Supports Bazaar repositories'

  commands bzr: 'bzr'
  has_features :reference_tracking

  def create
    check_force
    if !@resource.value(:source)
      create_repository(@resource.value(:path))
    else
      clone_repository(@resource.value(:revision))
    end
  end

  def working_copy_exists?
    return false unless File.directory?(@resource.value(:path))
    begin
      bzr('status', @resource.value(:path))
      return true
    rescue Puppet::ExecutionFailure
      return false
    end
  end

  def exists?
    working_copy_exists?
  end

  def destroy
    FileUtils.rm_rf(@resource.value(:path))
  end

  def revision
    at_path do
      current_revid = bzr('version-info')[%r{^revision-id:\s+(\S+)}, 1]
      desired = @resource.value(:revision)
      begin
        desired_revid = bzr('revision-info', desired).strip.split(%r{\s+}).last
      rescue Puppet::ExecutionFailure
        # Possible revid available during update (but definitely not current)
        desired_revid = nil
      end
      if current_revid == desired_revid
        desired
      else
        current_revid
      end
    end
  end

  def revision=(desired)
    at_path do
      begin
        bzr('update', '-r', desired)
      rescue Puppet::ExecutionFailure
        bzr('update', '-r', desired, ':parent')
      end
    end
    update_owner
  end

  def source
    at_path do
      bzr('info')[%r{^\s+parent branch:\s+(\S+?)$}m, 1]
    end
  end

  def source=(_desired)
    create # recreate
  end

  def latest
    at_path do
      bzr('version-info', ':parent')[%r{^revision-id:\s+(\S+)}, 1]
    end
  end

  def latest?
    at_path do
      return revision == latest
    end
  end

  private

  def create_repository(path)
    bzr('init', path)
    update_owner
  end

  def clone_repository(revision)
    args = ['branch']
    if revision
      args.push('-r', revision)
    end
    args.push(@resource.value(:source),
              @resource.value(:path))
    bzr(*args)
    update_owner
  end

  def update_owner
    set_ownership if @resource.value(:owner) || @resource.value(:group)
  end
end
