require 'digest/md5'

Puppet::Type.newtype(:ini_setting) do
  ensurable do
    defaultvalues
    defaultto :present
  end

  def munge_boolean_md5(value)
    case value
    when true, :true, 'true', :yes, 'yes'
      :true
    when false, :false, 'false', :no, 'no'
      :false
    when :md5, 'md5'
      :md5
    else
      raise('expected a boolean value or :md5')
    end
  end

  newparam(:name, namevar: true) do
    desc 'An arbitrary name used as the identity of the resource.'
  end

  newparam(:section) do
    desc 'The name of the section in the ini file in which the setting should be defined.' \
         'If not provided, defaults to global, top of file, sections.'
    defaultto('')
  end

  newparam(:setting) do
    desc 'The name of the setting to be defined.'
    munge do |value|
      if value =~ %r{(^\s|\s$)}
        Puppet.warn('Settings should not have spaces in the value, we are going to strip the whitespace')
      end
      value.strip
    end
  end

  newparam(:path) do
    desc 'The ini file Puppet will ensure contains the specified setting.'
    validate do |value|
      unless Puppet::Util.absolute_path?(value)
        raise(Puppet::Error, "File paths must be fully qualified, not '#{value}'")
      end
    end
  end

  newparam(:show_diff) do
    desc 'Whether to display differences when the setting changes.'

    defaultto :true

    newvalues(:true, :md5, :false)

    munge do |value|
      @resource.munge_boolean_md5(value)
    end
  end

  newparam(:key_val_separator) do
    desc 'The separator string to use between each setting name and value. ' \
         'Defaults to " = ", but you could use this to override e.g. ": ", or' \
         'whether or not the separator should include whitespace.'
    defaultto(' = ')
  end

  newproperty(:value) do
    desc 'The value of the setting to be defined.'

    munge do |value|
      value.to_s
    end

    def should_to_s(newvalue)
      if @resource[:show_diff] == :true && Puppet[:show_diff]
        newvalue
      elsif @resource[:show_diff] == :md5 && Puppet[:show_diff]
        '{md5}' + Digest::MD5.hexdigest(newvalue.to_s)
      else
        '[redacted sensitive information]'
      end
    end

    def is_to_s(value) # rubocop:disable Style/PredicateName : Changing breaks the code (./.bundle/gems/gems/puppet-5.3.3-universal-darwin/lib/puppet/parameter.rb:525:in `to_s')
      should_to_s(value)
    end

    def insync?(current)
      if @resource[:refreshonly]
        true
      else
        current == should
      end
    end
  end

  newparam(:section_prefix) do
    desc 'The prefix to the section name\'s header.' \
         'Defaults to \'[\'.'
    defaultto('[')
  end

  newparam(:section_suffix) do
    desc 'The suffix to the section name\'s header.' \
         'Defaults to \']\'.'
    defaultto(']')
  end

  newparam(:indent_char) do
    desc 'The character to indent new settings with.' \
         'Defaults to \' \'.'
    defaultto(' ')
  end

  newparam(:indent_width) do
    desc 'The number of indent_chars to use to indent a new setting.' \
         'Defaults to undef (autodetect).'
  end

  newparam(:refreshonly) do
    desc 'A flag indicating whether or not the ini_setting should be updated ' \
         'only when called as part of a refresh event'
    defaultto false
    newvalues(true, false)
  end

  def refresh
    # update the value in the provider, which will save the value to the ini file
    provider.value = self[:value] if self[:refreshonly]
  end
end
