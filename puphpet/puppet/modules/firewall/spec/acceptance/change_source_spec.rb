require 'spec_helper_acceptance'

describe 'changing the source' do
  before :all do
    iptables_flush_all_tables
    ip6tables_flush_all_tables
  end

  describe 'when unmanaged rules exist' do
    pp1 = <<-EOS
          class { '::firewall': }
          firewall { '101 test source changes':
            proto  => tcp,
            port   => '101',
            action => accept,
            source => '8.0.0.1',
          }
          firewall { '100 test source static':
            proto  => tcp,
            port   => '100',
            action => accept,
            source => '8.0.0.2',
          }
    EOS
    it 'applies with 8.0.0.1 first' do
      apply_manifest(pp1, catch_failures: true)
    end

    it 'adds a unmanaged rule without a comment' do
      shell('iptables -A INPUT -t filter -s 8.0.0.3/32 -p tcp -m multiport --ports 102 -j ACCEPT')
      expect(shell('iptables-save').stdout).to match(%r{-A INPUT -s 8\.0\.0\.3(\/32)? -p tcp -m multiport --ports 102 -j ACCEPT})
    end

    it 'contains the changable 8.0.0.1 rule' do
      shell('iptables-save') do |r|
        expect(r.stdout).to match(%r{-A INPUT -s 8\.0\.0\.1(\/32)? -p tcp -m multiport --ports 101 -m comment --comment "101 test source changes" -j ACCEPT})
      end
    end
    it 'contains the static 8.0.0.2 rule' do # rubocop:disable RSpec/RepeatedExample : The values being matched differ
      shell('iptables-save') do |r|
        expect(r.stdout).to match(%r{-A INPUT -s 8\.0\.0\.2(\/32)? -p tcp -m multiport --ports 100 -m comment --comment "100 test source static" -j ACCEPT})
      end
    end

    pp2 = <<-EOS
          class { '::firewall': }
          firewall { '101 test source changes':
            proto  => tcp,
            port   => '101',
            action => accept,
            source => '8.0.0.4',
          }
    EOS
    it 'changes to 8.0.0.4 second' do
      expect(apply_manifest(pp2, catch_failures: true).stdout)
        .to match(%r{Notice: \/Stage\[main\]\/Main\/Firewall\[101 test source changes\]\/source: source changed '8\.0\.0\.1\/32' to '8\.0\.0\.4\/32'})
    end

    it 'does not contain the old changing 8.0.0.1 rule' do
      shell('iptables-save') do |r|
        expect(r.stdout).not_to match(%r{8\.0\.0\.1})
      end
    end
    it 'contains the staic 8.0.0.2 rule' do # rubocop:disable RSpec/RepeatedExample : The values being matched differ
      shell('iptables-save') do |r|
        expect(r.stdout).to match(%r{-A INPUT -s 8\.0\.0\.2(\/32)? -p tcp -m multiport --ports 100 -m comment --comment "100 test source static" -j ACCEPT})
      end
    end
    it 'contains the changing new 8.0.0.4 rule' do
      shell('iptables-save') do |r|
        expect(r.stdout).to match(%r{-A INPUT -s 8\.0\.0\.4(\/32)? -p tcp -m multiport --ports 101 -m comment --comment "101 test source changes" -j ACCEPT})
      end
    end
  end
end
