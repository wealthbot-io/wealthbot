require 'spec_helper_acceptance'

# systcl settings are untestable in docker
unless default['hypervisor'] =~ /docker/
  describe 'redis::administration' do
    it 'should run successfully' do
      pp = <<-EOS
      include redis
      include redis::administration
      EOS

      # Apply twice to ensure no errors the second time.
      apply_manifest(pp, :catch_failures => true)
      apply_manifest(pp, :catch_changes => true)
    end
    it 'should set overcommit_memory to 1 in a seperate sysctl file' do
      shell('/bin/cat /proc/sys/vm/overcommit_memory') do |result|
        expect(result.stdout).to match(/^1$/)
      end
    end
    it 'should disable thp' do
      shell('/bin/cat /sys/kernel/mm/transparent_hugepage/enabled') do |result|
        expect(result.stdout).to match(/^always madvise \[never\]$/)
      end
    end
    it 'should set somaxconn to 65535' do
      shell('/bin/cat /proc/sys/net/core/somaxconn') do |result|
        expect(result.stdout).to match(/^65535$/)
      end
    end
    it 'should show no warnings about kernel settings in logs' do
      shell('timeout 1s redis-server --port 7777 --loglevel verbose', { :acceptable_exit_codes => [0,124] }) do |result|
        expect(result.stdout).not_to match(/WARNING/)
        expect(result.exit_code).to match(124)
      end
    end
  end
end
