#! /usr/bin/env ruby -S rspec

require 'spec_helper'

describe Puppet::Parser::Functions.function(:mysql_deepmerge) do
  let(:scope) { PuppetlabsSpec::PuppetInternals.scope }

  describe 'when calling mysql_deepmerge from puppet' do
    it 'does not compile when no arguments are passed' do
      skip('Fails on 2.6.x, see bug #15912') if Puppet.version =~ %r{^2\.6\.}
      Puppet[:code] = '$x = mysql_deepmerge()'
      expect {
        scope.compiler.compile
      }.to raise_error(Puppet::ParseError, %r{wrong number of arguments})
    end

    it 'does not compile when 1 argument is passed' do
      skip('Fails on 2.6.x, see bug #15912') if Puppet.version =~ %r{^2\.6\.}
      Puppet[:code] = "$my_hash={'one' => 1}\n$x = mysql_deepmerge($my_hash)"
      expect {
        scope.compiler.compile
      }.to raise_error(Puppet::ParseError, %r{wrong number of arguments})
    end
  end

  describe 'when calling mysql_deepmerge on the scope instance' do
    it 'accepts empty strings as puppet undef' do
      expect { new_hash = scope.function_mysql_deepmerge([{}, '']) }.not_to raise_error
    end

    it 'is able to mysql_deepmerge two hashes' do
      new_hash = scope.function_mysql_deepmerge([{ 'one' => '1', 'two' => '1' }, { 'two' => '2', 'three' => '2' }])
      expect(new_hash['one']).to   eq('1')
      expect(new_hash['two']).to   eq('2')
      expect(new_hash['three']).to eq('2')
    end

    it 'mysql_deepmerges multiple hashes' do
      hash = scope.function_mysql_deepmerge([{ 'one' => 1 }, { 'one' => '2' }, { 'one' => '3' }])
      expect(hash['one']).to eq('3')
    end

    it 'accepts empty hashes' do
      expect(scope.function_mysql_deepmerge([{}, {}, {}])).to eq({})
    end

    it 'mysql_deepmerges subhashes' do
      hash = scope.function_mysql_deepmerge([{ 'one' => 1 }, { 'two' => 2, 'three' => { 'four' => 4 } }])
      expect(hash['one']).to eq(1)
      expect(hash['two']).to eq(2)
      expect(hash['three']).to eq('four' => 4)
    end

    it 'appends to subhashes' do
      hash = scope.function_mysql_deepmerge([{ 'one' => { 'two' => 2 } }, { 'one' => { 'three' => 3 } }])
      expect(hash['one']).to eq('two' => 2, 'three' => 3)
    end

    it 'appends to subhashes 2' do
      hash = scope.function_mysql_deepmerge([{ 'one' => 1, 'two' => 2, 'three' => { 'four' => 4 } }, { 'two' => 'dos', 'three' => { 'five' => 5 } }])
      expect(hash['one']).to eq(1)
      expect(hash['two']).to eq('dos')
      expect(hash['three']).to eq('four' => 4, 'five' => 5)
    end

    it 'appends to subhashes 3' do
      hash = scope.function_mysql_deepmerge([{ 'key1' => { 'a' => 1, 'b' => 2 }, 'key2' => { 'c' => 3 } }, { 'key1' => { 'b' => 99 } }])
      expect(hash['key1']).to eq('a' => 1, 'b' => 99)
      expect(hash['key2']).to eq('c' => 3)
    end

    it 'equates keys mod dash and underscore' do
      hash = scope.function_mysql_deepmerge([{ 'a-b-c' => 1 }, { 'a_b_c' => 10 }])
      expect(hash['a_b_c']).to eq(10)
      expect(hash).not_to have_key('a-b-c')
    end

    it 'keeps style of the last when keys are euqal mod dash and underscore' do
      hash = scope.function_mysql_deepmerge([{ 'a-b-c' => 1, 'b_c_d' => { 'c-d-e' => 2, 'e-f-g' => 3 } }, { 'a_b_c' => 10, 'b-c-d' => { 'c_d_e' => 12 } }])
      expect(hash['a_b_c']).to eq(10)
      expect(hash).not_to have_key('a-b-c')
      expect(hash['b-c-d']).to eq('e-f-g' => 3, 'c_d_e' => 12)
      expect(hash).not_to have_key('b_c_d')
    end
  end
end
