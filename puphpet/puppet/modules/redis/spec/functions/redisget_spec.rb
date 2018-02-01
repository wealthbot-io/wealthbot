require 'spec_helper'
require 'mock_redis'
require 'redis'

REDIS_URL='redis://localhost:6379'
LOCAL_BROKEN_URL='redis://localhost:1234'
REMOTE_BROKEN_URL='redis://redis.example.com:1234'

describe 'redisget' do
  context 'should error if connection to remote redis server cannot be made and no default is specified' do
    it { is_expected.to run.with_params('nonexistent_key', REMOTE_BROKEN_URL).and_raise_error(Puppet::Error, /connection to redis server failed - getaddrinfo/) }
  end

  context 'should return default value if connection to remote redis server cannot be made and default is specified' do
    it { is_expected.to run.with_params('nonexistent_key', REMOTE_BROKEN_URL, 'default_value').and_return('default_value') }
  end

  context 'should error if connection to local redis server cannot be made and no default is specified' do
    it { is_expected.to run.with_params('nonexistent_key', LOCAL_BROKEN_URL).and_raise_error(Puppet::Error, /connection to redis server failed - Error connecting to Redis on localhost:1234/) }
  end

  context 'should return default value if connection to local redis server cannot be made and default is specified' do
    it { is_expected.to run.with_params('nonexistent_key', LOCAL_BROKEN_URL, 'default_value').and_return('default_value') }
  end

  context 'should return nil if key does not exist and no default is specified' do
    before {
      mr = MockRedis.new
      Redis.stubs(:new).returns(mr)
    }
    it { is_expected.to run.with_params('nonexistent_key', REDIS_URL).and_return(nil) }
  end

  context 'should return the default value if specified and key does not exist' do
    before {
      mr = MockRedis.new
      Redis.stubs(:new).returns(mr)
    }
    it { is_expected.to run.with_params('nonexistent_key', REDIS_URL, 'default_value').and_return('default_value') }
  end

  context 'should return the value of the specified key' do
    before {
      mr = MockRedis.new
      Redis.stubs(:new).returns(mr)
      mr.set('key', 'value')
    }
    it { is_expected.to run.with_params('key', REDIS_URL).and_return('value') }
  end

  context 'should return the value of the specified key and not the default' do
    before {
      mr = MockRedis.new
      Redis.stubs(:new).returns(mr)
      mr.set('key', 'value')
    }
    it { is_expected.to run.with_params('key', REDIS_URL, 'default_value').and_return('value') }
  end

  describe 'with incorrect arguments' do
    context 'with no argument specified' do
      it { is_expected.to run.with_params().and_raise_error(Puppet::ParseError, /wrong number of arguments/i) }
    end

    context 'with only one argument specified' do
      it { is_expected.to run.with_params('some_key').and_raise_error(Puppet::ParseError, /wrong number of arguments/i) }
    end

    context 'with more than three arguments specified' do
      it { is_expected.to run.with_params('way', 'too', 'many', 'args').and_raise_error(Puppet::ParseError, /wrong number of arguments/i) }
    end
  end

  describe 'when an invalid type (non-string) is specified' do
    before(:each) {
      mr = MockRedis.new
      Redis.stubs(:new).returns(mr)
    }
    [{ 'ha' => 'sh'}, true, 1, ['an', 'array']].each do |p|
      context "specifing first parameter as <#{p}>" do
        it { is_expected.to run.with_params(p, REDIS_URL).and_raise_error(Puppet::ParseError, /wrong argument type/i) }
      end

      context "specifing second parameter as <#{p}>" do
        it { is_expected.to run.with_params('valid', p).and_raise_error(Puppet::ParseError, /wrong argument type/i) }
      end

      context "specifing third parameter as <#{p}>" do
        it { is_expected.to run.with_params('valid', p).and_raise_error(Puppet::ParseError, /wrong argument type/i) }
      end
    end
  end
end
