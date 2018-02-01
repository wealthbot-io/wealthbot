require 'augeas' if Puppet.features.augeas?

# Base Augeas provider
# Handles basics such as opening, accessing and saving changes via an Augeas
# handle, plus standard configuration from a Puppet resource (e.g. the `target`
# parameter).
#
# To use, include as parent provider:
#
#     Puppet::Type.type(:example).provide(
#       :augeas,
#       :parent => Puppet::Type.type(:augeasprovider).provider(:default)
#     ) do
#       # [..]
#     end
#
Puppet::Type.type(:augeasprovider).provide(:default) do
  # Class methods automatically added to a Puppet provider by including the
  # {AugeasProviders::Provider} mixin.

  # Returns the Augeas version used
  #
  # @return [String] Augeas version in use
  # @api public
  def self.aug_version
    @aug_version ||= Augeas.open(nil, nil, Augeas::NO_MODL_AUTOLOAD) { |aug| aug.get('/augeas/version') }
  end

  # Returns whether a feature is supported.
  #
  # The following features are currently supported:
  #
  # * `:regexpi`: whether Augeas supports an 'i' flag in regexp expressions
  # * `:post_resource_eval`: whether Puppet supports `post_resource_eval` hooks
  #
  # @param [Symbol] feature the feature to check
  # @return [Boolean] whether feature is supported
  # @api public
  def self.supported?(feature)
    case feature
    when :regexpi
      Puppet::Util::Package.versioncmp(aug_version, '1.0.0') >= 0
    when :post_resource_eval
      Puppet::Util::Package.versioncmp(Puppet.version, '3.4.0') >= 0
    else
      raise Puppet::Error, "Unknown feature '#{feature}'"
    end
  end

  # Returns a node label to use for creating a new entry in an Augeas sequence
  # (seq), given the current list, e.g. '1' if it's the first.  Supply
  # aug.match('$target/*') or similar.
  #
  # @param [Array<String>] existing paths, from Augeas#match
  # @return [String] new node label
  def self.next_seq(matches)
    last = matches.map {|p| path_label(nil, p).to_i }.max || 0
    (last + 1).to_s
  end

  # Returns an Augeas handler.
  #
  # On Puppet >= 3.4, stores and returns a shared Augeas handler
  # for all instances of the class
  #
  # @return [Augeas] Augeas shared Augeas handle
  # @api private
  def self.aug_handler
    if supported?(:post_resource_eval)
      @aug ||= Augeas.open(nil, loadpath, Augeas::NO_MODL_AUTOLOAD)
    else
      Augeas.open(nil, loadpath, Augeas::NO_MODL_AUTOLOAD)
    end
  end

  # Close the shared Augeas handler.
  #
  # @param [Augeas] aug open Augeas handle
  # @api public
  def self.augclose!(aug)
    aug.close
  end

  # Opens Augeas and returns a handle to use.  It loads only the file
  # identified by {#target} (and the supplied `resource`) using {#lens}.
  #
  # If called with a block, this will be yielded to and the Augeas handle
  # closed after the block has executed (on Puppet < 3.4.0).
  # Otherwise, the handle will be returned and not closed automatically.
  # On Puppet >= 3.4, the handle will be closed by `post_resource_eval`.
  # On older versions, the caller is responsible for closing it to free
  # resources.
  #
  # If `yield_resource` is set to true, the supplied `resource` will be passed
  # as a yieldparam to the block, after the `aug` handle. Any arguments passed
  # after `yield_resource` will be added as yieldparams to the block.
  #
  # @param [Puppet::Resource] resource resource being evaluated
  # @param [Boolean] yield_resource whether to send `resource` as a yieldparam
  # @param [Splat] yield_params a splat of parameters to pass as yieldparams if `yield_resource` is true
  # @return [Augeas] Augeas handle if no block is given
  # @yield [aug, resource, *yield_params] block that uses the Augeas handle
  # @yieldparam [Augeas] aug open Augeas handle
  # @yieldparam [Puppet::Resource] resource the supplied Puppet resource, passed if `yield_resource` is set to true
  # @yieldparam [Splat] *yield_params a splat of additional arguments sent to the block, if `yield_resource` is set to true
  # @raise [Puppet::Error] if Augeas did not load the file
  # @api public
  def self.augopen(resource = nil, yield_resource = false, *yield_params, &block)
    augopen_internal(resource, false, yield_resource, *yield_params, &block)
  end

  # Opens Augeas and returns a handle to use.  It loads only the file
  # for the current Puppet resource using {AugeasProviders::Provider::ClassMethods#lens}.
  # #augsave! is called after the block is evaluated.
  #
  # If called with a block, this will be yielded to and the Augeas handle
  # closed after the block has executed (on Puppet < 3.4.0).
  # Otherwise, the handle will be returned and not closed automatically.
  # On Puppet >= 3.4, the handle will be closed by `post_resource_eval`.
  # On older versions, the caller is responsible for closing it to free
  # resources.
  #
  # If `yield_resource` is set to true, the supplied `resource` will be passed
  # as a yieldparam to the block, after the `aug` handle. Any arguments passed
  # after `yield_resource` will be added as yieldparams to the block.
  #
  # @param [Puppet::Resource] resource resource being evaluated
  # @param [Boolean] yield_resource whether to send `resource` as a yieldparam
  # @param [Splat] yield_params a splat of parameters to pass as yieldparams if `yield_resource` is true
  # @return [Augeas] Augeas handle if no block is given
  # @yield [aug, resource, *yield_params] block that uses the Augeas handle
  # @yieldparam [Augeas] aug open Augeas handle
  # @yieldparam [Puppet::Resource] resource the supplied Puppet resource, passed if `yield_resource` is set to true
  # @yieldparam [Splat] *yield_params a splat of additional arguments sent to the block, if `yield_resource` is set to true
  # @raise [Puppet::Error] if Augeas did not load the file
  # @api public
  def self.augopen!(resource = nil, yield_resource = false, *yield_params, &block)
    augopen_internal(resource, true, yield_resource, *yield_params, &block)
  end

  # Saves all changes made in the current Augeas handle and checks for any
  # errors while doing so.
  # Reloads the tree afterwards to remove specific changes for next resource.
  #
  # @param [Augeas] aug open Augeas handle
  # @param [Boolean] reload whether to reload the tree after saving
  # @raise [Augeas::Error] if saving fails
  # @api public
  def self.augsave!(aug, reload = false)
    begin
      aug.save!
    rescue Augeas::Error
      errors = []
      aug.match("/augeas//error").each do |errnode|
        aug.match("#{errnode}/*").each do |subnode|
          subvalue = aug.get(subnode)
          errors << "#{subnode} = #{subvalue}"
        end
      end
      debug("Save failure details:\n" + errors.join("\n"))
      raise Augeas::Error, 'Failed to save Augeas tree to file. See debug logs for details.'
    ensure
      aug.load! if reload
    end
  end

  # Define a method with a block passed to #augopen
  #
  # @param [Symbol] method the name of the method to create
  # @yield [aug, resource, *args] block that uses the Augeas handle
  # @yieldparam [Augeas] aug open Augeas handle
  # @yieldparam [Puppet::Resource] resource the supplied Puppet resource
  # @yieldparam [Splat] *args a splat of additional arguments sent to the block
  # @api public
  def self.define_aug_method(method, &block)
    define_method(method) do |*args|
      # We are calling the resource's augopen here, not the class
      augopen(true, *args, &block)
    end
  end
 
  # Define a method with a block passed to #augopen!
  #
  # @param [Symbol] method the name of the method to create
  # @yield [aug, resource, *args] block that uses the Augeas handle
  # @yieldparam [Augeas] aug open Augeas handle
  # @yieldparam [Puppet::Resource] resource the supplied Puppet resource
  # @yieldparam [Splat] *args a splat of additional arguments sent to the block
  # @api public
  def self.define_aug_method!(method, &block)
    define_method(method) do |*args|
      # We are calling the resource's augopen! here, not the class
      augopen!(true, *args, &block)
    end
  end

  # Defines a property getter with a provided implementation.  It works from
  # a node identified with the given `label` beneath the resource.
  #
  # Supports three implementations based on the type specified:
  #
  # :string causes the getter to return the value of the node below
  # resource with the label given in opts
  #
  # :array causes the getter to return an array of values matching the label.
  # If sublabel is given, values of matching nodes beneath the
  # label node will be returned in an array.  If sublabel is :seq, values of
  # nodes matching a numbered seq will be returned.
  # 
  # :hash causes the getter to return a hash of the value of each matching
  # label node against the value of each sublabel node.
  #
  # @param [String] name the name of the property
  # @param [Hash] opts the options to create the setter
  # @option opts [String] label node label to match beneath resource, default is `name.to_s`. When the value is `:resource`, `$resource` will be used as the path to the node
  # @option opts [Symbol] type either :string, :array or :hash
  # @option opts [String] default default value for hash values if sublabel doesn't exist
  # @option opts [String] sublabel label of next node(s) beneath node label, used in array and hash values, or :seq for array values representing a numbered seq
  # @api public
  def self.attr_aug_reader(name, opts = {})
    label = opts[:label] || name.to_s
    default = opts[:default] || nil
    type = opts[:type] || :string
    sublabel = opts[:sublabel] || nil

    rpath = label == :resource ? '$resource' : "$resource/#{label}"

    if type == :hash and sublabel.nil?
      fail "You must provide a sublabel for type hash"
    end

    unless [:string, :array, :hash].include? type
      fail "Invalid type: #{type}"
    end

    # Class getter method using an existing aug handler
    # Emulate define_singleton_method for Ruby 1.8
    metaclass = class << self; self; end
    metaclass.send(:define_method, "attr_aug_reader_#{name}") do |aug, *args|
      case type
      when :string
        aug.get(rpath)
      when :array
        aug.match(rpath).map do |p|
          if sublabel.nil?
            aug.get(p)
          else
            if sublabel == :seq
              sp = "#{p}/*[label()=~regexp('[0-9]+')]"
            else
              sp = "#{p}/#{sublabel}"
            end
            aug.match(sp).map { |spp| aug.get(spp) }
          end
        end.flatten
      when :hash
        values = {}
        aug.match(rpath).each do |p|
          sp = "#{p}/#{sublabel}"
          values[aug.get(p)] = aug.get(sp) || default
        end
        values
      end
    end

    # Instance getter method for the instance
    define_method("attr_aug_reader_#{name}") do |aug, *args|
      self.class.send("attr_aug_reader_#{name}", aug, *args)
    end

    # We are calling the resource's augopen here, not the class
    define_method(name) do |*args|
      augopen do |aug|
        self.send("attr_aug_reader_#{name}", aug, *args)
      end
    end
  end

  # Defines a property setter using #augopen
  #
  # @param [String] name the name of the property
  # @param [Hash] opts the options to create the setter
  # @option opts [String] label node label to match beneath resource, default is `name.to_s`. When the value is `:resource`, `$resource` will be used as the path to the node
  # @option opts [Symbol] type either :string, :array or :hash
  # @option opts [String] default default value for hash values if sublabel doesn't exist
  # @option opts [String] sublabel label of next node(s) beneath node label, used in array and hash values, or :seq for array values representing a numbered seq
  # @option opts [Boolean] purge_ident whether to purge other matches (keeps the last one only)
  # @option opts [Boolean] rm_node whether setting a string value to `nil` removes the node (default is to clear its value)
  # @api public
  def self.attr_aug_writer(name, opts = {})
    label = opts[:label] || name.to_s
    default = opts[:default] || nil
    type = opts[:type] || :string
    sublabel = opts[:sublabel] || nil
    purge_ident = opts[:purge_ident] || false
    rm_node = opts[:rm_node] || false

    rpath = label == :resource ? '$resource' : "$resource/#{label}"

    if type == :hash and sublabel.nil?
      fail "You must provide a sublabel for type hash"
    end

    unless [:string, :array, :hash].include? type
      fail "Invalid type: #{type}"
    end

    # Class setter method using an existing aug handler
    # Emulate define_singleton_method for Ruby 1.8
    metaclass = class << self; self; end
    metaclass.send(:define_method, "attr_aug_writer_#{name}") do |aug, *args|
      aug.rm("#{rpath}[position() != 1]") if purge_ident
      case type
      when :string
        if args[0]
          aug.set(rpath, args[0].to_s)
        elsif rm_node
          aug.rm(rpath)
        else
          aug.clear(rpath)
        end
      when :array
        if args[0].nil?
          aug.rm(rpath)
        else
          if sublabel.nil?
            aug.rm(rpath)
            count = 0
            args[0].each do |v|
              count += 1
              aug.set("#{rpath}[#{count}]", v)
            end
          elsif sublabel == :seq
            # Make sure only our values are used
            aug.rm("#{rpath}/*[label()=~regexp('[0-9]+')]")
            count = 0
            args[0].each do |v|
              count += 1
              aug.set("#{rpath}/#{count}", v)
            end
          else
            # Make sure only our values are used
            aug.rm("#{rpath}/#{sublabel}")
            count = 0
            args[0].each do |v|
              count += 1
              aug.set("#{rpath}/#{sublabel}[#{count}]", v)
            end
          end
        end
      when :hash
        # First get rid of all entries
        aug.rm(rpath)
        args[0].each do |k, v|
          aug.set("#{rpath}[.='#{k}']", k)
          unless v == default
            aug.set("#{rpath}[.='#{k}']/#{sublabel}", v)
          end
        end
      end
    end

    # Instance setter method for the instance
    define_method("attr_aug_writer_#{name}") do |aug, *args|
      self.class.send("attr_aug_writer_#{name}", aug, *args)
    end

    # We are calling the resource's augopen here, not the class
    define_method("#{name}=") do |*args|
      augopen! do |aug|
        self.send("attr_aug_writer_#{name}", aug, *args)
      end
    end
  end

  # Define getter and setter for a property
  #
  # @param [Symbol] name the name of the property
  # @param [Hash] opts the options to create the setter
  # @option opts [String] label node label to match beneath resource, default is `name.to_s`. When the value is `:resource`, `$resource` will be used as the path to the node
  # @option opts [Symbol] type either :string, :array or :hash
  # @option opts [String] default default value for hash values if sublabel doesn't exist
  # @option opts [String] sublabel label of next node(s) beneath node label, used in array and hash values, or :seq for array values representing a numbered seq
  # @option opts [Boolean] purge_ident whether to purge other matches (keeps the last one only)
  # @api public
  def self.attr_aug_accessor(name, opts = {})
    attr_aug_reader(name, opts)
    attr_aug_writer(name, opts)
  end

  # Setter for the default file path managed by the provider.
  #
  # Takes a block to store, but doesn't yield.  Will be called when it's
  # needed.
  #
  # @yield block that identifies the default file path managed by the provider
  # @yieldreturn [String] default file path
  # @api public
  def self.default_file(&block)
    @default_file_block = block
  end

  # Getter and setter for the Augeas lens used for this provider.
  #
  # When called with a block, will only store the block - it doesn't yield.
  #
  # When called without a block, expects `resource` parameter which is
  # passed into the block, which returns the lens to be used.
  #
  # @param resource [Puppet::Resource] required for getter, resource being evaluated
  # @yield [resource] block that identifies the lens to use
  # @yieldparam [Puppet::Resource] resource resource being evaluted
  # @yieldreturn [String] Augeas lens to use, e.g. `'Hosts.lns'`
  # @return [String] Augeas lens to use, e.g. `'Hosts.lns'`
  # @raise [Puppet::Error] if no block has been set when getting
  # @api public
  def self.lens(resource = nil, &block)
    if block_given?
      @lens_block = block
    else
      fail 'Lens is not provided' unless @lens_block
      @lens_block.call(resource)
    end
  end

  # Wrapper around aug.label for older versions of Augeas
  # and values not found in the tree.
  #
  # @param [Augeas] aug Augeas handler
  # @param [String] path expression to get the label from
  # @return [String] label of the given path
  # @api public
  def self.path_label(aug, path)
    if aug.respond_to? :label
      label = aug.label(path)
    end

    # Fallback
    label || path.split("/")[-1].split("[")[0]
  end

  # Determine which quote is needed
  #
  # @param [String] value the value to quote
  # @param [String] oldvalue the optional old value, used to auto-detect existing quoting
  # @return [String] the quoted value
  # @api public
  def self.whichquote(value, resource = nil, oldvalue = nil)
    oldquote = readquote oldvalue
 
    if resource and resource.parameters.include? :quoted
      quote = resource[:quoted]
    else
      quote = :auto
    end
 
    if quote == :auto
      quote = if oldquote
        oldquote
      elsif value =~ /[|&;()<>\s]/
        :double
      else
        :none
      end
    end

    case quote
    when :double
      '"'
    when :single
      "'"
    else
      ''
    end
  end

  # Automatically quote a value
  #
  # @param [String] value the value to quote
  # @param [String] oldvalue the optional old value, used to auto-detect existing quoting
  # @return [String] the quoted value
  # @api public
  def self.quoteit(value, resource = nil, oldvalue = nil)
    quote = whichquote(value, resource, oldvalue)
    "#{quote}#{value}#{quote}"
  end
 
  # Detect what type of quoting a value uses
  #
  # @param [String] value the value to be analyzed
  # @return [Symbol] the type of quoting used (:double, :single or nil)
  # @api public
  def self.readquote(value)
    if value =~ /^(["'])(.*)(?:\1)$/
      case $1
      when '"' then :double
      when "'" then :single
      else nil end
    else
      nil
    end
  end

  # Getter and setter for the Augeas path expression representing an
  # individual resource inside a file, that's managed by this provider.
  #
  # When called with a block, will only store the block - it doesn't yield.
  # The block is later used to generate the path expression.
  #
  # When called without a block, expects `resource` parameter which is
  # passed into the block, which returns the path expression representing
  # the supplied resource.
  #
  # If no block has already been set, it returns the path expression
  # representing the top-level of the file.
  #
  # @param resource [Puppet::Resource] required for getter, resource being evaluated
  # @yield [resource] block that identifies the path expression
  # @yieldparam [Puppet::Resource] resource resource being evaluted
  # @yieldreturn [String] Augeas path expression, e.g. `'/files/etc/hosts/1'`
  # @return [String] Augeas path expression to use, e.g. `'/files/etc/hosts/1'`
  # @raise [Puppet::Error] if no default file block is set and no resource is passed
  # @see AugeasProviders::Provider#resource_path
  # @see #target
  # @api public
  def self.resource_path(resource = nil, &block)
    if block_given?
      @resource_path_block = block
    else
      if @resource_path_block
        path = "/files#{target(resource)}"
        @resource_path_block.call(resource)
      else
        "#{target(resource)}/#{resource[:name]}"
      end
    end
  end

  # Sets useful Augeas variables for the session.
  #
  # * `$target` points to the root of the target file
  # * `$resource` points to path defined by #resource_path
  #
  # It also sets `/augeas/context` to the target file so
  # relative paths can be used, before the variables are set.
  #
  # If supplied with a resource, it will be used to determine the
  # path to the used file.
  #
  # @param [Augeas] aug Augeas handle
  # @param [Puppet::Resource] resource resource being evaluated
  # @see #resource_path
  # @api public
  def self.setvars(aug, resource = nil)
    aug.set('/augeas/context', "/files#{target(resource)}")
    aug.defnode('target', "/files#{target(resource)}", nil)
    aug.defvar('resource', resource_path(resource)) if resource
  end

  # Gets the path expression representing the file being managed.
  #
  # If supplied with a resource, this will represent the file identified by
  # the resource, else the default file that the provider manages. 
  #
  # @param [Puppet::Resource] resource resource being evaluated
  # @return [String] path expression representing the file being managed
  # @raise [Puppet::Error] if no default block is set and no resource is passed
  # @see AugeasProviders::Provider#target
  # @see #resource_path
  # @api public
  def self.target(resource = nil)
    file = @default_file_block.call if @default_file_block
    file = resource[:target] if resource and resource[:target]
    fail 'No target file given' if file.nil?
    file.chomp('/')
  end

  # Automatically unquote a value
  # 
  # @param [String] value the value to unquote
  # @return [String] the unquoted value
  # @api public
  def self.unquoteit(value)
    if value =~ /^(["'])(.*)(?:\1)$/
      $2
    else
      value
    end
  end

  # Returns whether text is parsed as path using lens
  #
  # @param [String] text the text to test
  # @param [String] path the relative path to test
  # @param [String] lens the lens to use for parsing
  # @return [Boolean] whether the path was found when parsing text with lens
  # @api public
  def self.parsed_as?(text, path, lens)
    Augeas.open(nil, nil, Augeas::NO_MODL_AUTOLOAD) do |aug|
      if aug.respond_to? :text_store
        aug.set('/input', text)
        if aug.text_store(lens, '/input', '/parsed')
          return aug.match("/parsed/#{path}").any?
        end
      else
        # ruby-augeas < 0.5 doesn't support text_store
        Tempfile.open('aug_text_store') do |tmpfile|
          tmpfile.write(text)
          tmpfile.flush
          aug.transform(
            :lens => lens,
            :name => 'Text_store',
            :incl => tmpfile.path.to_s,
            :excl => []
          )
          aug.load!
          return aug.match("/files#{tmpfile.path.to_s}/#{path}").any?
        end
      end
    end
    return false
  end

  # Sets the post_resource_eval class hook for Puppet
  # This is only used with Puppet > 3.4.0    
  # and allows to clean the shared Augeas handler.
  def self.post_resource_eval
    augclose!(aug_handler)
    @aug = nil
  end

  # Returns a set of load paths to use when initialising Augeas.
  #
  # @return [String] colon-separated string of module paths, or nil if defaults are to be used
  def self.loadpath
    loadpath = nil
    plugins = File.join(Puppet[:libdir], 'augeas', 'lenses')
    if File.exists?(plugins)
      loadpath = loadpath.to_s.split(File::PATH_SEPARATOR).push(plugins).join(File::PATH_SEPARATOR)
    end
    loadpath
  end

  # Opens Augeas and returns a handle to use.  It loads only the file
  # identified by {#target} (and the supplied `resource`) using {#lens}.
  #
  # If called with a block, this will be yielded to and the Augeas handle
  # closed after the block has executed (on Puppet < 3.4.0).
  # Otherwise, the handle will be returned and not closed automatically.
  # On Puppet >= 3.4, the handle will be closed by `post_resource_eval`.
  # On older versions, the caller is responsible for closing it to free
  # resources.
  #
  # If `yield_resource` is set to true, the supplied `resource` will be passed
  # as a yieldparam to the block, after the `aug` handle. Any arguments passed
  # after `yield_resource` will be added as yieldparams to the block.
  #
  # @param [Puppet::Resource] resource resource being evaluated
  # @param [Boolean] autosave whether to call augsave! automatically after the block evaluation
  # @param [Boolean] yield_resource whether to send `resource` as a yieldparam
  # @param [Splat] yield_params a splat of parameters to pass as yieldparams if `yield_resource` is true
  # @return [Augeas] Augeas handle if no block is given
  # @yield [aug, resource, *yield_params] block that uses the Augeas handle
  # @yieldparam [Augeas] aug open Augeas handle
  # @yieldparam [Puppet::Resource] resource the supplied Puppet resource, passed if `yield_resource` is set to true
  # @yieldparam [Splat] *yield_params a splat of additional arguments sent to the block, if `yield_resource` is set to true
  # @raise [Puppet::Error] if Augeas did not load the file
  # @api private
  def self.augopen_internal(resource = nil, autosave = false, yield_resource = false, *yield_params, &block)
    aug = aug_handler
    file = target(resource)
    begin
      lens_name = lens[/[^\.]+/]
      if aug.match("/augeas/load/#{lens_name}").empty?
        aug.transform(
          :lens => lens,
          :name => lens_name,
          :incl => file,
          :excl => []
        )
        aug.load!
      elsif aug.match("/augeas/load/#{lens_name}/incl[.='#{file}']").empty?
        # Only add missing file
        aug.set("/augeas/load/#{lens_name}/incl[.='#{file}']", file)
        aug.load!
      end

      if File.exist?(file) && aug.match("/files#{file}").empty?
        message = aug.get("/augeas/files#{file}/error/message")
        unless aug.match("/augeas/files#{file}/error/pos").empty?
          line = aug.get("/augeas/files#{file}/error/line")
          char = aug.get("/augeas/files#{file}/error/char")
          message += " (line:#{line}, character:#{char})"
        end
        from = loadpath.nil? ? '' : " from #{loadpath}"
        fail("Augeas didn't load #{file} with #{lens}#{from}: #{message}")
      end

      if block_given?
        setvars(aug, resource)
        if yield_resource
          block.call(aug, resource, *yield_params)
        else
          block.call(aug)
        end
      else
        aug
      end
    rescue
      autosave = false
      raise
    ensure
      if aug && block_given? && !supported?(:post_resource_eval)
        augsave!(aug) if autosave
        augclose!(aug)
      end
    end
  end

  # Returns the Augeas version used
  #
  # @return [String] Augeas version in use
  # @api public
  def aug_version
    self.class.aug_version
  end

  # Returns whether a feature is supported.
  #
  # The following features are currently supported:
  #
  # * `:regexpi`: whether Augeas supports an 'i' flag in regexp expressions
  # * `:post_resource_eval`: whether Puppet supports `post_resource_eval` hooks
  #
  # @param [Symbol] feature the feature to check
  # @return [Boolean] whether feature is supported
  # @api public
  def supported?(feature)
    self.class.supported?(feature)
  end

  # Opens Augeas and returns a handle to use.  It loads only the file
  # for the current Puppet resource using {AugeasProviders::Provider::ClassMethods#lens}.
  #
  # If called with a block, this will be yielded to and the Augeas handle
  # closed after the block has executed (on Puppet < 3.4.0).
  # Otherwise, the handle will be returned and not closed automatically.
  # On Puppet >= 3.4, the handle will be closed by `post_resource_eval`.
  # On older versions, the caller is responsible for closing it to free
  # resources.
  #
  # If `yield_resource` is set to true, the supplied `resource` will be passed
  # as a yieldparam to the block, after the `aug` handle. Any arguments passed
  # after `yield_resource` will be added as yieldparams to the block.
  #
  # @return [Augeas] Augeas handle if no block is given
  # @yield [aug, resource, *yield_params] block that uses the Augeas handle
  # @yieldparam [Augeas] aug open Augeas handle
  # @yieldparam [Puppet::Resource] resource the supplied Puppet resource, passed if `yield_resource` is set to true
  # @yieldparam [Splat] *yield_params a splat of additional arguments sent to the block, if `yield_resource` is set to true
  # @raise [Puppet::Error] if Augeas did not load the file
  # @api public
  def augopen(yield_resource = false, *yield_params, &block)
    self.class.augopen(self.resource, yield_resource, *yield_params, &block)
  end

  # Opens Augeas and returns a handle to use.  It loads only the file
  # for the current Puppet resource using {AugeasProviders::Provider::ClassMethods#lens}.
  # #augsave! is called after the block is evaluated.
  #
  # If called with a block, this will be yielded to and the Augeas handle
  # closed after the block has executed (on Puppet < 3.4.0).
  # Otherwise, the handle will be returned and not closed automatically.
  # On Puppet >= 3.4, the handle will be closed by `post_resource_eval`.
  # On older versions, the caller is responsible for closing it to free
  # resources.
  #
  # @return [Augeas] Augeas handle if no block is given
  # @yield [aug, resource, *yield_params] block that uses the Augeas handle
  # @yieldparam [Augeas] aug open Augeas handle
  # @yieldparam [Puppet::Resource] resource the supplied Puppet resource, passed if `yield_resource` is set to true
  # @yieldparam [Splat] *yield_params a splat of additional arguments sent to the block, if `yield_resource` is set to true
  # @raise [Puppet::Error] if Augeas did not load the file
  # @api public
  def augopen!(yield_resource = false, *yield_params, &block)
    self.class.augopen!(self.resource, yield_resource, *yield_params, &block)
  end

  # Saves all changes made in the current Augeas handle and checks for any
  # errors while doing so.
  #
  # @param [Augeas] aug open Augeas handle
  # @param [Boolean] reload whether to reload the tree after saving
  # @raise [Augeas::Error] if saving fails
  # @api public
  def augsave!(aug, reload = false)
    self.class.augsave!(aug, reload)
  end

  # Close the shared Augeas handler.
  #
  # @param [Augeas] aug open Augeas handle
  # @api public
  def augclose!(aug)
    self.class.augclose!(aug)
  end

  # Returns an Augeas handler.
  #
  # On Puppet >= 3.4, stores and returns a shared Augeas handler
  # for all instances of the class
  #
  # @return [Augeas] Augeas shared Augeas handle
  # @api private
  def aug_handler
    self.class.aug_handler
  end

  # Wrapper around Augeas#label for older versions of Augeas
  #
  # @param [Augeas] aug Augeas handler
  # @param [String] path expression to get the label from
  # @return [String] label of the given path
  # @api public
  def path_label(aug, path)
    self.class.path_label(aug, path)
  end

  # Determine which quote is needed
  #
  # @param [String] value the value to quote
  # @param [String] oldvalue the optional old value, used to auto-detect existing quoting
  # @return [String] the quoted value
  # @api public
  def whichquote(value, oldvalue = nil)
    self.class.whichquote(value, self.resource, oldvalue)
  end

  # Automatically quote a value
  #
  # @param [String] value the value to quote
  # @param [String] oldvalue the optional old value, used to auto-detect existing quoting
  # @return [String] the quoted value
  # @api public
  def quoteit(value, oldvalue = nil)
    self.class.quoteit(value, self.resource, oldvalue)
  end

  # Detect what type of quoting a value uses
  #
  # @param [String] value the value to be analyzed
  # @return [Symbol] the type of quoting used (:double, :single or nil)
  # @api public
  def readquote(value)
    self.class.readquote(value)
  end

  # Gets the Augeas path expression representing the individual resource inside
  # the file, that represents the current Puppet resource.
  #
  # If no block was set by the provider's class method, it returns the path
  # expression representing the top-level of the file.
  #
  # @return [String] Augeas path expression to use, e.g. `'/files/etc/hosts/1'`
  # @see AugeasProviders::Provider::ClassMethods#resource_path
  # @see #target
  # @api public
  def resource_path
    self.class.resource_path(self.resource)
  end

  # Sets useful Augeas variables for the session:
  #
  # * `$target` points to the root of the target file
  # * `$resource` points to path defined by #resource_path
  #
  # It also sets `/augeas/context` to the target file so
  # relative paths can be used, before the variables are set.
  #
  # If supplied with a resource, it will be used to determine the
  # path to the used file.
  #
  # @param [Augeas] aug Augeas handle
  # @see #resource_path
  # @api public
  def setvars(aug)
    self.class.setvars(aug, self.resource)
  end

  # Gets the path expression representing the file being managed for the
  # current Puppet resource.
  #
  # @return [String] path expression representing the file being managed
  # @see AugeasProviders::Provider::ClassMethods#target
  # @see #resource_path
  # @api public
  def target
    self.class.target(self.resource)
  end

  # Automatically unquote a value
  # 
  # @param [String] value the value to unquote
  # @return [String] the unquoted value
  # @api public
  def unquoteit(value)
    self.class.unquoteit(value)
  end

  # Returns whether text is parsed as path using lens
  #
  # @param [String] text the text to test
  # @param [String] path the relative path to test
  # @param [String] lens the lens to use for parsing
  # @return [Boolean] whether the path was found when parsing text with lens
  # @api public
  def parsed_as?(text, path, lens = nil)
    lens ||= self.class.lens(self.resource)
    self.class.parsed_as?(text, path, lens)
  end

  # Default method to determine the existence of a resource
  # can be overridden if necessary
  def exists?
    augopen do |aug|
      not aug.match('$resource').empty?
    end
  end

  # Default method to destroy a resource
  # can be overridden if necessary
  def destroy
    augopen! do |aug|
      aug.rm('$resource')
    end
  end

  # Default method to flush a resource
  #
  # On Puppet >= 3.4, this takes care of
  # saving the tree for the shared Augeas handler.
  #
  # This method can be overridden in your provider
  # but you should make sure to call `super`
  # to ensure that the tree will be saved in Puppet >= 3.4
  def flush
    augsave!(aug_handler, true) if supported?(:post_resource_eval)
  end

  # Returns a node label to use for creating a new entry in an Augeas sequence
  # (seq), given the current list, e.g. '1' if it's the first.  Supply
  # aug.match('$target/*') or similar.
  #
  # @param [Array<String>] existing paths, from Augeas#match
  # @return [String] new node label
  def next_seq(matches)
    self.class.next_seq(matches)
  end
end
