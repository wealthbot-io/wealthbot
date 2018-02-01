require 'spec_helper'

describe "the apache_pw_hash function" do
  let(:scope) { PuppetlabsSpec::PuppetInternals.scope }

  it "should exist" do
    expect(Puppet::Parser::Functions.function("apache_pw_hash")).to eq("function_apache_pw_hash")
  end

  it "should raise a ParseError if there is less than 1 arguments" do
    expect { scope.function_apache_pw_hash([]) }.to( raise_error(Puppet::ParseError))
  end

  it "should raise an Puppet::ParseError if argument is an empty string" do
    expect { scope.function_apache_pw_hash(['']) }.to( raise_error(Puppet::ParseError))
  end

  context "when argument is not a string" do
    it { expect { scope.function_apache_pw_hash([1]) }.to( raise_error(Puppet::ParseError)) }
    it { expect { scope.function_apache_pw_hash([true]) }.to( raise_error(Puppet::ParseError)) }
    it { expect { scope.function_apache_pw_hash([{}]) }.to( raise_error(Puppet::ParseError)) }
    it { expect { scope.function_apache_pw_hash([[]]) }.to( raise_error(Puppet::ParseError)) }
  end

  it "should raise an Puppet::ParseError if argument is not a string" do
    expect { scope.function_apache_pw_hash([1]) }.to( raise_error(Puppet::ParseError))
  end

  it "should return proper hash" do
    expect(scope.function_apache_pw_hash(['test'])).to(eq('{SHA}qUqP5cyxm6YcTAhz05Hph5gvu9M='))
  end
end
