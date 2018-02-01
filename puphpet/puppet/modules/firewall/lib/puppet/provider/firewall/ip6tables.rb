Puppet::Type.type(:firewall).provide :ip6tables, parent: :iptables, source: :ip6tables do
  @doc = 'Ip6tables type provider'

  has_feature :iptables
  has_feature :connection_limiting
  has_feature :hop_limiting
  has_feature :rate_limiting
  has_feature :recent_limiting
  has_feature :snat
  has_feature :dnat
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
  has_feature :tcp_flags
  has_feature :pkttype
  has_feature :ishasmorefrags
  has_feature :islastfrag
  has_feature :isfirstfrag
  has_feature :socket
  has_feature :address_type
  has_feature :iprange
  has_feature :ipsec_dir
  has_feature :ipsec_policy
  has_feature :mask
  has_feature :ipset
  has_feature :length
  has_feature :string_matching
  has_feature :queue_num
  has_feature :queue_bypass

  optional_commands(ip6tables: 'ip6tables',
                    ip6tables_save: 'ip6tables-save')

  confine kernel: :linux

  ip6tables_version = Facter.value('ip6tables_version')
  mark_flag = if ip6tables_version && Puppet::Util::Package.versioncmp(ip6tables_version, '1.4.1') < 0
                '--set-mark'
              else
                '--set-xmark'
              end

  def initialize(*args)
    ip6tables_version = Facter.value('ip6tables_version')
    raise ArgumentError, 'The ip6tables provider is not supported on version 1.3 of iptables' if ip6tables_version && ip6tables_version.match(%r{1\.3\.\d})
    super
  end

  def self.iptables(*args)
    ip6tables(*args)
  end

  def self.iptables_save(*args)
    ip6tables_save(*args)
  end

  @protocol = 'IPv6'

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
    hop_limit: '-m hl --hl-eq',
    icmp: '-m icmp6 --icmpv6-type',
    iniface: '-i',
    ipsec_dir: '-m policy --dir',
    ipsec_policy: '--pol',
    ipset: '-m set --match-set',
    isfirstfrag: '-m frag --fragid 0 --fragfirst',
    ishasmorefrags: '-m frag --fragid 0 --fragmore',
    islastfrag: '-m frag --fragid 0 --fraglast',
    jump: '-j',
    length: '-m length --length',
    limit: '-m limit --limit',
    log_level: '--log-level',
    log_prefix: '--log-prefix',
    log_uid: '--log-uid',
    mask: '--mask',
    match_mark: '-m mark --mark',
    name: '-m comment --comment',
    mac_source: ['-m mac --mac-source', '--mac-source'],
    mss: '-m tcpmss --mss',
    outiface: '-o',
    pkttype: '-m pkttype --pkt-type',
    port: '-m multiport --ports',
    proto: '-p',
    queue_num: '--queue-num',
    queue_bypass: '--queue-bypass',
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
    :ishasmorefrags,
    :islastfrag,
    :isfirstfrag,
    :log_uid,
    :rsource,
    :rdest,
    :reap,
    :rttl,
    :socket,
    :physdev_is_bridged,
    :physdev_is_in,
    :physdev_is_out,
    :time_contiguous,
    :kernel_timezone,
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
  # (Note: on my CentOS 6.4 ip6tables-save returns -m frag on the place
  # I put it when calling the command. So compability with manual changes
  # not provided with current parser [georg.koester])
  @resource_list = [:table, :source, :destination, :iniface, :outiface, :physdev_in,
                    :physdev_out, :physdev_is_bridged, :physdev_is_in, :physdev_is_out,
                    :proto, :ishasmorefrags, :islastfrag, :isfirstfrag, :src_range, :dst_range,
                    :tcp_flags, :uid, :gid, :mac_source, :sport, :dport, :port, :src_type,
                    :dst_type, :socket, :pkttype, :ipsec_dir, :ipsec_policy, :state,
                    :ctstate, :icmp, :hop_limit, :limit, :burst, :length, :recent, :rseconds, :reap,
                    :rhitcount, :rttl, :rname, :mask, :rsource, :rdest, :ipset, :string, :string_algo,
                    :string_from, :string_to, :jump, :clamp_mss_to_pmtu, :gateway, :todest,
                    :tosource, :toports, :checksum_fill, :log_level, :log_prefix, :log_uid, :reject, :set_mss, :set_dscp, :set_dscp_class, :mss, :queue_num, :queue_bypass,
                    :set_mark, :match_mark, :connlimit_above, :connlimit_mask, :connmark, :time_start, :time_stop, :month_days, :week_days, :date_start, :date_stop, :time_contiguous, :kernel_timezone,
                    :src_cc, :dst_cc, :hashlimit_upto, :hashlimit_above, :hashlimit_name, :hashlimit_burst,
                    :hashlimit_mode, :hashlimit_srcmask, :hashlimit_dstmask, :hashlimit_htable_size,
                    :hashlimit_htable_max, :hashlimit_htable_expire, :hashlimit_htable_gcinterval, :name]
end
