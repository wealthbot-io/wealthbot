# run a test task
require 'spec_helper_acceptance'

describe 'mysql tasks' do
  describe 'execute some sql', if: pe_install? && puppet_version =~ %r{(5\.\d\.\d)} do
    pp = <<-EOS
        class { 'mysql::server': root_password => 'password' }
        mysql::db { 'spec1':
          user     => 'root1',
          password => 'password',
        }
    EOS

    it "sets up a mysql instance" do
      apply_manifest(pp, catch_failures: true)
    end

    it 'execute arbitary sql' do
      result = run_task(task_name: 'mysql::sql', params: 'sql="show databases;" password=password')
      expect_multiple_regexes(result: result, regexes: [%r{information_schema}, %r{performance_schema}, %r{Job completed. 1/1 nodes succeeded}])
    end
  end
end
