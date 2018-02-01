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
when 'Solaris'
  username = 'root'
  groupname = 'root'
else
  username = 'root'
  groupname = 'root'
end

describe 'basic concat test' do
  basedir = default.tmpdir('concat')

  shared_examples 'successfully_applied' do |pp|
    it 'applies the manifest twice with no stderr' do
      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)
    end
  end

  context 'owner/group root' do
    before(:all) do
      pp = <<-EOS
        file { '#{basedir}':
          ensure => directory,
        }
      EOS
      apply_manifest(pp)
    end
    pp = <<-EOS
      concat { '#{basedir}/file':
        owner => '#{username}',
        group => '#{groupname}',
        mode  => '0644',
      }

      concat::fragment { '1':
        target  => '#{basedir}/file',
        content => '1',
        order   => '01',
      }

      concat::fragment { '2':
        target  => '#{basedir}/file',
        content => '2',
        order   => '02',
      }
    EOS

    it_behaves_like 'successfully_applied', pp

    describe file("#{basedir}/file") do
      it { is_expected.to be_file }
      it { is_expected.to be_owned_by username }
      it('is group', unless: (fact('osfamily') == 'windows')) { is_expected.to be_grouped_into groupname }
      it('is mode', unless: (fact('osfamily') == 'AIX' || fact('osfamily') == 'windows')) {
        is_expected.to be_mode 644
      }
      its(:content) do
        is_expected.to match '1'
      end
      its(:content) do
        is_expected.to match '2'
      end
    end
  end

  context 'ensure works when set to present with path set' do
    before(:all) do
      pp = <<-EOS
        file { '#{basedir}':
          ensure => directory,
        }
        EOS
      apply_manifest(pp)
    end
    pp = "
      concat { 'file':
        ensure => present,
        path   => '#{basedir}/file',
        mode   => '0644',
      }
      concat::fragment { '1':
        target  => 'file',
        content => '1',
        order   => '01',
      }
    "

    it_behaves_like 'successfully_applied', pp

    describe file("#{basedir}/file") do
      it { is_expected.to be_file }
      it('is mode', unless: (fact('osfamily') == 'AIX' || fact('osfamily') == 'windows')) {
        is_expected.to be_mode 644
      }
      its(:content) { is_expected.to match '1' }
    end
  end
  context 'works when set to absent with path set' do
    before(:all) do
      pp = <<-EOS
        file { '#{basedir}':
          ensure => directory,
        }
        EOS
      apply_manifest(pp)
    end
    pp = "
      concat { 'file':
        ensure => absent,
        path   => '#{basedir}/file',
        mode   => '0644',
      }
      concat::fragment { '1':
        target  => 'file',
        content => '1',
        order   => '01',
      }
    "

    it 'applies the manifest twice with no stderr' do
      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)
    end

    describe file("#{basedir}/file") do
      it { is_expected.not_to be_file }
    end
  end
  context 'ensure works when set to present with path that has special characters' do
    filename = (fact('osfamily') == 'windows') ? 'file(1)' : 'file(1:2)'

    before(:all) do
      pp = <<-EOS
        file { '#{basedir}':
          ensure => directory,
        }
        EOS
      apply_manifest(pp)
    end
    pp = "
      concat { '#{filename}':
        ensure => present,
        path   => '#{basedir}/#{filename}',
        mode   => '0644',
      }
      concat::fragment { '1':
        target  => '#{filename}',
        content => '1',
        order   => '01',
      }
    "

    it_behaves_like 'successfully_applied', pp

    describe file("#{basedir}/#{filename}") do
      it { is_expected.to be_file }
      it('is mode', unless: (fact('osfamily') == 'AIX' || fact('osfamily') == 'windows')) {
        is_expected.to be_mode 644
      }
      its(:content) { is_expected.to match '1' }
    end
  end
  context 'ensure noop properly' do
    before(:all) do
      pp = <<-EOS
        file { '#{basedir}':
          ensure => directory,
        }
        EOS
      apply_manifest(pp)
    end
    pp = "
      concat { 'file':
        ensure => present,
        path   => '#{basedir}/file',
        mode   => '0644',
        noop   => true,
      }
      concat::fragment { '1':
        target  => 'file',
        content => '1',
        order   => '01',
      }
    "

    it_behaves_like 'successfully_applied', pp

    describe file("#{basedir}/file") do
      it { is_expected.not_to be_file }
    end
  end
end
