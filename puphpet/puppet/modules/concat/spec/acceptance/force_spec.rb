require 'spec_helper_acceptance'

basedir = default.tmpdir('concat')
describe 'force merge of file' do
  context 'should not force' do
    before(:all) do
      pp = <<-EOS
          file { '#{basedir}':
            ensure => directory,
          }
          file { '#{basedir}/file':
            content => "file exists\n"
          }
        EOS
      apply_manifest(pp)
    end
    pp = <<-EOS
        concat { '#{basedir}/file':
          format => 'yaml',
          force => false,
        }

        concat::fragment { '1':
          target  => '#{basedir}/file',
          content => '{"one": "foo"}',
        }

        concat::fragment { '2':
          target  => '#{basedir}/file',
          content => '{"one": "bar"}',
        }
      EOS

    i = 0
    num = 2
    while i < num
      it 'applies the manifest twice with stderr' do
        expect(apply_manifest(pp, catch_failures: true).stderr).to match("Duplicate key 'one' found with values 'foo' and bar'. Use 'force' attribute to merge keys.")
      end
      i += 1
    end

    describe file("#{basedir}/file") do
      it { is_expected.to be_file }
      its(:content) do
        is_expected.to match 'file exists'
      end
      its(:content) do
        is_expected.not_to match 'one: foo'
      end
      its(:content) do
        is_expected.not_to match 'one: bar'
      end
    end
  end

  context 'should not force by default' do
    before(:all) do
      pp = <<-EOS
          file { '#{basedir}':
            ensure => directory,
          }
          file { '#{basedir}/file':
            content => "file exists\n"
          }
        EOS
      apply_manifest(pp)
    end
    pp = <<-EOS
        concat { '#{basedir}/file':
          format => 'yaml',
        }

        concat::fragment { '1':
          target  => '#{basedir}/file',
          content => '{"one": "foo"}',
        }

        concat::fragment { '2':
          target  => '#{basedir}/file',
          content => '{"one": "bar"}',
        }
      EOS

    i = 0
    num = 2
    while i < num
      it 'applies the manifest twice with stderr' do
        expect(apply_manifest(pp, catch_failures: true).stderr).to match("Duplicate key 'one' found with values 'foo' and bar'. Use 'force' attribute to merge keys.")
      end
      i += 1
    end

    describe file("#{basedir}/file") do
      it { is_expected.to be_file }
      its(:content) do
        is_expected.to match 'file exists'
      end
      its(:content) do
        is_expected.not_to match 'one: foo'
      end
      its(:content) do
        is_expected.not_to match 'one: bar'
      end
    end
  end

  context 'should force' do
    before(:all) do
      pp = <<-EOS
          file { '#{basedir}':
            ensure => directory,
          }
          file { '#{basedir}/file':
            content => "file exists\n"
          }
        EOS
      apply_manifest(pp)
    end
    pp = <<-EOS
        concat { '#{basedir}/file':
          format => 'yaml',
          force => true,
        }

        concat::fragment { '1':
          target  => '#{basedir}/file',
          content => '{"one": "foo"}',
        }

        concat::fragment { '2':
          target  => '#{basedir}/file',
          content => '{"one": "bar"}',
        }
      EOS

    it 'applies the manifest twice with no stderr' do
      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)
    end

    describe file("#{basedir}/file") do
      it { is_expected.to be_file }
      its(:content) do
        is_expected.to match 'one: foo'
      end
    end
  end

  context 'should not force on plain' do
    before(:all) do
      pp = <<-EOS
          file { '#{basedir}':
            ensure => directory,
          }
          file { '#{basedir}/file':
            content => "file exists\n"
          }
        EOS
      apply_manifest(pp)
    end
    pp = <<-EOS
        concat { '#{basedir}/file':
          force => true,
          format => plain,
        }

        concat::fragment { '1':
          target  => '#{basedir}/file',
          content => '{"one": "foo"}',
        }

        concat::fragment { '2':
          target  => '#{basedir}/file',
          content => '{"one": "bar"}',
        }
      EOS

    it 'applies the manifest twice with no stderr' do
      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)
    end

    describe file("#{basedir}/file") do
      it { is_expected.to be_file }
      its(:content) do
        is_expected.to match '{"one": "foo"}{"one": "bar"}'
      end
    end
  end
end
