require 'spec_helper'
require 'stringio'
require 'puppet/util/ini_file'

describe Puppet::Util::IniFile do
  let(:subject) { described_class.new('/my/ini/file/path') }

  before :each do
    allow(File).to receive(:file?).with('/my/ini/file/path') { true }
    allow(described_class).to receive(:readlines).once.with('/my/ini/file/path') do
      sample_content
    end
  end

  context 'when parsing a file' do
    let(:sample_content) do
      template = <<-EOS
        # This is a comment
        [section1]
        ; This is also a comment
        foo=foovalue

        bar = barvalue
        baz =
        [section2]

        foo= foovalue2
        baz=bazvalue
         ; commented = out setting
            #another comment
         ; yet another comment
         zot = multi word value
         xyzzy['thing1']['thing2']=xyzzyvalue
         l=git log
      EOS
      template.split("\n")
    end

    it 'parses the correct number of sections' do
      # there is always a "global" section, so our count should be 3.
      expect(subject.section_names.length).to eq(3)
    end

    it 'parses the correct section_names' do
      # there should always be a "global" section named "" at the beginning of the list
      expect(subject.section_names).to eq(['', 'section1', 'section2'])
    end

    it 'exposes settings for sections #section1' do
      expect(subject.get_settings('section1')).to eq('bar' => 'barvalue',
                                                     'baz' => '',
                                                     'foo' => 'foovalue')
    end
    it 'exposes settings for sections #section2' do
      expect(subject.get_settings('section2')).to eq('baz' => 'bazvalue',
                                                     'foo' => 'foovalue2',
                                                     'l' => 'git log',
                                                     "xyzzy['thing1']['thing2']" => 'xyzzyvalue',
                                                     'zot' => 'multi word value')
    end
  end

  context 'when parsing a file whose first line is a section' do
    let(:sample_content) do
      template = <<-EOS
        [section1]
        ; This is a comment
        foo=foovalue
      EOS
      template.split("\n")
    end

    it 'parses the correct number of sections' do
      # there is always a "global" section, so our count should be 2.
      expect(subject.section_names.length).to eq(2)
    end

    it 'parses the correct section_names' do
      # there should always be a "global" section named "" at the beginning of the list
      expect(subject.section_names).to eq(['', 'section1'])
    end

    it 'exposes settings for sections' do
      expect(subject.get_value('section1', 'foo')).to eq('foovalue')
    end
  end

  context "when parsing a file with a 'global' section" do
    let(:sample_content) do
      template = <<-EOS
        foo = bar
        [section1]
        ; This is a comment
        foo=foovalue
      EOS
      template.split("\n")
    end

    it 'parses the correct number of sections' do
      # there is always a "global" section, so our count should be 2.
      expect(subject.section_names.length).to eq(2)
    end

    it 'parses the correct section_names' do
      # there should always be a "global" section named "" at the beginning of the list
      expect(subject.section_names).to eq(['', 'section1'])
    end

    it 'exposes settings for sections #bar' do
      expect(subject.get_value('', 'foo')).to eq('bar')
    end
    it 'exposes settings for sections #foovalue' do
      expect(subject.get_value('section1', 'foo')).to eq('foovalue')
    end
  end

  context 'when updating a file with existing empty values' do
    let(:sample_content) do
      template = <<-EOS
        [section1]
        foo=
        #bar=
        #xyzzy['thing1']['thing2']='xyzzyvalue'
      EOS
      template.split("\n")
    end

    # rubocop:disable RSpec/ExpectInHook
    before :each do
      expect(subject.get_value('section1', 'far')).to eq(nil)
      expect(subject.get_value('section1', 'bar')).to eq(nil)
      expect(subject.get_value('section1', "xyzzy['thing1']['thing2']")).to eq(nil)
    end
    # rubocop:enable RSpec/ExpectInHook

    it 'properlies update uncommented values' do
      subject.set_value('section1', 'foo', ' = ', 'foovalue')
      expect(subject.get_value('section1', 'foo')).to eq('foovalue')
    end

    it 'properlies update uncommented values without separator' do
      subject.set_value('section1', 'foo', 'foovalue')
      expect(subject.get_value('section1', 'foo')).to eq('foovalue')
    end

    it 'properlies update commented value' do
      subject.set_value('section1', 'bar', ' = ', 'barvalue')
      expect(subject.get_value('section1', 'bar')).to eq('barvalue')
    end

    it 'properlies update commented values' do
      subject.set_value('section1', "xyzzy['thing1']['thing2']", ' = ', 'xyzzyvalue')
      expect(subject.get_value('section1', "xyzzy['thing1']['thing2']")).to eq('xyzzyvalue')
    end

    it 'properlies update commented value without separator' do
      subject.set_value('section1', 'bar', 'barvalue')
      expect(subject.get_value('section1', 'bar')).to eq('barvalue')
    end

    it 'properlies update commented values without separator' do
      subject.set_value('section1', "xyzzy['thing1']['thing2']", 'xyzzyvalue')
      expect(subject.get_value('section1', "xyzzy['thing1']['thing2']")).to eq('xyzzyvalue')
    end

    it 'properlies add new empty values' do
      subject.set_value('section1', 'baz', ' = ', 'bazvalue')
      expect(subject.get_value('section1', 'baz')).to eq('bazvalue')
    end

    it 'adds new empty values without separator' do
      subject.set_value('section1', 'baz', 'bazvalue')
      expect(subject.get_value('section1', 'baz')).to eq('bazvalue')
    end
  end

  context 'the file has quotation marks in its section names' do
    let(:sample_content) do
      template = <<-EOS
        [branch "master"]
                remote = origin
                merge = refs/heads/master

        [alias]
        to-deploy = log --merges --grep='pull request' --format='%s (%cN)' origin/production..origin/master
        [branch "production"]
                remote = origin
                merge = refs/heads/production
      EOS
      template.split("\n")
    end

    it 'parses the sections' do
      expect(subject.section_names).to match_array ['',
                                                    'branch "master"',
                                                    'alias',
                                                    'branch "production"']
    end
  end

  context 'Samba INI file with dollars in section names' do
    let(:sample_content) do
      template = <<-EOS
        [global]
          workgroup = FELLOWSHIP
          ; ...
          idmap config * : backend = tdb

        [printers]
          comment = All Printers
          ; ...
          browseable = No

        [print$]
          comment = Printer Drivers
          path = /var/lib/samba/printers

        [Shares]
          path = /home/shares
          read only = No
          guest ok = Yes
      EOS
      template.split("\n")
    end

    it 'parses the correct section_names' do
      expect(subject.section_names).to match_array ['', 'global', 'printers', 'print$', 'Shares']
    end
  end

  context 'section names with forward slashes in them' do
    let(:sample_content) do
      template = <<-EOS
        [monitor:///var/log/*.log]
        disabled = test_value
      EOS
      template.split("\n")
    end

    it 'parses the correct section_names' do
      expect(subject.section_names).to match_array [
        '',
        'monitor:///var/log/*.log',
      ]
    end
  end

  context 'KDE Configuration with braces in setting names' do
    let(:sample_content) do
      template = <<-EOS
              [khotkeys]
        _k_friendly_name=khotkeys
        {5465e8c7-d608-4493-a48f-b99d99fdb508}=Print,none,PrintScreen
        {d03619b6-9b3c-48cc-9d9c-a2aadb485550}=Search,none,Search
      EOS
      template.split("\n")
    end

    it 'exposes settings for sections #print' do
      expect(subject.get_value('khotkeys', '{5465e8c7-d608-4493-a48f-b99d99fdb508}')).to eq('Print,none,PrintScreen')
    end
    it 'exposes settings for sections #search' do
      expect(subject.get_value('khotkeys', '{d03619b6-9b3c-48cc-9d9c-a2aadb485550}')).to eq('Search,none,Search')
    end
  end

  context 'Configuration with colons in setting names' do
    let(:sample_content) do
      template = <<-EOS
              [Drive names]
        A:=5.25" Floppy
        B:=3.5" Floppy
        C:=Winchester
      EOS
      template.split("\n")
    end

    it 'exposes settings for sections #A' do
      expect(subject.get_value('Drive names', 'A:')).to eq '5.25" Floppy'
    end
    it 'exposes settings for sections #B' do
      expect(subject.get_value('Drive names', 'B:')).to eq '3.5" Floppy'
    end
    it 'exposes settings for sections #C' do
      expect(subject.get_value('Drive names', 'C:')).to eq 'Winchester'
    end
  end

  context 'Configuration with spaces in setting names' do
    let(:sample_content) do
      template = <<-EOS
        [global]
          # log files split per-machine:
          log file = /var/log/samba/log.%m

          kerberos method = system keytab
          passdb backend = tdbsam
          security = ads
      EOS
      template.split("\n")
    end

    it 'exposes settings for sections #log' do
      expect(subject.get_value('global', 'log file')).to eq '/var/log/samba/log.%m'
    end
    it 'exposes settings for sections #kerberos' do
      expect(subject.get_value('global', 'kerberos method')).to eq 'system keytab'
    end
    it 'exposes settings for sections #passdb' do
      expect(subject.get_value('global', 'passdb backend')).to eq 'tdbsam'
    end
    it 'exposes settings for sections #security' do
      expect(subject.get_value('global', 'security')).to eq 'ads'
    end
  end
end
