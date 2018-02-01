# run a test task
require 'spec_helper_acceptance'

describe 'apache tasks', if: pe_install? && puppet_version =~ %r{(5\.\d\.\d)} do
  describe 'reload' do
    it 'execute reload' do
      pp = <<-EOS
      class { 'apache':
        default_vhost => false,
      }
      apache::listen { '9090':}
      EOS

      apply_manifest(pp, :catch_failures => true)

      result = run_task(task_name: 'apache', params: 'action=reload')
      expect_multiple_regexes(result: result, regexes: [%r{reload successful}, %r{Job completed. 1/1 nodes succeeded}])
    end
  end
end
