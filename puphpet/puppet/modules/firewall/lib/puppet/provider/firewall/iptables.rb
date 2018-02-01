require 'puppet/provider/firewall'
require 'digest/md5'

Puppet::Type.type(:firewall).provide :iptables, parent: Puppet::Provider::Firewall do
  include Puppet::Util::Firewall

  @doc = 'Iptables type provider'

  has_feature :iptables
  has_feature :connection_limiting
  has_feature :rate_limiting
  has_feature :recent_limiting
  has_feature :snat
  has_feature :dnat
  has_feature :netmap
  has_feature :interface_match
  has_feature :icmp_match
  has_feature :owner
  has_feature :state_match
  has_feature :reject_type
  has_feature :log_level
  has_feature :log_prefix
  has_feature :log_uid
  has_feature :mark
  has_feature :mss
  has_feature :nflog_group
  has_feature :nflog_prefix
  has_feature :nflog_range
  has_feature :nflog_threshold
  has_feature :tcp_flags
  has_feature :pkttype
  has_feature :isfragment
  has_feature :socket
  has_feature :address_type
  has_feature :iprange
  has_feature :ipsec_dir
  has_feature :ipsec_policy
  has_feature :mask
  has_feature :ipset
  has_feature :clusterip
  has_feature :length
  has_feature :string_matching
  has_feature :queue_num
  has_feature :queue_bypass

  optional_commands(iptables: 'iptables',
                    iptables_save: 'iptables-save')

  defaultfor kernel: :linux
  confine kernel: :linux

  iptables_version = Facter.value('iptables_version')
  mark_flag = if iptables_version && Puppet::Util::Package.versioncmp(iptables_version, '1.4.1') < 0
                '--set-mark'
              else
                '--set-xmark'
              end

  @protocol = 'IPv4'

  @resource_map = {
    burst: '--limit-burst',
    checksum_fill: '--checksum-fill',
    clamp_mss_to_pmtu: '--clamp-mss-to-pmtu',
    connlimit_above: '-m connlimit --connlimit-above',
    connlimit_mask: '--connlimit-mask',
    connmark: '-m connmark --mark',
    ctstate: '-m conntrack --ctstate',
    destination: '-d',
    dport: ['-m multiport --dports', '--dport'],
    dst_range: '--dst-range',
    dst_type: '--dst-type',
    gateway: '--gateway',
    gid: '--gid-owner',
    icmp: '-m icmp --icmp-type',
    iniface: '-i',
    ipsec_dir: '-m policy --dir',
    ipsec_policy: '--pol',
    ipset: '-m set --match-set',
    isfragment: '-f',
    jump: '-j',
    goto: '-g',
    length: '-m length --length',
    limit: '-m limit --limit',
    log_level: '--log-level',
    log_prefix: '--log-prefix',
    log_uid: '--log-uid',
    mac_source: ['-m mac --mac-source', '--mac-source'],
    mask: '--mask',
    match_mark: '-m mark --mark',
    mss: '-m tcpmss --mss',
    name: '-m comment --comment',
    nflog_group: '--nflog-group',
    nflog_prefix: '--nflog-prefix',
    nflog_range: '--nflog-range',
    nflog_threshold: '--nflog-threshold',
    outiface: '-o',
    pkttype: '-m pkttype --pkt-type',
    port: '-m multiport --ports',
    proto: '-p',
    queue_num: '--queue-num',
    queue_bypass: '--queue-bypass',
    random: '--random',
    rdest: '--rdest',
    reap: '--reap',
    recent: '-m recent',
    reject: '--reject-with',
    rhitcount: '--hitcount',
    rname: '--name',
    rseconds: '--seconds',
    rsource: '--rsource',
    rttl: '--rttl',
    set_dscp: '--set-dscp',
    set_dscp_class: '--set-dscp-class',
    set_mark: mark_flag,
    set_mss: '--set-mss',
    socket: '-m socket',
    source: '-s',
    sport: ['-m multiport --sports', '--sport'],
    src_range: '--src-range',
    src_type: '--src-type',
    stat_every: '--every',
    stat_mode: '-m statistic --mode',
    stat_packet: '--packet',
    stat_probability: '--probability',
    state: '-m state --state',
    string: '-m string --string',
    string_algo: '--algo',
    string_from: '--from',
    string_to: '--to',
    table: '-t',
    tcp_flags: '-m tcp --tcp-flags',
    todest: '--to-destination',
    toports: '--to-ports',
    tosource: '--to-source',
    to: '--to',
    uid: '--uid-owner',
    physdev_in: '--physdev-in',
    physdev_out: '--physdev-out',
    physdev_is_bridged: '--physdev-is-bridged',
    physdev_is_in: '--physdev-is-in',
    physdev_is_out: '--physdev-is-out',
    date_start: '--datestart',
    date_stop: '--datestop',
    time_start: '--timestart',
    time_stop: '--timestop',
    month_days: '--monthdays',
    week_days: '--weekdays',
    time_contiguous: '--contiguous',
    kernel_timezone: '--kerneltz',
    clusterip_new: '--new',
    clusterip_hashmode: '--hashmode',
    clusterip_clustermac: '--clustermac',
    clusterip_total_nodes: '--total-nodes',
    clusterip_local_node: '--local-node',
    clusterip_hash_init: '--hash-init',
    src_cc: '--source-country',
    dst_cc: '--destination-country',
    hashlimit_name: '--hashlimit-name',
    hashlimit_upto: '--hashlimit-upto',
    hashlimit_above: '--hashlimit-above',
    hashlimit_burst: '--hashlimit-burst',
    hashlimit_mode: '--hashlimit-mode',
    hashlimit_srcmask: '--hashlimit-srcmask',
    hashlimit_dstmask: '--hashlimit-dstmask',
    hashlimit_htable_size: '--hashlimit-htable-size',
    hashlimit_htable_max: '--hashlimit-htable-max',
    hashlimit_htable_expire: '--hashlimit-htable-expire',
    hashlimit_htable_gcinterval: '--hashlimit-htable-gcinterval',
  }

  # These are known booleans that do not take a value, but we want to munge
  # to true if they exist.
  @known_booleans = [
    :checksum_fill,
    :clamp_mss_to_pmtu,
    :isfragment,
    :log_uid,
    :random,
    :rdest,
    :reap,
    :rsource,
    :rttl,
    :socket,
    :physdev_is_bridged,
    :physdev_is_in,
    :physdev_is_out,
    :time_contiguous,
    :kernel_timezone,
    :clusterip_new,
    :queue_bypass,
  ]

  # Properties that use "-m <ipt module name>" (with the potential to have multiple
  # arguments against the same IPT module) must be in this hash. The keys in this
  # hash are the IPT module names, with the values being an array of the respective
  # supported arguments for this IPT module.
  #
  # ** IPT Module arguments must be in order as they would appear in iptables-save **
  #
  # Exceptions:
  #             => multiport: (For some reason, the multiport arguments can't be)
  #                specified within the same "-m multiport", but works in seperate
  #                ones.
  #
  @module_to_argument_mapping = {
    physdev: [:physdev_in, :physdev_out, :physdev_is_bridged, :physdev_is_in, :physdev_is_out],
    addrtype: [:src_type, :dst_type],
    iprange: [:src_range, :dst_range],
    owner: [:uid, :gid],
    time: [:time_start, :time_stop, :month_days, :week_days, :date_start, :date_stop, :time_contiguous, :kernel_timezone],
    geoip: [:src_cc, :dst_cc],
    hashlimit: [:hashlimit_upto, :hashlimit_above, :hashlimit_name, :hashlimit_burst, :hashlimit_mode, :hashlimit_srcmask, :hashlimit_dstmask,
                :hashlimit_htable_size, :hashlimit_htable_max, :hashlimit_htable_expire, :hashlimit_htable_gcinterval],
  }

  def self.munge_resource_map_from_existing_values(resource_map_original, compare)
    resource_map_new = resource_map_original.clone

    @module_to_argument_mapping.each do |ipt_module, arg_array|
      arg_array.each do |argument|
        if resource_map_original[argument].is_a?(Array)
          if compare.include?(resource_map_original[argument].first)
            resource_map_new[argument] = resource_map_original[argument].clone
            resource_map_new[argument][0] = "-m #{ipt_module} #{resource_map_original[argument].first}"
            break
          end
        elsif compare.include?(resource_map_original[argument])
          resource_map_new[argument] = "-m #{ipt_module} #{resource_map_original[argument]}"
          break
        end
      end
    end
    resource_map_new
  end

  def munge_resource_map_from_resource(resource_map_original, compare)
    resource_map_new = resource_map_original.clone
    module_to_argument_mapping = self.class.instance_variable_get('@module_to_argument_mapping')

    module_to_argument_mapping.each do |ipt_module, arg_array|
      arg_array.each do |argument|
        next unless compare[argument]
        if resource_map_original[argument].is_a?(Array)
          resource_map_new[argument] = resource_map_original[argument].clone
          resource_map_new[argument][0] = "-m #{ipt_module} #{resource_map_original[argument].first}"
        else
          resource_map_new[argument] = "-m #{ipt_module} #{resource_map_original[argument]}"
        end
        break
      end
    end
    resource_map_new
  end

  # Create property methods dynamically
  (@resource_map.keys << :chain << :table << :action).each do |property|
    if @known_booleans.include?(property)
      # The boolean properties default to '' which should be read as false
      define_method property.to_s do
        @property_hash[property] = :false if @property_hash[property].nil?
        @property_hash[property.to_sym]
      end
    else
      define_method property.to_s do
        @property_hash[property.to_sym]
      end
    end

    if property == :chain
      define_method "#{property}=" do |value|
        if @property_hash[:chain] != value
          raise ArgumentError, 'Modifying the chain for existing rules is not supported.'
        end
      end
    else
      define_method "#{property}=" do |_value|
        @property_hash[:needs_change] = true
      end
    end
  end

  # This is the order of resources as they appear in iptables-save output,
  # we need it to properly parse and apply rules, if the order of resource
  # changes between puppet runs, the changed rules will be re-applied again.
  # This order can be determined by going through iptables source code or just tweaking and trying manually
  @resource_list = [
    :table, :source, :destination, :iniface, :outiface,
    :physdev_in, :physdev_out, :physdev_is_bridged, :physdev_is_in, :physdev_is_out,
    :proto, :isfragment, :stat_mode, :stat_every, :stat_packet, :stat_probability,
    :src_range, :dst_range, :tcp_flags, :uid, :gid, :mac_source, :sport, :dport, :port,
    :src_type, :dst_type, :socket, :pkttype, :ipsec_dir, :ipsec_policy,
    :state, :ctstate, :icmp, :limit, :burst, :length, :recent, :rseconds, :reap,
    :rhitcount, :rttl, :rname, :mask, :rsource, :rdest, :ipset, :string, :string_algo,
    :string_from, :string_to, :jump, :goto, :clusterip_new, :clusterip_hashmode,
    :clusterip_clustermac, :clusterip_total_nodes, :clusterip_local_node, :clusterip_hash_init, :queue_num, :queue_bypass,
    :nflog_group, :nflog_prefix, :nflog_range, :nflog_threshold, :clamp_mss_to_pmtu, :gateway,
    :set_mss, :set_dscp, :set_dscp_class, :todest, :tosource, :toports, :to, :checksum_fill, :random, :log_prefix,
    :log_level, :log_uid, :reject, :set_mark, :match_mark, :mss, :connlimit_above, :connlimit_mask, :connmark, :time_start, :time_stop,
    :month_days, :week_days, :date_start, :date_stop, :time_contiguous, :kernel_timezone,
    :src_cc, :dst_cc, :hashlimit_upto, :hashlimit_above, :hashlimit_name, :hashlimit_burst,
    :hashlimit_mode, :hashlimit_srcmask, :hashlimit_dstmask, :hashlimit_htable_size,
    :hashlimit_htable_max, :hashlimit_htable_expire, :hashlimit_htable_gcinterval, :name
  ]

  def insert
    debug 'Inserting rule %s' % resource[:name]
    iptables insert_args
  end

  def update
    debug 'Updating rule %s' % resource[:name]
    iptables update_args
  end

  def delete
    debug 'Deleting rule %s' % resource[:name]
    begin
      iptables delete_args
    rescue Puppet::ExecutionFailure => e
      # Check to see if the iptables rule is already gone. This can sometimes
      # happen as a side effect of other resource changes. If it's not gone,
      # raise the error as per usual.
      raise e unless resource.property(:ensure).insync?(:absent)

      # If it's already gone, there is no error. Still record a change, but
      # adjust the change message to indicate ambiguity over what work Puppet
      # actually did to remove the resource, vs. what could have been a side
      # effect of something else puppet did.
      resource.property(:ensure).singleton_class.send(:define_method, :change_to_s) do |_a, _b|
        'ensured absent'
      end
    end
  end

  def exists?
    properties[:ensure] != :absent
  end

  # Flush the property hash once done.
  def flush
    debug('[flush]')
    if @property_hash.delete(:needs_change)
      notice('Properties changed - updating rule')
      update
    end
    persist_iptables(self.class.instance_variable_get(:@protocol))
    @property_hash.clear
  end

  def self.instances
    debug '[instances]'
    table = nil
    rules = []
    counter = 1

    # String#lines would be nice, but we need to support Ruby 1.8.5
    iptables_save.split("\n").each do |line|
      unless line =~ %r{^\#\s+|^\:\S+|^COMMIT|^FATAL}
        if line =~ %r{^\*}
          table = line.sub(%r{\*}, '')
        else
          hash = rule_to_hash(line, table, counter)
          if hash
            rules << new(hash)
            counter += 1
          end
        end
      end
    end
    rules
  end

  def self.rule_to_hash(line, table, counter)
    hash = {}
    keys = []
    values = line.dup

    ####################
    # PRE-PARSE CLUDGING
    ####################

    # The match for ttl
    values = values.gsub(%r{(!\s+)?-m ttl (!\s+)?--ttl-(eq|lt|gt) [0-9]+}, '')
    # --tcp-flags takes two values; we cheat by adding " around it
    # so it behaves like --comment
    values = values.gsub(%r{(!\s+)?--tcp-flags (\S*) (\S*)}, '--tcp-flags "\1\2 \3"')
    # --match-set can have multiple values with weird iptables format
    if values =~ %r{-m set (!\s+)?--match-set}
      values = values.gsub(%r{(!\s+)?--match-set (\S*) (\S*)}, '--match-set \1\2 \3')
      ind  = values.index('-m set --match-set')
      sets = values.scan(%r{-m set --match-set ((?:!\s+)?\S* \S*)})
      values = values.gsub(%r{-m set --match-set (!\s+)?\S* \S* }, '')
      values.insert(ind, "-m set --match-set \"#{sets.join(';')}\" ")
    end
    # we do a similar thing for negated address masks (source and destination).
    values = values.gsub(%r{(-\S+) (!)\s?(\S*)}, '\1 "\2 \3"')
    # fix negated physdev rules
    values = values.gsub(%r{-m physdev ! (--physdev-is-\S+)}, '-m physdev \1 "!"')
    # the actual rule will have the ! mark before the option.
    values = values.gsub(%r{(!)\s*(-\S+)\s*(\S*)}, '\2 "\1 \3"')
    # The match extension for tcp & udp are optional and throws off the @resource_map.
    values = values.gsub(%r{(?!-m tcp --tcp-flags)-m (tcp|udp) }, '')
    # There is a bug in EL5 which puts 2 spaces before physdev, so we fix it
    values = values.gsub(%r{\s{2}--physdev}, ' --physdev')
    # '--pol ipsec' takes many optional arguments; we cheat again by adding " around them
    values = values.sub(%r{
        --pol\sipsec
        (\s--strict)?
        (\s--reqid\s\S+)?
        (\s--spi\s\S+)?
        (\s--proto\s\S+)?
        (\s--mode\s\S+)?
        (\s--tunnel-dst\s\S+)?
        (\s--tunnel-src\s\S+)?
        (\s--next)?}x,
                        '--pol "ipsec\1\2\3\4\5\6\7\8" ')
    # on some iptables versions, --connlimit-saddr switch is added after the rule is applied
    values = values.gsub(%r{--connlimit-saddr}, '')

    resource_map = munge_resource_map_from_existing_values(@resource_map, values)

    # Trick the system for booleans
    @known_booleans.each do |bool|
      # append "true" because all params are expected to have values
      values = if bool == :isfragment
                 # -f requires special matching:
                 # only replace those -f that are not followed by an l to
                 # distinguish between -f and the '-f' inside of --tcp-flags.
                 values.sub(%r{\s-f(?!l)(?=.*--comment)}, ' -f true')
               else
                 # append `true` to booleans that are not already negated (followed by "!")
                 values.sub(%r{#{resource_map[bool]}(?! "!")}, "#{resource_map[bool]} true")
               end
    end

    ############
    # Populate parser_list with used value, in the correct order
    ############
    map_index = {}
    resource_map.each_pair do |map_k, map_v|
      [map_v].flatten.each do |v|
        ind = values.index(%r{\s#{v}\s})
        next unless ind
        map_index[map_k] = ind
      end
    end
    # Generate parser_list based on the index of the found option
    parser_list = []
    map_index.sort_by { |_k, v| v }.each { |mapi| parser_list << mapi.first }

    ############
    # MAIN PARSE
    ############

    # Here we iterate across our values to generate an array of keys
    parser_list.reverse.each do |k|
      resource_map_key = resource_map[k]
      [resource_map_key].flatten.each do |opt|
        if values.slice!(%r{\s#{opt}})
          keys << k
          break
        end
      end
    end

    # Manually remove chain
    if values =~ %r{(\s|^)-A\s}
      values = values.sub(%r{(\s|^)-A\s}, '\1')
      keys << :chain
    end

    # Manually remove table (used in some tests)
    if values =~ %r{^-t\s}
      values = values.sub(%r{^-t\s}, '')
      keys << :table
    end

    valrev = values.scan(%r{("([^"\\]|\\.)*"|\S+)}).transpose[0].reverse

    if keys.length != valrev.length
      warning "Skipping unparsable iptables rule: keys (#{keys.length}) and values (#{valrev.length}) count mismatch on line: #{line}"
      return
    end

    # Here we generate the main hash by scanning arguments off the values
    # string, handling any quoted characters present in the value, and then
    # zipping the values with the array of keys.
    keys.zip(valrev) do |f, v|
      hash[f] = if v =~ %r{^".*"$}
                  v.sub(%r{^"(.*)"$}, '\1').gsub(%r{\\(\\|'|")}, '\1')
                else
                  v.dup
                end
    end

    #####################
    # POST PARSE CLUDGING
    #####################

    [:dport, :sport, :port, :state, :ctstate].each do |prop|
      hash[prop] = hash[prop].split(',') unless hash[prop].nil?
    end

    hash[:ipset] = hash[:ipset].split(';') unless hash[:ipset].nil?

    ## clean up DSCP class to HEX mappings
    valid_dscp_classes = {
      '0x0a' => 'af11',
      '0x0c' => 'af12',
      '0x0e' => 'af13',
      '0x12' => 'af21',
      '0x14' => 'af22',
      '0x16' => 'af23',
      '0x1a' => 'af31',
      '0x1c' => 'af32',
      '0x1e' => 'af33',
      '0x22' => 'af41',
      '0x24' => 'af42',
      '0x26' => 'af43',
      '0x08' => 'cs1',
      '0x10' => 'cs2',
      '0x18' => 'cs3',
      '0x20' => 'cs4',
      '0x28' => 'cs5',
      '0x30' => 'cs6',
      '0x38' => 'cs7',
      '0x2e' => 'ef',
    }
    [:set_dscp_class].each do |prop|
      [:set_dscp].each do |dmark|
        next unless hash[dmark]
        hash[prop] = valid_dscp_classes[hash[dmark]]
      end
    end

    # Convert booleans removing the previous cludge we did
    @known_booleans.each do |bool|
      unless [nil, 'true', '!'].include?(hash[bool])
        raise "Parser error: #{bool} was meant to be a boolean but received value: #{hash[bool]}."
      end
    end

    # Our type prefers hyphens over colons for ranges so ...
    # Iterate across all ports replacing colons with hyphens so that ranges match
    # the types expectations.
    [:dport, :sport, :port].each do |prop|
      next unless hash[prop]
      hash[prop] = hash[prop].map do |elem|
        elem.tr(':', '-')
      end
    end
    if hash[:length]
      hash[:length].tr!(':', '-')
    end

    # Invert any rules that are prefixed with a '!'
    [
      :connmark,
      :ctstate,
      :destination,
      :dport,
      :dst_range,
      :dst_type,
      :port,
      :physdev_is_bridged,
      :physdev_is_in,
      :physdev_is_out,
      :proto,
      :source,
      :sport,
      :src_range,
      :src_type,
      :state,
    ].each do |prop|
      if hash[prop] && hash[prop].is_a?(Array)
        # find if any are negated, then negate all if so
        should_negate = hash[prop].index do |value|
          value.match(%r{^(!)\s+})
        end
        if should_negate
          hash[prop] = hash[prop].map do |v|
            "! #{v.sub(%r{^!\s+}, '')}"
          end
        end
      elsif hash[prop]
        m = hash[prop].match(%r{^(!?)\s?(.*)})
        neg = '! ' if m[1] == '!'
        hash[prop] = if [:source, :destination].include?(prop)
                       # Normalise all rules to CIDR notation.
                       "#{neg}#{Puppet::Util::IPCidr.new(m[2]).cidr}"
                     else
                       "#{neg}#{m[2]}"
                     end
      end
    end

    # States should always be sorted. This ensures that the output from
    # iptables-save and user supplied resources is consistent.
    hash[:state]   = hash[:state].sort   unless hash[:state].nil?
    hash[:ctstate] = hash[:ctstate].sort unless hash[:ctstate].nil?

    # This forces all existing, commentless rules or rules with invalid comments to be moved
    # to the bottom of the stack.
    # Puppet-firewall requires that all rules have comments (resource names) and match this
    # regex and will fail if a rule in iptables does not have a comment. We get around this
    # by appending a high level
    if !hash[:name]
      num = 9000 + counter
      hash[:name] = "#{num} #{Digest::MD5.hexdigest(line)}"
    elsif not %r{^\d+[[:graph:][:space:]]+$} =~ hash[:name] # rubocop:disable Style/Not : Making this change breaks the code
      num = 9000 + counter
      hash[:name] = "#{num} #{%r{([[:graph:][:space:]]+)}.match(hash[:name])[1]}"
    end

    # Iptables defaults to log_level '4', so it is omitted from the output of iptables-save.
    # If the :jump value is LOG and you don't have a log-level set, we assume it to be '4'.
    if hash[:jump] == 'LOG' && !hash[:log_level]
      hash[:log_level] = '4'
    end

    # Iptables defaults to burst '5', so it is ommitted from the output of iptables-save.
    # If the :limit value is set and you don't have a burst set, we assume it to be '5'.
    if hash[:limit] && !hash[:burst]
      hash[:burst] = '5'
    end

    hash[:line] = line
    hash[:provider] = name.to_s
    hash[:table] = table
    hash[:ensure] = :present

    # Munge some vars here ...

    # Proto should equal 'all' if undefined
    hash[:proto] = 'all' unless hash.include?(:proto)

    # If the jump parameter is set to one of: ACCEPT, REJECT or DROP then
    # we should set the action parameter instead.
    if %w[ACCEPT REJECT DROP].include?(hash[:jump])
      hash[:action] = hash[:jump].downcase
      hash.delete(:jump)
    end
    hash
  end

  def insert_args
    args = []
    args << ['-I', resource[:chain], insert_order]
    args << general_args
    args
  end

  def update_args
    args = []
    args << ['-R', resource[:chain], insert_order]
    args << general_args
    args
  end

  def delete_args
    # Split into arguments
    line = properties[:line].gsub(%r{^\-A }, '-D ').split(%r{\s+(?=(?:[^"]|"[^"]*")*$)}).map { |v| v.gsub(%r{^"}, '').gsub(%r{"$}, '') }
    line.unshift('-t', properties[:table])
  end

  # This method takes the resource, and attempts to generate the command line
  # arguments for iptables.
  def general_args
    debug 'Current resource: %s' % resource.class

    args = []
    resource_list = self.class.instance_variable_get('@resource_list')
    known_booleans = self.class.instance_variable_get('@known_booleans')
    resource_map = self.class.instance_variable_get('@resource_map')
    resource_map = munge_resource_map_from_resource(resource_map, resource)

    # Always attempt to wait for a lock for iptables to prevent failures when
    # puppet is running at the same time something else is managing the rules
    # note: --wait wasn't added untip iptables version 1.4.20
    iptables_version = Facter.value('iptables_version')
    if iptables_version && Puppet::Util::Package.versioncmp(iptables_version, '1.4.20') >= 0
      args << ['--wait']
    end

    # nflog options are not available on older OSes
    [:nflog_group, :nflog_prefix, :nflog_threshold, :nflog_range].each do |nflog_feature|
      raise "#{nflog_feature} is not available on iptables version #{iptables_version}" if resource[nflog_feature] && (iptables_version && iptables_version < '1.3.7')
    end

    resource_list.each do |res|
      resource_value = nil
      if resource[res]
        resource_value = resource[res]
        # If socket is true then do not add the value as -m socket is standalone
        if known_booleans.include?(res)
          next unless resource[res] == :true && known_booleans.include?(res)
          resource_value = nil
        end
      elsif res == :jump && resource[:action]
        # In this case, we are substituting jump for action
        resource_value = resource[:action].to_s.upcase
      else
        next
      end

      args << [resource_map[res]].flatten.first.split(' ')
      args = args.flatten

      # On negations, the '!' has to be before the option (eg: "! -d 1.2.3.4")
      if resource_value.is_a?(String) && resource_value.sub!(%r{^!\s*}, '')
        # we do this after adding the 'dash' argument because of ones like "-m multiport --dports", where we want it before the "--dports" but after "-m multiport".
        # so we insert before whatever the last argument is
        args.insert(-2, '!')
      elsif resource_value.is_a?(Symbol) && resource_value.to_s.match(%r{^!})
        # ruby 1.8.7 can't .match Symbols ------------------ ^
        resource_value = resource_value.to_s.sub!(%r{^!\s*}, '').to_sym
        args.insert(-2, '!')
      elsif resource_value.is_a?(Array) && res != :ipset
        should_negate = resource_value.index do |value|
          # ruby 1.8.7 can't .match symbols
          value.to_s.match(%r{^(!)\s+})
        end
        if should_negate
          resource_value, wrong_values = resource_value.map { |value|
            # rubocop:disable Metrics/BlockNesting
            if value.is_a?(String)
              wrong = value unless value =~ %r{^!\s+}
              [value.sub(%r{^!\s*}, ''), wrong]
            else
              [value, nil]
            end
          }.transpose
          wrong_values = wrong_values.compact
          unless wrong_values.empty?
            raise "All values of the '#{res}' property must be prefixed with a '!' when inverting, but '#{wrong_values.join("', '")}' #{(wrong_values.length > 1) ? 'are' : 'is'} not prefixed; aborting" # rubocop:disable Metrics/LineLength : Line length cannot be reduced
          end
          args.insert(-2, '!')
          # rubocop:enable Metrics/BlockNesting
        end
      end

      # For sport and dport, convert hyphens to colons since the type
      # expects hyphens for ranges of ports.
      if [:sport, :dport, :port].include?(res)
        resource_value = resource_value.map do |elem|
          elem.tr('-', ':')
        end
      end

      # ipset can accept multiple values with weird iptables arguments
      if res == :ipset
        resource_value.join(" #{[resource_map[res]].flatten.first} ").split(' ').each do |a|
          if a.sub!(%r{^!\s*}, '')
            # Negate ipset options
            args.insert(-2, '!')
          end

          args << a unless a.empty?
        end
      # our tcp_flags takes a single string with comma lists separated
      # by space
      # --tcp-flags expects two arguments
      elsif res == :tcp_flags
        one, two = resource_value.split(' ')
        args << one
        args << two
      elsif resource_value.is_a?(Array)
        args << resource_value.join(',')
      elsif !resource_value.nil?
        args << resource_value
      end
    end

    args
  end

  def insert_order
    debug('[insert_order]')
    rules = []

    # Find list of current rules based on chain and table
    self.class.instances.each do |rule|
      if rule.chain == resource[:chain].to_s && rule.table == resource[:table].to_s
        rules << rule.name
      end
    end

    # No rules at all? Just bail now.
    return 1 if rules.empty?

    # Add our rule to the end of the array of known rules
    my_rule = resource[:name].to_s
    rules << my_rule

    unmanaged_rule_regex = %r{^9[0-9]{3}\s.*$}
    # Find if this is a new rule or an existing rule, then find how many
    # unmanaged rules preceed it.
    if rules.length == rules.uniq.length
      # This is a new rule so find its ordered location.
      new_rule_location = rules.sort.uniq.index(my_rule)
      offset_rule = if new_rule_location.zero?
                      # The rule will be the first rule in the chain because nothing came
                      # before it.
                      rules[0]
                    else
                      # This rule will come after other managed rules, so find the rule
                      # immediately preceeding it.
                      rules.sort.uniq[new_rule_location - 1]
                    end
    else
      # This is a pre-existing rule, so find the offset from the original
      # ordering.
      offset_rule = my_rule
    end
    # Count how many unmanaged rules are ahead of the target rule so we know
    # how much to add to the insert order
    unnamed_offset = rules[0..rules.index(offset_rule)].reduce(0) do |sum, rule|
      # This regex matches the names given to unmanaged rules (a number
      # 9000-9999 followed by an MD5 hash).
      sum + (rule.match(unmanaged_rule_regex) ? 1 : 0)
    end

    # We want our rule to come before unmanaged rules if it's not a 9-rule
    if offset_rule.match(unmanaged_rule_regex) && !my_rule.match(%r{^9})
      unnamed_offset -= 1
    end

    # Insert our new or updated rule in the correct order of named rules, but
    # offset for unnamed rules.
    rules.reject { |r| r.match(unmanaged_rule_regex) }.sort.index(my_rule) + 1 + unnamed_offset
  end
end
