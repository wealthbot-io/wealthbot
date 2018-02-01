require 'spec_helper_acceptance'

case fact('osfamily')
when 'AIX'
  username = 'root'
  groupname = 'system'
when 'Darwin'
  username = 'root'
  groupname = 'wheel'
when 'windows'
  username = 'Administrator'
  groupname = 'Administrators'
else
  username = 'root'
  groupname = 'root'
end

describe 'concat::fragment source' do
  basedir = default.tmpdir('concat')
  context 'should read file fragments from local system' do
    pp = <<-EOS
      file { '#{basedir}/file1':
        content => "file1 contents\n"
      }
      file { '#{basedir}/file2':
        content => "file2 contents\n"
      }
      concat { '#{basedir}/foo': }

      concat::fragment { '1':
        target  => '#{basedir}/foo',
        source  => '#{basedir}/file1',
        require => File['#{basedir}/file1'],
      }
      concat::fragment { '2':
        target  => '#{basedir}/foo',
        content => 'string1 contents',
      }
      concat::fragment { '3':
        target  => '#{basedir}/foo',
        source  => '#{basedir}/file2',
        require => File['#{basedir}/file2'],
      }
    EOS

    it 'applies the manifest twice with no stderr' do
      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)
    end

    describe file("#{basedir}/foo") do
      it { is_expected.to be_file }

      its(:content) do
        is_expected.to match 'file1 contents'
      end

      its(:content) do
        is_expected.to match 'string1 contents'
      end

      its(:content) do
        is_expected.to match 'file2 contents'
      end
    end
  end # should read file fragments from local system

  context 'should create files containing first match only.' do
    pp = <<-EOS
      file { '#{basedir}/file1':
        content => "file1 contents\n"
      }
      file { '#{basedir}/file2':
        content => "file2 contents\n"
      }
      concat { '#{basedir}/result_file1':
        owner   => '#{username}',
        group   => '#{groupname}',
        mode    => '0644',
      }
      concat { '#{basedir}/result_file2':
        owner   => '#{username}',
        group   => '#{groupname}',
        mode    => '0644',
      }
      concat { '#{basedir}/result_file3':
        owner   => '#{username}',
        group   => '#{groupname}',
        mode    => '0644',
      }

      concat::fragment { '1':
        target  => '#{basedir}/result_file1',
        source  => [ '#{basedir}/file1', '#{basedir}/file2' ],
        require => [ File['#{basedir}/file1'], File['#{basedir}/file2'] ],
        order   => '01',
      }
      concat::fragment { '2':
        target  => '#{basedir}/result_file2',
        source  => [ '#{basedir}/file2', '#{basedir}/file1' ],
        require => [ File['#{basedir}/file1'], File['#{basedir}/file2'] ],
        order   => '01',
      }
      concat::fragment { '3':
        target  => '#{basedir}/result_file3',
        source  => [ '#{basedir}/file1', '#{basedir}/file2' ],
        require => [ File['#{basedir}/file1'], File['#{basedir}/file2'] ],
        order   => '01',
      }
    EOS

    it 'applies the manifest twice with no stderr' do
      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)
    end
    describe file("#{basedir}/result_file1") do
      it { is_expected.to be_file }
      its(:content) do
        is_expected.to match 'file1 contents'
      end
      its(:content) do
        is_expected.not_to match 'file2 contents'
      end
    end
    describe file("#{basedir}/result_file2") do
      it { is_expected.to be_file }
      its(:content) do
        is_expected.to match 'file2 contents'
      end
      its(:content) do
        is_expected.not_to match 'file1 contents'
      end
    end
    describe file("#{basedir}/result_file3") do
      it { is_expected.to be_file }
      its(:content) do
        is_expected.to match 'file1 contents'
      end
      its(:content) do
        is_expected.not_to match 'file2 contents'
      end
    end
  end

  context 'should fail if no match on source.' do
    pp = <<-EOS
      concat { '#{basedir}/fail_no_source':
        owner   => '#{username}',
        group   => '#{groupname}',
        mode    => '0644',
      }

      concat::fragment { '1':
        target  => '#{basedir}/fail_no_source',
        source => [ '#{basedir}/nofilehere', '#{basedir}/nothereeither' ],
        order   => '01',
      }
    EOS

    it 'applies the manifest with resource failures' do
      expect(apply_manifest(pp, catch_failures: true).stderr).to match(%r{Failed to generate additional resources using 'eval_generate'})
    end
    describe file("#{basedir}/fail_no_source") do
      # FIXME: Serverspec::Type::File doesn't support exists? for some reason. so... hack.
      it { is_expected.not_to be_directory }
    end
  end
end
