require 'spec_helper'

describe 'elasticsearch::script', :type => 'define' do
  let(:title) { 'foo' }
  let(:pre_condition) do
    %(
      class { "elasticsearch":
        config => {
          "node" => {"name" => "test" }
        }
      }
    )
  end

  on_supported_os(
    :hardwaremodels => ['x86_64'],
    :supported_os => [
      {
        'operatingsystem' => 'CentOS',
        'operatingsystemrelease' => ['6']
      }
    ]
  ).each do |os, facts|
    context "on #{os}" do
      let(:facts) { facts.merge(
        :scenario => '',
        :common => ''
      ) }

      describe 'missing parent class' do
        let(:pre_condition) {}
        it { should_not compile }
      end

      describe 'adding script files' do
        let(:params) do {
          :ensure => 'present',
          :source => 'puppet:///path/to/foo.groovy'
        } end

        it { should contain_elasticsearch__script('foo') }
        it { should contain_file('/usr/share/elasticsearch/scripts/foo.groovy')
          .with(
            :source => 'puppet:///path/to/foo.groovy',
            :ensure => 'present'
          ) }
      end

      describe 'adding script directories' do
        let(:params) do {
          :ensure  => 'directory',
          :source  => 'puppet:///path/to/my_scripts',
          :recurse => 'remote'
        } end

        it { should contain_elasticsearch__script('foo') }
        it { should contain_file(
          '/usr/share/elasticsearch/scripts/my_scripts'
        ).with(
          :ensure  => 'directory',
          :source  => 'puppet:///path/to/my_scripts',
          :recurse => 'remote'
        ) }
      end

      describe 'removing scripts' do
        let(:params) do {
          :ensure => 'absent',
          :source => 'puppet:///path/to/foo.groovy'
        } end

        it { should contain_elasticsearch__script('foo') }
        it { should contain_file('/usr/share/elasticsearch/scripts/foo.groovy')
          .with(
            :source => 'puppet:///path/to/foo.groovy',
            :ensure => 'absent'
          ) }
      end
    end
  end
end
