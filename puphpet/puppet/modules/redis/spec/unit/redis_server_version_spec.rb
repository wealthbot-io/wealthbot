require 'spec_helper'

describe 'redis_server_version', type: :fact do
  before { Facter.clear }
  after { Facter.clear }

  it 'is 2.4.10 according to output' do
    Facter::Util::Resolution.stubs(:which).with('redis-server').returns('/usr/bin/redis-server')
    redis_server_2410_version = File.read(fixtures('facts', 'redis_server_2410_version'))
    Facter::Util::Resolution.stubs(:exec).with('redis-server -v').returns(redis_server_2410_version)
    expect(Facter.fact(:redis_server_version).value).to eq('2.4.10')
  end

  it 'is 2.8.19 according to output' do
    Facter::Util::Resolution.stubs(:which).with('redis-server').returns('/usr/bin/redis-server')
    redis_server_2819_version = File.read(fixtures('facts', 'redis_server_2819_version'))
    Facter::Util::Resolution.stubs(:exec).with('redis-server -v').returns(redis_server_2819_version)
    expect(Facter.fact(:redis_server_version).value).to eq('2.8.19')
  end

  it 'is 3.2.9 according to output' do
    Facter::Util::Resolution.stubs(:which).with('redis-server').returns('/usr/bin/redis-server')
    redis_server_3209_version = File.read(fixtures('facts', 'redis_server_3209_version'))
    Facter::Util::Resolution.stubs(:exec).with('redis-server -v').returns(redis_server_3209_version)
    expect(Facter.fact(:redis_server_version).value).to eq('3.2.9')
  end

  it 'is empty string if redis-server not installed' do
    Facter::Util::Resolution.stubs(:which).with('redis-server').returns(nil)
    expect(Facter.fact(:redis_server_version).value).to eq(nil)
  end
end
