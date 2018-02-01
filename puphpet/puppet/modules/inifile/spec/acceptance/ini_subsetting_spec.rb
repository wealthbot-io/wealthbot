require 'spec_helper_acceptance'

tmpdir = default.tmpdir('tmp')

describe 'ini_subsetting resource' do
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

        it {
          is_expected.to match content
        }
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

  describe 'ensure, section, setting, subsetting, & value parameters => present with subsections' do
    pp = <<-EOS
    ini_subsetting { 'ensure => present for alpha':
      ensure     => present,
      path       => "#{tmpdir}/ini_subsetting.ini",
      section    => 'one',
      setting    => 'key',
      subsetting => 'alpha',
      value      => 'bet',
    }
    ini_subsetting { 'ensure => present for beta':
      ensure     => present,
      path       => "#{tmpdir}/ini_subsetting.ini",
      section    => 'one',
      setting    => 'key',
      subsetting => 'beta',
      value      => 'trons',
      require    => Ini_subsetting['ensure => present for alpha'],
    }
    EOS

    it 'applies the manifest twice' do
      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)
    end

    describe file("#{tmpdir}/ini_subsetting.ini") do
      it { is_expected.to be_file }

      describe '#content' do # rubocop:disable RSpec/NestedGroups : Unable to reduce nesting further without affecting tests
        subject { super().content }

        it { is_expected.to match %r{\[one\]\nkey = alphabet betatrons} }
      end
    end
  end

  describe 'ensure => absent' do
    before :all do
      if fact('osfamily') == 'Darwin'
        shell("echo \"[one]\nkey = alphabet betatrons\" > #{tmpdir}/ini_subsetting.ini")
      else
        shell("echo -e \"[one]\nkey = alphabet betatrons\" > #{tmpdir}/ini_subsetting.ini")
      end
    end

    pp = <<-EOS
    ini_subsetting { 'ensure => absent for subsetting':
      ensure     => absent,
      path       => "#{tmpdir}/ini_subsetting.ini",
      section    => 'one',
      setting    => 'key',
      subsetting => 'alpha',
    }
    EOS

    it 'applies the manifest twice' do
      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)
    end

    describe file("#{tmpdir}/ini_subsetting.ini") do
      it { is_expected.to be_file }

      describe '#content' do # rubocop:disable RSpec/NestedGroups : Unable to reduce nesting further without affecting tests
        subject { super().content }

        it { is_expected.to match %r{\[one\]} }
        it { is_expected.to match %r{key = betatrons} }
        it { is_expected.not_to match %r{alphabet} }
      end
    end
  end

  describe 'subsetting_separator' do
    {
      '' => %r{two = twinethree foobar},
      "subsetting_separator => ',',"    => %r{two = twinethree,foobar},
      "subsetting_separator => '   ',"  => %r{two = twinethree   foobar},
      "subsetting_separator => ' == '," => %r{two = twinethree == foobar},
      "subsetting_separator => '=',"    => %r{two = twinethree=foobar},
    }.each do |parameter, content|
      context "with \"#{parameter}\" makes \"#{content}\"" do
        pp = <<-EOS
        ini_subsetting { "with #{parameter} makes #{content}":
          ensure     => present,
          section    => 'one',
          setting    => 'two',
          subsetting => 'twine',
          value      => 'three',
          path       => "#{tmpdir}/subsetting_separator.ini",
          before     => Ini_subsetting['foobar'],
          #{parameter}
        }
        ini_subsetting { "foobar":
          ensure     => present,
          section    => 'one',
          setting    => 'two',
          subsetting => 'foo',
          value      => 'bar',
          path       => "#{tmpdir}/subsetting_separator.ini",
          #{parameter}
        }
        EOS

        it_behaves_like 'has_content', "#{tmpdir}/subsetting_separator.ini", pp, content
      end
    end
  end

  describe 'subsetting_key_val_separator' do
    {
      '' => %r{two = twinethree foobar},
      "subsetting_key_val_separator => ':',"    => %r{two = twine:three foo:bar},
      "subsetting_key_val_separator => '-',"    => %r{two = twine-three foo-bar},
    }.each do |parameter, content|
      context "with '#{parameter}' makes '#{content}'" do
        pp = <<-EOS
        ini_subsetting { "with #{parameter} makes #{content}":
          ensure     => 'present',
          section    => 'one',
          setting    => 'two',
          subsetting => 'twine',
          value      => 'three',
          path       => "#{tmpdir}/subsetting_key_val_separator.ini",
          before     => Ini_subsetting['foobar'],
          #{parameter}
        }
        ini_subsetting { "foobar":
          ensure     => 'present',
          section    => 'one',
          setting    => 'two',
          subsetting => 'foo',
          value      => 'bar',
          path       => "#{tmpdir}/subsetting_key_val_separator.ini",
          #{parameter}
        }
        EOS

        it_behaves_like 'has_content', "#{tmpdir}/subsetting_key_val_separator.ini", pp, content
      end
    end
  end

  describe 'quote_char' do
    {
      ['-Xmx'] => %r{args=""},
      ['-Xmx', '256m'] => %r{args=-Xmx256m},
      ['-Xmx', '512m'] => %r{args="-Xmx512m"},
      ['-Xms', '256m'] => %r{args="-Xmx256m -Xms256m"},
    }.each do |parameter, content|
      context %(with '#{parameter.first}' #{(parameter.length > 1) ? '=> \'' << parameter[1] << '\'' : 'absent'} makes '#{content}') do
        path = File.join(tmpdir, 'ini_subsetting.ini')

        before :all do
          shell(%(echo '[java]\nargs=-Xmx256m' > #{path}))
        end
        after :all do
          shell("cat #{path}", acceptable_exit_codes: [0, 1, 2])
          shell("rm #{path}", acceptable_exit_codes: [0, 1, 2])
        end

        pp = <<-EOS
        ini_subsetting { '#{parameter.first}':
          ensure     => #{(parameter.length > 1) ? 'present' : 'absent'},
          path       => '#{path}',
          section    => 'java',
          setting    => 'args',
          quote_char => '"',
          subsetting => '#{parameter.first}',
          value      => '#{(parameter.length > 1) ? parameter[1] : ''}'
        }
        EOS

        it 'applies the manifest twice' do
          apply_manifest(pp, catch_failures: true)
          apply_manifest(pp, catch_changes: true)
        end

        describe file("#{tmpdir}/ini_subsetting.ini") do # rubocop:disable RSpec/NestedGroups : Unable to reduce nesting further without affecting tests
          it { is_expected.to be_file }

          describe '#content' do # rubocop:disable RSpec/NestedGroups : Unable to reduce nesting further without affecting tests
            subject { super().content }

            it { is_expected.to match content }
          end
        end
      end
    end
  end

  describe 'show_diff parameter and logging:' do
    [{ value: 'initial_value', matcher: 'created', show_diff: true },
     { value: 'public_value', matcher: %r{initial_value.*public_value}, show_diff: true },
     { value: 'secret_value', matcher: %r{redacted sensitive information.*redacted sensitive information}, show_diff: false },
     { value: 'md5_value', matcher: %r{\{md5\}881671aa2bbc680bc530c4353125052b.*\{md5\}ed0903a7fa5de7886ca1a7a9ad06cf51}, show_diff: :md5 }].each do |i|
      context "show_diff => #{i[:show_diff]}" do
        pp = <<-EOS
          ini_subsetting { 'test_show_diff':
            ensure      => present,
            section     => 'test',
            setting     => 'something',
            subsetting  => 'xxx',
            value       => '#{i[:value]}',
            path        => "#{tmpdir}/test_show_diff.ini",
            show_diff   => #{i[:show_diff]}
          }
        EOS

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

  describe 'insert types:' do
    [
      {
        insert_type: :start,
        content: %r{d a b c},
      },
      {
        insert_type: :end,
        content: %r{a b c d},
      },
      {
        insert_type: :before,
        insert_value: 'c',
        content: %r{a b d c},
      },
      {
        insert_type: :after,
        insert_value: 'a',
        content: %r{a d b c},
      },
      {
        insert_type: :index,
        insert_value: 2,
        content: %r{a b d c},
      },
    ].each do |params|
      context "with '#{params[:insert_type]}' makes '#{params[:content]}'" do
        pp = <<-EOS
        ini_subsetting { "a":
          ensure     => present,
          section    => 'one',
          setting    => 'two',
          subsetting => 'a',
          path       => "#{tmpdir}/insert_types.ini",
        } ->
        ini_subsetting { "b":
          ensure     => present,
          section    => 'one',
          setting    => 'two',
          subsetting => 'b',
          path       => "#{tmpdir}/insert_types.ini",
        } ->
        ini_subsetting { "c":
          ensure     => present,
          section    => 'one',
          setting    => 'two',
          subsetting => 'c',
          path       => "#{tmpdir}/insert_types.ini",
        } ->
        ini_subsetting { "insert makes #{params[:content]}":
          ensure       => present,
          section      => 'one',
          setting      => 'two',
          subsetting   => 'd',
          path         => "#{tmpdir}/insert_types.ini",
          insert_type  => '#{params[:insert_type]}',
          insert_value => '#{params[:insert_value]}',
        }
        EOS

        it_behaves_like 'has_content', "#{tmpdir}/insert_types.ini", pp, params[:content]
      end
    end
  end
end
