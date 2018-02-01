require 'spec_helper_system'

describe 'epel class:' do
  context puppet_agent do
    its(:stderr) { is_expected.to be_empty }
    its(:exit_code) { is_expected.not_to == 1 }
  end

  # Verify the operatingsystemmajrelease fact is working
  context shell 'facter --puppet operatingsystemmajrelease' do
    its(:stdout) { is_expected.not_to be_empty }
    its(:stderr) { is_expected.to be_empty }
    its(:exit_code) { is_expected.to be_zero }
  end

  pp = "class { 'epel': }"

  context puppet_apply pp do
    its(:stderr) { is_expected.to be_empty }
    its(:refresh) { is_expected.to be_nil }
    its(:exit_code) { is_expected.to be_zero }
  end

  context 'test EPEL repo presence' do
    facts = node.facts

    # Only test for EPEL's presence if not Fedora
    if facts['operatingsystem'] !~ %r{Fedora}
      context shell '/usr/bin/yum-config-manager epel | grep -q "\[epel\]"' do
        its(:exit_code) { is_expected.to be_zero }
      end
    end
  end
end
