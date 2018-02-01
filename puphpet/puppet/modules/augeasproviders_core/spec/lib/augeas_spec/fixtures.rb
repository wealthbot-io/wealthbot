require 'augeas'
require 'tempfile'

module AugeasSpec::Fixtures
  # Creates a temp file from a given fixture name
  # Doesn't explicitly clean up the temp file as we can't evaluate a block with
  # "let" or pass the path back via an "around" hook.
  def aug_fixture(name)
    tmp = Tempfile.new("target")
    tmp.write(File.read(my_fixture(name)))
    tmp.close
    return tmp
  end

  # Runs a particular resource via a catalog
  def apply(*resources)
    catalog = Puppet::Resource::Catalog.new
    catalog.host_config = false
    resources.each do |resource|
      catalog.add_resource resource
    end
    catalog.apply
  end

  # Runs a resource and checks for warnings and errors
  def apply!(*resources)
    txn = apply(*resources)

    # Check for warning+ log messages
    loglevels = Puppet::Util::Log.levels[3, 999]
    firstlogs = @logs.dup
    @logs.select { |log| loglevels.include? log.level and log.message !~ /'modulepath' as a setting/ }.should == []

    # Check for transaction success after, as it's less informative
    txn.any_failed?.should_not eq(true)

    # Run the exact same resources, but this time ensure there were absolutely
    # no changes (as seen by logs) to indicate if it was idempotent or not
    @logs.clear
    txn_idempotent = apply(*resources)
    loglevels = Puppet::Util::Log.levels[2, 999]
    againlogs = @logs.select { |log| loglevels.include? log.level }

    againlogs.should eq([]), "expected no change on second run (idempotence check),\n     got: #{againlogs.inspect}"
    txn_idempotent.any_failed?.should_not eq(true), "expected no change on second run (idempotence check), got a resource failure"

    @logs = firstlogs
    txn
  end

  # Open Augeas on a given file.  Used for testing the results of running
  # Puppet providers.
  def aug_open(file, lens, &block)
    aug = Augeas.open(nil, nil, Augeas::NO_MODL_AUTOLOAD)
    begin
      aug.transform(
        :lens => lens,
        :name => lens.split(".")[0],
        :incl => file,
        :excl => []
      )
      aug.set("/augeas/context", "/files#{file}")
      aug.load!
      raise AugeasSpec::Error, "Augeas didn't load #{file}" if aug.match(".").empty?
      yield aug
    rescue Augeas::Error
      errors = []
      aug.match("/augeas//error").each do |errnode|
        aug.match("#{errnode}/*").each do |subnode|
          subvalue = aug.get(subnode)
          errors << "#{subnode} = #{subvalue}"
        end
      end
      raise AugeasSpec::Error, errors.join("\n")
    ensure
      aug.close
    end
  end
end
