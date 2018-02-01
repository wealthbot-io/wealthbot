require 'spec_helper_acceptance'

describe 'param based tests' do
  before :all do
    iptables_flush_all_tables
    ip6tables_flush_all_tables
  end

  ppm1 = <<-EOS
    firewall { '100 test':
      table     => 'raw',
      socket    => 'true',
      chain     => 'PREROUTING',
      jump      => 'LOG',
      log_level => 'debug',
    }
  EOS
  values = [2, 0]
  it 'test various params', unless: (default['platform'].match(%r{el-5}) || fact('operatingsystem') == 'SLES') do
    iptables_flush_all_tables

    values.each do |value|
      expect(apply_manifest(ppm1, catch_failures: true).exit_code).to eq(value)
    end
  end

  ppm2 = <<-EOS
    firewall { '998 log all':
      proto     => 'all',
      jump      => 'LOG',
      log_level => 'debug',
    }
  EOS
  values = [2, 0]
  it 'test log rule' do
    iptables_flush_all_tables

    values.each do |value|
      expect(apply_manifest(ppm2, catch_failures: true).exit_code).to eq(value)
    end
  end

  ppm3 = <<-EOS
    firewall { '004 log all INVALID packets':
      chain      => 'INPUT',
      proto      => 'all',
      ctstate    => 'INVALID',
      jump       => 'LOG',
      log_level  => '3',
      log_prefix => 'IPTABLES dropped invalid: ',
    }
  EOS
  ppm4 = <<-EOS
    firewall { '003 log all INVALID packets':
      chain      => 'INPUT',
      proto      => 'all',
      ctstate    => 'INVALID',
      jump       => 'LOG',
      log_level  => '3',
      log_prefix => 'IPTABLES dropped invalid: ',
    }
  EOS
  ppm5 = <<-EOS + "\n" + ppm4
      resources { 'firewall':
        purge => true,
      }
  EOS
  it 'test log rule - changing names' do # rubocop:disable RSpec/MultipleExpectations
    iptables_flush_all_tables

    expect(apply_manifest(ppm3, catch_failures: true).exit_code).to eq(2)
    expect(apply_manifest(ppm5, catch_failures: true).exit_code).to eq(2)
  end

  ppm6 = <<-EOS
    firewall { '004 with a chain':
      chain => 'INPUT',
      proto => 'all',
    }
  EOS
  ppm7 = <<-EOS
    firewall { '004 with a chain':
      chain => 'OUTPUT',
      proto => 'all',
    }
  EOS
  _ppm8 = <<-EOS + "\n" + ppm7
      resources { 'firewall':
        purge => true,
      }
  EOS
  it 'test chain - changing names' do
    iptables_flush_all_tables

    apply_manifest(ppm6, expect_changes: true)
    expect(apply_manifest(ppm7, expect_failures: true).stderr).to match(%r{is not supported})
  end

  ppm9 = <<-EOS
    firewall { '004 log all INVALID packets':
      chain      => 'INPUT',
      proto      => 'all',
      ctstate    => 'INVALID',
      jump       => 'LOG',
      log_level  => '3',
      log_prefix => 'IPTABLES dropped invalid: ',
    }
  EOS
  values = [2, 0]
  it 'test log rule - idempotent' do
    iptables_flush_all_tables

    values.each do |value|
      expect(apply_manifest(ppm9, catch_failures: true).exit_code).to eq(value)
    end
  end

  ppm10 = <<-EOS
    firewall { '997 block src ip range':
      chain     => 'INPUT',
      proto     => 'all',
      action    => 'drop',
      src_range => '10.0.0.1-10.0.0.10',
    }
  EOS
  values = [2, 0]
  it 'test src_range rule' do
    iptables_flush_all_tables

    values.each do |value|
      expect(apply_manifest(ppm10, catch_failures: true).exit_code).to eq(value)
    end
  end

  ppm11 = <<-EOS
    firewall { '998 block dst ip range':
      chain     => 'INPUT',
      proto     => 'all',
      action    => 'drop',
      dst_range => '10.0.0.2-10.0.0.20',
    }
  EOS
  values = [2, 0]
  it 'test dst_range rule' do
    iptables_flush_all_tables

    values.each do |value|
      expect(apply_manifest(ppm11, catch_failures: true).exit_code).to eq(value)
    end
  end
end
