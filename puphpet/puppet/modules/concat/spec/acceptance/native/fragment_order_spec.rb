require 'spec_helper_acceptance'

describe 'concat_fragment order' do
  basedir = default.tmpdir('concat')

  context '=> reverse order' do
    shared_examples 'order_by' do |order_by, match_output|
      pp = <<-EOS
      concat_file { '#{basedir}/foo':
          order => '#{order_by}'
      }
      concat_fragment { '1':
        target  => '#{basedir}/foo',
        content => 'string1',
        order   => '15',
      }
      concat_fragment { '2':
        target  => '#{basedir}/foo',
        content => 'string2',
        # default order 10
      }
      concat_fragment { '3':
        target  => '#{basedir}/foo',
        content => 'string3',
        order   => '1',
      }
      EOS

      it 'applies the manifest twice with no stderr' do
        apply_manifest(pp, catch_failures: true)
        apply_manifest(pp, catch_changes: true)
      end

      describe file("#{basedir}/foo") do
        it { is_expected.to be_file }
        its(:content) { is_expected.to match match_output }
      end
    end
    describe 'alpha' do
      it_behaves_like 'order_by', 'alpha', %r{string3string2string1}
    end
    describe 'numeric' do
      it_behaves_like 'order_by', 'numeric', %r{string3string2string1}
    end
  end

  context '=> normal order' do
    pp = <<-EOS
      concat_file { '#{basedir}/foo': }
      concat_fragment { '1':
        target  => '#{basedir}/foo',
        content => 'string1',
        order   => '01',
      }
      concat_fragment { '2':
        target  => '#{basedir}/foo',
        content => 'string2',
        order   => '02'
      }
      concat_fragment { '3':
        target  => '#{basedir}/foo',
        content => 'string3',
        order   => '03',
      }
    EOS

    it 'applies the manifest twice with no stderr' do
      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)
    end

    describe file("#{basedir}/foo") do
      it { is_expected.to be_file }
      its(:content) { is_expected.to match %r{string1string2string3} }
    end
  end
end
