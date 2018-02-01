require 'spec_helper'

describe 'validate_nodejs_version' do
  it do
    expect {
      run.with_params('v0.6')
    }.to raise_error(Puppet::ParseError, /All NodeJS versions below `v0.10.0` are not supported!/)
  end

it do
    expect {
      run.with_params('v0.10')
    }.to raise_error(Puppet::ParseError, /All NodeJS versions below `v0.10.0` are not supported!/)
  end
end
