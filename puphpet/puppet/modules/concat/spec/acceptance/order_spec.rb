require 'spec_helper_acceptance'

describe 'concat order' do
  basedir = default.tmpdir('concat')

  context '=> ' do
    shared_examples 'sortby' do |order_by, match_output|
      pp = <<-EOS
      concat { '#{basedir}/foo':
        order => '#{order_by}'
      }
      concat::fragment { '1':
        target  => '#{basedir}/foo',
        content => 'string1',
        order   => '1',
      }
      concat::fragment { '2':
        target  => '#{basedir}/foo',
        content => 'string2',
        order   => '2',
      }
      concat::fragment { '10':
        target  => '#{basedir}/foo',
        content => 'string10',
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
      it_behaves_like 'sortby', 'alpha', %r{string1string10string2}
    end

    describe 'numeric' do
      it_behaves_like 'sortby', 'numeric', %r{string1string2string10}
    end
  end
end # concat order
