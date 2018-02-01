# Alternative Augeas-based provider for sysctl type
#
# Copyright (c) 2012 Dominic Cleal
# Licensed under the Apache License, Version 2.0

raise("Missing augeasproviders_core dependency") if Puppet::Type.type(:augeasprovider).nil?
Puppet::Type.type(:sysctl).provide(:augeas, :parent => Puppet::Type.type(:augeasprovider).provider(:default)) do
  desc "Uses Augeas API to update sysctl settings"

  default_file { '/etc/sysctl.conf' }

  lens { 'Sysctl.lns' }

  optional_commands :sysctl => 'sysctl'

  resource_path do |resource|
    "$target/#{resource[:name]}"
  end

  def self.sysctl_set(key, value, silent=false)
    begin
      if Facter.value(:kernel) == :openbsd
        sysctl("#{key}=#{value}")
      else
        sysctl('-w', %Q{#{key}=#{value}})
      end
    rescue Puppet::ExecutionFailure => e
      if silent
        debug("augeasprovider_sysctl ignoring failed attempt to set #{key} due to :silent mode")
      else
        raise e
      end
    end
  end

  def self.sysctl_get(key)
    sysctl('-n', key).chomp
  end

  confine :feature => :augeas

  def self.instances(reference_resource = nil)
    return @resource_cache if @resource_cache

    resources = nil

    augopen(reference_resource) do |aug|
      resources ||= []

      aug.match("$target/*").each do |spath|
        resource = {
          :ensure  => :present,
          :persist => :true
        }

        basename = spath.split("/")[-1]
        resource[:name] = basename.split("[")[0]
        next unless resource[:name]
        next if (resource[:name] == "#comment")

        resource[:value] = aug.get("#{spath}")

        # Only match comments immediately before the entry and prefixed with
        # the sysctl name
        cmtnode = aug.match("$target/#comment[following-sibling::*[1][self::#{basename}]]")
        unless cmtnode.empty?
          comment = aug.get(cmtnode[0])
          if comment.match(/#{resource[:name]}:/)
            resource[:comment] = comment.sub(/^#{resource[:name]}:\s*/, "")
          end
        end

        resources << resource
      end
    end

    # Grab everything else
    resources ||= []

    sysctl('-a').each_line do |line|
      value = line.split('=')

      key = value.shift.strip

      value = value.join('=').strip

      existing_index = resources.index{ |x| x[:name] == key }

      if existing_index
        resources[existing_index][:apply] = :true
      else
        resources << {
          :name    => key,
          :ensure  => :present,
          :value   => value,
          :apply   => :true,
          :persist => :false
        }
      end
    end

    if resources
      @resource_cache = resources.map{|x| x = new(x)}
      return @resource_cache
    end
  end

  def self.prefetch(resources)
    # We need to pass a reference resource so that the proper target is in
    # scope.
    instances(resources.first.last).each do |prov|
      if resource = resources[prov.name]
        resource.provider = prov
      end
    end
  end

  def create
    if resource[:persist] == :true
      if !valid_resource?(resource[:name]) && (resource[:silent] == :false)
        raise Puppet::Error, "Error: `#{resource[:name]}` is not a valid sysctl key"
      end

      # the value to pass to augeas can come either from the 'value' or the
      # 'val' type parameter.
      value = resource[:value] || resource[:val]

      augopen! do |aug|
        # Prefer to create the node next to a commented out entry
        commented = aug.match("$target/#comment[.=~regexp('#{resource[:name]}([^a-z\.].*)?')]")
        aug.insert(commented.first, resource[:name], false) unless commented.empty?
        aug.set(resource_path, value)
        setvars(aug)
      end
    end
  end

  def valid_resource?(name)
    @property_hash.is_a?(Hash) && !@property_hash.empty? && (@property_hash[:apply] == :true)
  end

  def exists?
    # If in silent mode, short circuit the process on an invalid key
    #
    # This only matters when creating entries since invalid missing entries
    # might be used to clean up /etc/sysctl.conf
    if resource[:ensure] != :absent
      if !valid_resource?(resource[:name])
        if resource[:silent] == :true
          debug("augeasproviders_sysctl: `#{resource[:name]}` is not a valid sysctl key")
          return true
        else
          raise Puppet::Error, "Error: `#{resource[:name]}` is not a valid sysctl key"
        end
      end
    end

    if @property_hash[:ensure] == :present
      # Short circuit this if there's nothing to do
      if (resource[:ensure] == :absent) && (@property_hash[:persist] == :false)
        return false
      else
        return true
      end
    else
      super
    end
  end


  define_aug_method!(:destroy) do |aug, resource|
    aug.rm("$target/#comment[following-sibling::*[1][self::#{resource[:name]}]][. =~ regexp('#{resource[:name]}:.*')]")
    aug.rm('$resource')
  end

  def live_value
    if resource[:silent] == :true
      debug("augeasproviders_sysctl not setting live value for #{resource[:name]} due to :silent mode")
      if resource[:value]
        return resource[:value]
      else
        return resource[:val]
      end
    else
      return self.class.sysctl_get(resource[:name])
    end
  end

  attr_aug_accessor(:value, :label => :resource)

  alias_method :val, :value
  alias_method :val=, :value=

  define_aug_method(:comment) do |aug, resource|
    comment = aug.get("$target/#comment[following-sibling::*[1][self::#{resource[:name]}]][. =~ regexp('#{resource[:name]}:.*')]")
    comment.sub!(/^#{resource[:name]}:\s*/, "") if comment
    comment || ""
  end

  define_aug_method!(:comment=) do |aug, resource, value|
    cmtnode = "$target/#comment[following-sibling::*[1][self::#{resource[:name]}]][. =~ regexp('#{resource[:name]}:.*')]"
    if value.empty?
      aug.rm(cmtnode)
    else
      if aug.match(cmtnode).empty?
        aug.insert('$resource', "#comment", true)
      end
      aug.set("$target/#comment[following-sibling::*[1][self::#{resource[:name]}]]",
              "#{resource[:name]}: #{resource[:comment]}")
    end
  end

  def flush
    if resource[:ensure] == :absent
      super
    else
      if resource[:apply] == :true
        value = resource[:value] || resource[:val]
        if value
          silent = (resource[:silent] == :true)
          self.class.sysctl_set(resource[:name], value, silent)
        end
      end

      # Ensures that we only save to disk when we're supposed to
      if resource[:persist] == :true
        # Create the entry on disk if it's not already there
        if @property_hash[:persist] == :false
          create
        end

        super
      end
    end
  end
end
