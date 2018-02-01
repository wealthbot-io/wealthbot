# run a test task
require 'spec_helper_acceptance'

describe 'apt tasks' do
  describe 'update and upgrade', if: pe_install? && puppet_version =~ %r{(5\.\d\.\d)} && fact_on(master, 'osfamily') == 'Debian' do
    it 'execute arbitary sql' do
      result = run_task(task_name: 'apt', params: 'action=update')
      expect_multiple_regexes(result: result, regexes: [%r{Reading package lists}, %r{Job completed. 1/1 nodes succeeded}])
    end
  end
end
