#! /usr/bin/env ruby
require 'spec_helper'

describe Puppet::Type.type(:postgresql_conn_validator) do
  before do
    @provider_class = described_class.provide(:simple) { mk_resource_methods }
    @provider_class.stub(:suitable?).and_return true
    described_class.stub(:defaultprovider).and_return @provider_class
  end

  describe "when validating attributes" do
    [:name, :db_name, :db_username, :command, :host, :port, :connect_settings, :sleep, :tries, :psql_path].each do |param|
      it "should have a #{param} parameter" do
        expect(described_class.attrtype(param)).to eq(:param)
      end
    end
  end

  describe "when validating values" do
    describe "tries and sleep" do
      [:tries, :sleep, :port].each do |param|
        it "#{param} should be able to cast value as integer" do
          expect { described_class.new(:name => 'test', param => '1') }.to_not raise_error
          expect { described_class.new(:name => 'test', param => 1) }.to_not raise_error
        end
        it "#{param} should not accept non-numeric string" do
          expect { described_class.new(:name => 'test', param => 'test') }.to raise_error Puppet::ResourceError
        end
      end
    end
    describe "connect_settings" do
      it "should accept a hash" do
        expect { described_class.new(:name => 'test', :connect_settings => { "PGPASSWORD" => "test1" }) }.to_not raise_error
      end
    end
    describe "port" do
      it "does not accept a word" do
        expect { described_class.new(:name => 'test', :port => 'test')}.to raise_error Puppet::Error
      end
    end
  end
end
