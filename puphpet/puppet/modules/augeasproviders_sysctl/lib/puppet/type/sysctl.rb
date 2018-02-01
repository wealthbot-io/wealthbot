# Manages entries in /etc/sysctl.conf
#
# Copyright (c) 2012 Dominic Cleal
# Licensed under the Apache License, Version 2.0

Puppet::Type.newtype(:sysctl) do
  @doc = "Manages entries in /etc/sysctl.conf."

  ensurable

  newparam(:name) do
    desc "The name of the setting, e.g. net.ipv4.ip_forward"
    isnamevar
  end

  module SysctlValueSync
    def insync?(is)
      _is_insync = true

      if provider.valid_resource?(resource[:name])
        if resource[:apply] == :true
          @live_value = provider.live_value

          _is_insync = equal(should, @live_value)
        end

        if _is_insync && (resource[:persist] == :true)
            _is_insync = equal(should, is)
        end
      else
        # We won't get here unless exists? has been short circuited so we can
        # rely on that to raise an approprite error.
        debug("augeasproviders_sysctl: skipping insync? due to invalid resource `#{resource[:name]}`")
      end

      return _is_insync
    end

    def change_to_s(current, new)
      if resource[:apply] == :true
        if equal(current, new)
          return "changed live value from '#{@live_value}' to '#{new}'"
        elsif equal(@live_value, new)
          return "changed configuration value from '#{current}' to '#{new}'"
        else
          if resource[:persist] == :true
            return "changed configuration value from '#{current}' to '#{new}' and live value from '#{@live_value}' to '#{new}'"
          else
            return "changed live value from '#{@live_value}' to '#{new}'"
          end
        end
      else
        return "changed configuration value from '#{current}' to '#{new}'"
      end
    end

    def equal(a, b)
      a && b && (a.gsub(/\s+/, ' ') == b.gsub(/\s+/, ' '))
    end
  end

  newproperty(:val) do
    desc "An alias for 'value'. Maintains interface compatibility with the traditional ParsedFile sysctl provider. If both are set, 'value' will take precedence over 'val'."

    munge do |val|
      val.to_s
    end

    include SysctlValueSync
  end

  newproperty(:value) do
    desc "Value to change the setting to. Settings with multiple values (such as net.ipv4.tcp_mem) are represented as a single whitespace separated string."

    munge do |val|
      val.to_s
    end

    include SysctlValueSync
  end

  newparam(:target) do
    desc "The file in which to store the settings, defaults to
      `/etc/sysctl.conf`."
  end

  newproperty(:comment) do
    desc "Text to be stored in a comment immediately above the entry.  It will be automatically prepended with the name of the setting in order for the provider to know whether it controls the comment or not."
  end

  newparam(:apply, :boolean => true) do
    desc "Whether to apply the value using the sysctl command."
    newvalues(:true, :false)
    defaultto(:true)
  end

  newparam(:persist, :boolean => true) do
    desc "Persist the value in the on-disk file ($target)."
    newvalues(:true, :false)
    defaultto(:true)
  end

  newparam(:silent, :boolean => true) do
    desc "If set, do not report an error if the system key does not exist. This is useful for systems that may need to load a kernel module prior to the sysctl values existing."
    newvalues(:true, :false)
    defaultto(:false)
  end

  autorequire(:file) do
    self[:target]
  end
end
