require 'augeas'
require 'tempfile'

module AugeasSpec::Augparse
  # Creates a simple test file, reads in a fixture (that's been modified by
  # the provider) and runs augparse against the expected tree.
  def augparse(file, lens, result = "?")
    Dir.mktmpdir { |dir|
      # Augeas always starts with a blank line when creating new files, so
      # reprocess file and remove it to make writing tests easier
      File.open("#{dir}/input", "w") do |finput|
        File.open(file, "r") do |ffile|
          line = ffile.readline
          finput.write line unless line == "\n"
          ffile.each {|line| finput.write line }
        end
      end

      # Test module, Augeas reads back in the input file
      testaug = "#{dir}/test_augeasproviders.aug"
      File.open(testaug, "w") { |tf|
        tf.write(<<eos)
module Test_Augeasproviders =
  test #{lens} get Sys.read_file "#{dir}/input" =
    #{result}
eos
      }

      output = %x(augparse --notypecheck #{testaug} 2>&1)
      raise AugeasSpec::Error, "augparse failed:\n#{output}" unless $? == 0 && output.empty?
    }
  end

  # Takes a full fixture file, loads it in Augeas, uses the relative path
  # and/or filter and saves just that part in a new file.  That's then passed
  # into augparse and compared against the expected tree.  Saves creating a
  # tree of the entire file.
  #
  # Because the filtered fragment is saved in a new file, seq labels will reset
  # too, so it'll be "1" rather than what it was in the original fixture.
  def augparse_filter(file, lens, filter, result)
    # duplicate the original since we use aug.mv
    tmpin = Tempfile.new("original")
    tmpin.write(File.read(file))
    tmpin.close

    tmpout = Tempfile.new("filtered")
    tmpout.close

    aug_open(tmpin.path, lens) do |aug|
      # Load a transform of the target, so Augeas can write into it
      aug.transform(
        :lens => lens,
        :name => lens.split(".")[0],
        :incl => tmpout.path,
        :excl => []
      )
      aug.load!
      tmpaug = "/files#{tmpout.path}"
      raise AugeasSpec::Error, "Augeas didn't load empty file #{tmpout.path}" if aug.match(tmpaug).empty?

      # Check the filter matches something and move it
      ftmatch = aug.match(filter)
      raise AugeasSpec::Error, "Filter #{filter} within #{file} matched #{ftmatch.size} nodes, should match at least one" if ftmatch.empty?

      begin
        # Loop on aug_match as path indexes will change as we move nodes
        fp = ftmatch.first
        aug.mv(fp, "#{tmpaug}/#{fp.split(/\//)[-1]}")
        ftmatch = aug.match(filter)
      end while not ftmatch.empty?

      aug.save!
    end

    augparse(tmpout.path, lens, result)
  ensure
    tmpin.unlink
    tmpout.unlink
  end
end
