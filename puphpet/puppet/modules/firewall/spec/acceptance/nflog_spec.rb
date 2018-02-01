require 'spec_helper_acceptance'

describe 'nflog on older OSes', if: fact('iptables_version') < '1.3.7' do # rubocop:disable RSpec/MultipleDescribes : Describes are clearly seperate
  pp1 = <<-EOS
      class {'::firewall': }
      firewall { '503 - test':
        jump  => 'NFLOG',
        proto => 'all',
        nflog_group => 3,
      }
  EOS
  it 'throws an error' do
    apply_manifest(pp1, acceptable_error_codes: [0])
  end
end

describe 'nflog', unless: fact('iptables_version') < '1.3.7' do
  describe 'nflog_group' do
    let(:group) { 3 }

    it 'applies' do
      pp2 = <<-EOS
        class {'::firewall': }
        firewall { '503 - test': jump  => 'NFLOG', proto => 'all', nflog_group => #{group}}
      EOS
      apply_manifest(pp2, catch_failures: true)
    end

    it 'contains the rule' do
      shell('iptables-save') do |r|
        expect(r.stdout).to match(%r{NFLOG --nflog-group #{group}})
      end
    end
  end

  describe 'nflog_prefix' do
    let(:prefix) { 'TEST PREFIX' }

    it 'applies' do
      pp3 = <<-EOS
      class {'::firewall': }
      firewall { '503 - test': jump  => 'NFLOG', proto => 'all', nflog_prefix => '#{prefix}'}
      EOS
      apply_manifest(pp3, catch_failures: true)
    end

    it 'contains the rule' do
      shell('iptables-save') do |r|
        expect(r.stdout).to match(%r{NFLOG --nflog-prefix +"#{prefix}"})
      end
    end
  end

  describe 'nflog_range' do
    let(:range) { 16 }

    it 'applies' do
      pp4 = <<-EOS
        class {'::firewall': }
        firewall { '503 - test': jump  => 'NFLOG', proto => 'all', nflog_range => #{range}}
      EOS
      apply_manifest(pp4, catch_failures: true)
    end

    it 'contains the rule' do
      shell('iptables-save') do |r|
        expect(r.stdout).to match(%r{NFLOG --nflog-range #{range}})
      end
    end
  end

  describe 'nflog_threshold' do
    let(:threshold) { 2 }

    it 'applies' do
      pp5 = <<-EOS
        class {'::firewall': }
        firewall { '503 - test': jump  => 'NFLOG', proto => 'all', nflog_threshold => #{threshold}}
      EOS
      apply_manifest(pp5, catch_failures: true)
    end

    it 'contains the rule' do
      shell('iptables-save') do |r|
        expect(r.stdout).to match(%r{NFLOG --nflog-threshold #{threshold}})
      end
    end
  end

  describe 'multiple rules' do
    let(:threshold) { 2 }
    let(:group) { 3 }

    it 'applies' do
      pp6 = <<-EOS
        class {'::firewall': }
        firewall { '503 - test': jump  => 'NFLOG', proto => 'all', nflog_threshold => #{threshold}, nflog_group => #{group}}
      EOS
      apply_manifest(pp6, catch_failures: true)
    end

    it 'contains the rules' do
      shell('iptables-save') do |r|
        expect(r.stdout).to match(%r{NFLOG --nflog-group #{group} --nflog-threshold #{threshold}})
      end
    end
  end
end
