require 'spec_helper_acceptance'

tmpdir = default.tmpdir('tmp')

describe 'ini_setting resource' do
  after :all do
    shell("rm #{tmpdir}/*.ini", acceptable_exit_codes: [0, 1, 2])
  end

  shared_examples 'has_content' do |path, pp, content|
    before :all do
      shell("rm #{path}", acceptable_exit_codes: [0, 1, 2])
    end
    after :all do
      shell("cat #{path}", acceptable_exit_codes: [0, 1, 2])
      shell("rm #{path}", acceptable_exit_codes: [0, 1, 2])
    end

    it 'applies the manifest twice' do
      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)
    end

    describe file(path) do
      it { is_expected.to be_file }

      describe '#content' do
        subject { super().content }

        it { is_expected.to match content }
      end
    end
  end

  shared_examples 'has_error' do |path, pp, error|
    before :all do
      shell("rm #{path}", acceptable_exit_codes: [0, 1, 2])
    end
    after :all do
      shell("cat #{path}", acceptable_exit_codes: [0, 1, 2])
      shell("rm #{path}", acceptable_exit_codes: [0, 1, 2])
    end

    it 'applies the manifest and gets a failure message' do
      expect(apply_manifest(pp, expect_failures: true).stderr).to match(error)
    end

    describe file(path) do
      it { is_expected.not_to be_file }
    end
  end

  context 'ensure parameter => present for global and section' do
    pp = <<-EOS
    ini_setting { 'ensure => present for section':
      ensure  => present,
      path    => "#{tmpdir}/ini_setting.ini",
      section => 'one',
      setting => 'two',
      value   => 'three',
    }
    ini_setting { 'ensure => present for global':
      ensure  => present,
      path    => "#{tmpdir}/ini_setting.ini",
      section => '',
      setting => 'four',
      value   => 'five',
    }
    EOS

    it 'applies the manifest twice' do
      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)
    end

    it_behaves_like 'has_content', "#{tmpdir}/ini_setting.ini", pp, %r{four = five\n\n\[one\]\ntwo = three}
  end

  context 'ensure parameter => present for global and section (from previous blank value)' do
    before :all do
      if fact('osfamily') == 'Darwin'
        shell("echo \"four =[one]\ntwo =\" > #{tmpdir}/ini_setting.ini")
      else
        shell("echo -e \"four =\n[one]\ntwo =\" > #{tmpdir}/ini_setting.ini")
      end
    end

    pp = <<-EOS
    ini_setting { 'ensure => present for section':
      ensure  => present,
      path    => "#{tmpdir}/ini_setting.ini",
      section => 'one',
      setting => 'two',
      value   => 'three',
    }
    ini_setting { 'ensure => present for global':
      ensure  => present,
      path    => "#{tmpdir}/ini_setting.ini",
      section => '',
      setting => 'four',
      value   => 'five',
    }
    EOS

    it 'applies the manifest twice' do
      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)
    end

    it_behaves_like 'has_content', "#{tmpdir}/ini_setting.ini", pp, %r{four = five\n\n\[one\]\ntwo = three}
  end

  context 'ensure parameter => absent for key/value' do
    before :all do
      if fact('osfamily') == 'Darwin'
        shell("echo \"four = five[one]\ntwo = three\" > #{tmpdir}/ini_setting.ini")
      else
        shell("echo -e \"four = five\n[one]\ntwo = three\" > #{tmpdir}/ini_setting.ini")
      end
    end

    pp = <<-EOS
    ini_setting { 'ensure => absent for key/value':
      ensure  => absent,
      path    => "#{tmpdir}/ini_setting.ini",
      section => 'one',
      setting => 'two',
      value   => 'three',
    }
    EOS

    it 'applies the manifest twice' do
      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)
    end

    describe file("#{tmpdir}/ini_setting.ini") do
      it { is_expected.to be_file }

      describe '#content' do # rubocop:disable RSpec/NestedGroups : Unable to reduce nesting further without affecting tests
        subject { super().content }

        it { is_expected.to match %r{four = five} }
        it { is_expected.not_to match %r{\[one\]} }
        it { is_expected.not_to match %r{two = three} }
      end
    end
  end

  context 'ensure parameter => absent for global' do
    before :all do
      if fact('osfamily') == 'Darwin'
        shell("echo \"four = five\n[one]\ntwo = three\" > #{tmpdir}/ini_setting.ini")
      else
        shell("echo -e \"four = five\n[one]\ntwo = three\" > #{tmpdir}/ini_setting.ini")
      end
    end
    after :all do
      shell("cat #{tmpdir}/ini_setting.ini", acceptable_exit_codes: [0, 1, 2])
      shell("rm #{tmpdir}/ini_setting.ini", acceptable_exit_codes: [0, 1, 2])
    end

    pp = <<-EOS
    ini_setting { 'ensure => absent for global':
      ensure  => absent,
      path    => "#{tmpdir}/ini_setting.ini",
      section => '',
      setting => 'four',
      value   => 'five',
    }
    EOS

    it 'applies the manifest twice' do
      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)
    end

    describe file("#{tmpdir}/ini_setting.ini") do
      it { is_expected.to be_file }

      describe '#content' do # rubocop:disable RSpec/NestedGroups : Unable to reduce nesting further without affecting tests
        subject { super().content }

        it { is_expected.not_to match %r{four = five} }
        it { is_expected.to match %r{\[one\]} }
        it { is_expected.to match %r{two = three} }
      end
    end
  end

  describe 'section, setting, value parameters' do
    {
      "section => 'test', setting => 'foo', value => 'bar'," => %r{\[test\]\nfoo = bar},
      "section => 'more', setting => 'baz', value => 'quux',"        => %r{\[more\]\nbaz = quux},
      "section => '',     setting => 'top', value => 'level',"       => %r{top = level},
      "section => 'z',    setting => 'sp aces', value => 'foo bar'," => %r{\[z\]\nsp aces = foo bar},
    }.each do |parameter_list, content|
      context parameter_list do
        pp = <<-EOS
        ini_setting { "#{parameter_list}":
          ensure  => present,
          path    => "#{tmpdir}/ini_setting.ini",
          #{parameter_list}
        }
        EOS

        it_behaves_like 'has_content', "#{tmpdir}/ini_setting.ini", pp, content
      end
    end

    {
      "section => 'test'," => %r{setting is a required.+value is a required},
      "setting => 'foo',  value   => 'bar'," => %r{section is a required},
      "section => 'test', setting => 'foo'," => %r{value is a required},
      "section => 'test', value   => 'bar'," => %r{setting is a required},
      "value   => 'bar',"                    => %r{section is a required.+setting is a required},
      "setting => 'foo',"                    => %r{section is a required.+value is a required},
    }.each do |parameter_list, error|
      context parameter_list, pending: 'no error checking yet' do
        pp = <<-EOS
        ini_setting { "#{parameter_list}":
          ensure  => present,
          path    => "#{tmpdir}/ini_setting.ini",
          #{parameter_list}
        }
        EOS

        it_behaves_like 'has_error', "#{tmpdir}/ini_setting.ini", pp, error
      end
    end
  end

  describe 'path parameter' do
    [
      "#{tmpdir}/one.ini",
      "#{tmpdir}/two.ini",
      "#{tmpdir}/three.ini",
    ].each do |path|
      context "path => #{path}" do
        pp = <<-EOS
        ini_setting { 'path => #{path}':
          ensure  => present,
          section => 'one',
          setting => 'two',
          value   => 'three',
          path    => '#{path}',
        }
        EOS

        it_behaves_like 'has_content', path, pp, %r{\[one\]\ntwo = three}
      end
    end

    context 'path => foo' do
      pp = <<-EOS
        ini_setting { 'path => foo':
          ensure     => present,
          section    => 'one',
          setting    => 'two',
          value      => 'three',
          path       => 'foo',
        }
      EOS

      it_behaves_like 'has_error', 'foo', pp, %r{must be fully qualified}
    end
  end

  describe 'key_val_separator parameter' do
    {
      '' => %r{two = three},
      "key_val_separator => '=',"    => %r{two=three},
      "key_val_separator => ' =  '," => %r{two =  three},
      "key_val_separator => ' '," => %r{two three},
      "key_val_separator => '   '," => %r{two   three},
    }.each do |parameter, content|
      context "with \"#{parameter}\" makes \"#{content}\"" do
        pp = <<-EOS
        ini_setting { "with #{parameter} makes #{content}":
          ensure  => present,
          section => 'one',
          setting => 'two',
          value   => 'three',
          path    => "#{tmpdir}/key_val_separator.ini",
          #{parameter}
        }
        EOS

        it_behaves_like 'has_content', "#{tmpdir}/key_val_separator.ini", pp, content
      end
    end
  end

  describe 'show_diff parameter and logging:' do
    [{ value: 'initial_value', matcher: 'created', show_diff: true },
     { value: 'public_value', matcher: %r{initial_value.*public_value}, show_diff: true },
     { value: 'secret_value', matcher: %r{redacted sensitive information.*redacted sensitive information}, show_diff: false },
     { value: 'md5_value', matcher: %r{\{md5\}881671aa2bbc680bc530c4353125052b.*\{md5\}ed0903a7fa5de7886ca1a7a9ad06cf51}, show_diff: :md5 }].each do |i|

      pp = <<-EOS
          ini_setting { 'test_show_diff':
            ensure      => present,
            section     => 'test',
            setting     => 'something',
            value       => '#{i[:value]}',
            path        => "#{tmpdir}/test_show_diff.ini",
            show_diff   => #{i[:show_diff]}
          }
      EOS

      context "show_diff => #{i[:show_diff]}" do
        config = { 'main' => { 'show_diff' => true } }
        configure_puppet_on(default, config)

        res = apply_manifest(pp, expect_changes: true)
        it 'applies manifest and expects changed value to be logged in proper form' do
          expect(res.stdout).to match(i[:matcher])
        end
        it 'applies manifest and expects changed value to be logged in proper form #optional test' do
          expect(res.stdout).not_to match(i[:value]) unless i[:show_diff] == true
        end
      end
    end
  end
end
