# debian.rb --- Debian provider for debconf type

Puppet::Type.type(:debconf).provide(:debian) do
  desc %q{Manage debconf database entries on Debian based systems.}

  confine :osfamily => :debian
  defaultfor :osfamily => :debian

  class Debconf < IO
    # A private class to communicate with the debconf database

    # The regular expression used to parse the debconf-communicate output
    DEBCONF_COMMUNICATE = Regexp.new(
      "^([0-9]+)" +             # error code
      "\s*" +                   # whitespace
      "(.*)" +                  # return value
      "\s*$"                    # optional trailing spaces
    )

    def initialize(pipe)
      # The pipe to the debconf-communicate program
      @pipe = pipe

      # Last return code and message from debconf-communicate
      @retcode = nil
      @retmesg = ''
    end

    # Open communication channel with the debconf database
    def self.communicate(package)
      Puppet.debug("Debconf: open pipe to debconf-communicate for #{package}")

      pipe = IO.popen("/usr/bin/debconf-communicate #{package}", 'w+')

      unless pipe
        fail("Debconf: failed to open pipe to debconf-communicate")
      end

      # Call block for pipe
      yield self.new(pipe) if block_given?

      # Close pipe and finish, ignore remaining output from command
      pipe.close_write
      pipe.read(nil)
      @pipe = nil
    end

    # Send a command to the debconf-communicate pipe and collect response
    def send(command)
      Puppet.debug("Debconf: send #{command}")

      @pipe.puts(command)
      response = @pipe.gets("\n")

      if response
        if DEBCONF_COMMUNICATE.match(response)
          # Response is devided into the return code (casted to int) and the
          # result text. Depending on the context the text could be an error
          # message or the value of an item.
          @retcode, @retmesg = $1.to_i, $2
        else
          fail("Debconf: debconf-communicate returned (#{response})")
        end
      else
        fail("Debconf: debconf-communicate unexpectedly closed pipe")
      end
    end

    # Get an item from the debconf database
    # Return the value of the item or nil if the item is not found
    def get(item)
      self.send("GET #{item}")

      # Check for errors
      case @retcode
      when 0 then @retmesg      # OK
      when 10 then nil          # item doesn't exist
      else
        fail("Debconf: debconf-communicate returned #{@retcode}: #{@retmesg}")
      end
    end

    # Unregister an item in the debconf database
    def unregister(item)
      self.send("UNREGISTER #{item}")

      # Check for errors
      unless @retcode == 0
        fail("Debconf: debconf-communicate returned #{@retcode}: #{@retmesg}")
      end
    end
  end

  #
  # The Debian debconf provider
  #

  def initialize(value = {})
    super(value)
    @properties = Hash.new
  end

  # Fetch item properties
  def fetch
    Puppet.debug("Debconf: fetch #{resource[:item]} for #{resource[:package]}")

    Debconf.communicate(resource[:package]) do |debconf|
      value = debconf.get(resource[:item])

      if value
        Puppet.debug("Debconf: #{resource[:item]} = '#{value}'")
        @properties[:value] = value
        @properties[:exists] = true
      else
        @properties[:exists] = false
      end
    end
  end

  # Call debconf-set-selections to store the item value
  def update
    Puppet.debug("Debconf: updating #{resource[:name]}")

    # Build the string to send
    args = [:package, :item, :type, :value].map { |e| resource[e] }.join(' ')

    IO.popen('/usr/bin/debconf-set-selections', 'w+') do |pipe|
      Puppet.debug("Debconf: debconf-set-selections #{args}")
      pipe.puts(args)

      # Ignore remaining output from command
      pipe.close_write
      pipe.read(nil)
    end
  end

  def create
    Puppet.debug("Debconf: calling create #{resource[:name]}")
    update
  end

  def destroy
    Puppet.debug("Debconf: calling destroy for #{resource[:name]}")

    Debconf.communicate(resource[:package]) do |debconf|
      debconf.unregister(resource[:item])
    end
  end

  def exists?
    Puppet.debug("Debconf: calling exists? for #{resource[:name]}")
    fetch if @properties.empty?

    @properties[:exists]
  end

  def value
    Puppet.debug("Debconf: calling get #{resource[:item]}")
    fetch if @properties.empty?

    @properties[:value]
  end

  def value=(val)
    Puppet.debug("Debconf: calling set #{resource[:item]} to #{val}")
    update
  end
end
