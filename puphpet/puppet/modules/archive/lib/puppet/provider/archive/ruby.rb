begin
  require 'puppet_x/bodeco/archive'
  require 'puppet_x/bodeco/util'
rescue LoadError
  require 'pathname' # WORK_AROUND #14073 and #7788
  archive = Puppet::Module.find('archive', Puppet[:environment].to_s)
  raise(LoadError, "Unable to find archive module in modulepath #{Puppet[:basemodulepath] || Puppet[:modulepath]}") unless archive
  require File.join archive.path, 'lib/puppet_x/bodeco/archive'
  require File.join archive.path, 'lib/puppet_x/bodeco/util'
end

require 'securerandom'
require 'tempfile'

# This provider implements a simple state-machine. The following attempts to #
# document it. In general, `def adjective?` implements a [state], while `def
# verb` implements an {action}.
# Some states are more complex, as they might depend on other states, or trigger
# actions. Since this implements an ad-hoc state-machine, many actions or states
# have to guard themselves against being called out of order.
#
# [exists?]
#   |
#   v
# [extracted?] -> no -> [checksum?]
#    |
#    v
#   yes
#    |
#    v
# [path.exists?] -> no -> {cleanup}
#    |                    |    |
#    v                    v    v
# [checksum?]            yes. [extracted?] && [cleanup?]
#                              |
#                              v
#                            {destroy}
#
# Now, with [exists?] defined, we can define [ensure]
# But that's just part of the standard puppet provider state-machine:
#
# [ensure] -> absent -> [exists?] -> no.
#   |                     |
#   v                     v
#  present               yes
#   |                     |
#   v                     v
# [exists?]            {destroy}
#   |
#   v
# {create}
#
# Here's how we would extend archive for an `ensure => latest`:
#
#  [exists?] -> no -> {create}
#    |
#    v
#   yes
#    |
#    v
#  [ttl?] -> expired -> {destroy} -> {create}
#    |
#    v
#  valid.
#

Puppet::Type.type(:archive).provide(:ruby) do
  optional_commands aws: 'aws'
  defaultfor feature: :microsoft_windows
  attr_reader :archive_checksum

  def exists?
    return checksum? unless extracted?
    return checksum? if File.exist? archive_filepath
    cleanup
    true
  end

  def create
    transfer_download(archive_filepath) unless checksum?
    extract
    cleanup
  end

  def destroy
    FileUtils.rm_f(archive_filepath) if File.exist?(archive_filepath)
  end

  def archive_filepath
    resource[:path]
  end

  def tempfile_name
    if resource[:checksum] == 'none'
      "#{resource[:filename]}_#{SecureRandom.base64}"
    else
      "#{resource[:filename]}_#{resource[:checksum]}"
    end
  end

  def creates
    if resource[:extract] == :true
      extracted? ? resource[:creates] : 'archive not extracted'
    else
      resource[:creates]
    end
  end

  def creates=(_value)
    extract
  end

  def checksum
    resource[:checksum] || (resource[:checksum] = remote_checksum if resource[:checksum_url])
  end

  def remote_checksum
    PuppetX::Bodeco::Util.content(
      resource[:checksum_url],
      username: resource[:username],
      password: resource[:password],
      cookie: resource[:cookie],
      proxy_server: resource[:proxy_server],
      proxy_type: resource[:proxy_type],
      insecure: resource[:allow_insecure]
    )[%r{\b[\da-f]{32,128}\b}i]
  end

  # Private: See if local archive checksum matches.
  # returns boolean
  def checksum?(store_checksum = true)
    return false unless File.exist? archive_filepath
    return true  if resource[:checksum_type] == :none

    archive = PuppetX::Bodeco::Archive.new(archive_filepath)
    archive_checksum = archive.checksum(resource[:checksum_type])
    @archive_checksum = archive_checksum if store_checksum
    checksum == archive_checksum
  end

  def cleanup
    return unless extracted? && resource[:cleanup] == :true
    Puppet.debug("Cleanup archive #{archive_filepath}")
    destroy
  end

  def extract
    return unless resource[:extract] == :true
    raise(ArgumentError, 'missing archive extract_path') unless resource[:extract_path]
    PuppetX::Bodeco::Archive.new(archive_filepath).extract(
      resource[:extract_path],
      custom_command: resource[:extract_command],
      options: resource[:extract_flags],
      uid: resource[:user],
      gid: resource[:group]
    )
  end

  def extracted?
    resource[:creates] && File.exist?(resource[:creates])
  end

  def transfer_download(archive_filepath)
    if resource[:temp_dir] && !File.directory?(resource[:temp_dir])
      raise Puppet::Error, "Temporary directory #{resource[:temp_dir]} doesn't exist"
    end
    tempfile = Tempfile.new(tempfile_name, resource[:temp_dir])

    temppath = tempfile.path
    tempfile.close!

    case resource[:source]
    when %r{^(puppet)}
      puppet_download(temppath)
    when %r{^(http|ftp)}
      download(temppath)
    when %r{^file}
      uri = URI(resource[:source])
      FileUtils.copy(Puppet::Util.uri_to_path(uri), temppath)
    when %r{^s3}
      s3_download(temppath)
    when nil
      raise(Puppet::Error, 'Unable to fetch archive, the source parameter is nil.')
    else
      raise(Puppet::Error, "Source file: #{resource[:source]} does not exists.") unless File.exist?(resource[:source])
      FileUtils.copy(resource[:source], temppath)
    end

    # conditionally verify checksum:
    if resource[:checksum_verify] == :true && resource[:checksum_type] != :none
      archive = PuppetX::Bodeco::Archive.new(temppath)
      actual_checksum = archive.checksum(resource[:checksum_type])
      if actual_checksum != checksum
        raise(Puppet::Error, "Download file checksum mismatch (expected: #{checksum} actual: #{actual_checksum})")
      end
    end

    move_file_in_place(temppath, archive_filepath)
  end

  def move_file_in_place(from, to)
    # Ensure to directory exists.
    FileUtils.mkdir_p(File.dirname(to))
    FileUtils.mv(from, to)
  end

  def download(filepath)
    PuppetX::Bodeco::Util.download(
      resource[:source],
      filepath,
      username: resource[:username],
      password: resource[:password],
      cookie: resource[:cookie],
      proxy_server: resource[:proxy_server],
      proxy_type: resource[:proxy_type],
      insecure: resource[:allow_insecure]
    )
  end

  def puppet_download(filepath)
    PuppetX::Bodeco::Util.puppet_download(
      resource[:source],
      filepath
    )
  end

  def s3_download(path)
    params = [
      's3',
      'cp',
      resource[:source],
      path
    ]
    params += resource[:download_options] if resource[:download_options]

    aws(params)
  end

  def optional_switch(value, option)
    if value
      option.map { |flags| flags % value }
    else
      []
    end
  end
end
