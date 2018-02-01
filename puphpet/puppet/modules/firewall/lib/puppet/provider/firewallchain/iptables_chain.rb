Puppet::Type.type(:firewallchain).provide :iptables_chain do
  include Puppet::Util::Firewall

  @doc = 'Iptables chain provider'

  has_feature :iptables_chain
  has_feature :policy

  optional_commands(iptables: 'iptables',
                    iptables_save: 'iptables-save',
                    ip6tables: 'ip6tables',
                    ip6tables_save: 'ip6tables-save',
                    ebtables: 'ebtables',
                    ebtables_save: 'ebtables-save')

  defaultfor kernel: :linux
  confine kernel: :linux

  # chain name is greedy so we anchor from the end.
  # [\d+:\d+] doesn't exist on ebtables
  MAPPING = {
    IPv4: {
      tables: method(:iptables),
      save: method(:iptables_save),
      re: %r{^:(.+)\s(\S+)\s\[\d+:\d+\]$},
    },
    IPv6: {
      tables: method(:ip6tables),
      save: method(:ip6tables_save),
      re: %r{^:(.+)\s(\S+)\s\[\d+:\d+\]$},
    },
    ethernet: {
      tables: method(:ebtables),
      save: method(:ebtables_save),
      re: %r{^:(.+)\s(\S+)$},
    },
  }.freeze
  INTERNAL_CHAINS = %r{^(PREROUTING|POSTROUTING|BROUTING|INPUT|FORWARD|OUTPUT)$}
  TABLES = 'nat|mangle|filter|raw|rawpost|broute|security'.freeze
  NAME_FORMAT = %r{^(.+):(#{TABLES}):(IP(v[46])?|ethernet)$}

  def create
    allvalidchains do |t, chain, table, protocol|
      if chain =~ INTERNAL_CHAINS
        # can't create internal chains
        warning "Attempting to create internal chain #{@resource[:name]}"
      end
      if properties[:ensure] == protocol
        debug "Skipping Inserting chain #{chain} on table #{table} (#{protocol}) already exists"
      else
        debug "Inserting chain #{chain} on table #{table} (#{protocol}) using #{t}"
        t.call ['-t', table, '-N', chain]
        unless @resource[:policy].nil?
          t.call ['-t', table, '-P', chain, @resource[:policy].to_s.upcase]
        end
      end
    end
  end

  def destroy
    allvalidchains do |t, chain, table|
      if chain =~ INTERNAL_CHAINS
        # can't delete internal chains
        warning "Attempting to destroy internal chain #{@resource[:name]}"
      end
      debug "Deleting chain #{chain} on table #{table}"
      t.call ['-t', table, '-X', chain]
    end
  end

  def exists?
    allvalidchains do |_t, chain|
      if chain =~ INTERNAL_CHAINS
        # If the chain isn't present, it's likely because the module isn't loaded.
        # If this is true, then we fall into 2 cases
        # 1) It'll be loaded on demand
        # 2) It won't be loaded on demand, and we throw an error
        #    This is the intended behavior as it's not the provider's job to load kernel modules
        # So we pretend it exists...
        return true
      end
    end
    properties[:ensure] == :present
  end

  def policy=(value)
    return if value == :empty
    allvalidchains do |t, chain, table|
      p = ['-t', table, '-P', chain, value.to_s.upcase]
      debug "[set policy] #{t} #{p}"
      t.call p
    end
  end

  def policy
    debug "[get policy] #{@resource[:name]} =#{@property_hash[:policy].to_s.downcase}"
    @property_hash[:policy].to_s.downcase
  end

  def self.prefetch(resources)
    debug('[prefetch(resources)]')
    instances.each do |prov|
      resource = resources[prov.name]
      if resource
        resource.provider = prov
      end
    end
  end

  def flush
    debug('[flush]')
    persist_iptables(@resource[:name].match(NAME_FORMAT)[3])
    # Clear the property hash so we re-initialize with updated values
    @property_hash.clear
  end

  # Look up the current status. This allows us to conventiently look up
  # existing status with properties[:foo].
  def properties
    if @property_hash.empty?
      @property_hash = query || { ensure: :absent }
    end
    @property_hash.dup
  end

  # Pull the current state of the list from the full list.
  def query
    self.class.instances.each do |instance|
      if instance.name == name
        debug "query found #{name}" % instance.properties.inspect
        return instance.properties
      end
    end
    nil
  end

  def self.instances
    debug '[instances]'
    table = nil
    chains = []

    MAPPING.each do |p, c|
      begin
        c[:save].call.each_line do |line|
          if line =~ c[:re]
            name = Regexp.last_match(1) + ':' + ((table == 'filter') ? 'filter' : table) + ':' + p.to_s
            policy = (Regexp.last_match(2) == '-') ? nil : Regexp.last_match(2).downcase.to_sym

            chains << new(name: name,
                          policy: policy,
                          ensure: :present)

            debug "[instance] '#{name}' #{policy}"
          elsif line =~ %r{^\*(\S+)}
            table = Regexp.last_match(1)
          else
            next
          end
        end
      rescue Puppet::Error # rubocop:disable Lint/HandleExceptions
        # ignore command not found for ebtables or anything that doesn't exist
      end
    end

    chains
  end

  def allvalidchains
    @resource[:name].match(NAME_FORMAT)
    chain = Regexp.last_match(1)
    table = Regexp.last_match(2)
    protocol = Regexp.last_match(3)
    yield MAPPING[protocol.to_sym][:tables], chain, table, protocol.to_sym
  end
end
