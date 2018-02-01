require 'spec_helper'

describe 'redis' do
  context 'on Debian Wheezy' do

    let(:facts) {
      debian_wheezy_facts
    }

    context 'should set Wheezy specific values' do

      context 'should set redis rundir correctly to Wheezy requirements' do
        it { should contain_file('/var/run/redis').with('mode' => '2755') }
        it { should contain_file('/var/run/redis').with('group' => 'redis') }
      end
    end
  end

end
