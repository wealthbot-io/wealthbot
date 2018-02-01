Puppet::Type.newtype(:concat_fragment) do
  @doc = "Create a concat fragment to be used by concat.
    the `concat_fragment` type creates a file fragment to be collected by concat based on the tag.
    The example is based on exported resources.

    Example:
    @@concat_fragment { \"uniqe_name_${::fqdn}\":
      tag => 'unique_name',
      order => 10, # Optional. Default to 10
      content => 'some content' # OR
      content => template('template.erb') # OR
      source  => 'puppet:///path/to/file'
    }
  "

  newparam(:name, namevar: true) do
    desc 'Unique name'
  end

  newparam(:target) do
    desc 'Target'

    validate do |value|
      raise ArgumentError, 'Target must be a String' unless value.is_a?(String)
    end
  end

  newparam(:content) do
    desc 'Content'

    validate do |value|
      raise ArgumentError, 'Content must be a String' unless value.is_a?(String)
    end
  end

  newparam(:source) do
    desc 'Source'

    validate do |value|
      raise ArgumentError, 'Content must be a String or Array' unless [String, Array].include?(value.class)
    end
  end

  newparam(:order) do
    desc 'Order'
    defaultto '10'
    validate do |val|
      raise Puppet::ParseError, '$order is not a string or integer.' unless val.is_a?(String) || val.is_a?(Integer)
      raise Puppet::ParseError, "Order cannot contain '/', ':', or '\n'." if val.to_s =~ %r{[:\n\/]}
    end
  end

  newparam(:tag) do
    desc 'Tag name to be used by concat to collect all concat_fragments by tag name'
  end

  autorequire(:file) do
    found = catalog.resources.select do |resource|
      next unless resource.is_a?(Puppet::Type.type(:concat_file))

      resource[:path] == self[:target] || resource.title == self[:target] ||
        (resource[:tag] && resource[:tag] == self[:tag])
    end

    if found.empty?
      tag_message = (self[:tag]) ? "or tag '#{self[:tag]} " : ''
      warning "Target Concat_file with path or title '#{self[:target]}' #{tag_message}not found in the catalog"
    end
  end

  validate do
    # Check if target is set
    raise Puppet::ParseError, "No 'target' or 'tag' set" unless self[:target] || self[:tag]

    # Check if either source or content is set. raise error if none is set
    raise Puppet::ParseError, "Set either 'source' or 'content'" if self[:source].nil? && self[:content].nil?

    # Check if both are set, if so rais error
    raise Puppet::ParseError, "Can't use 'source' and 'content' at the same time" if !self[:source].nil? && !self[:content].nil?
  end
end
