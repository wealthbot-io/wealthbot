require 'rspec'
require 'puppet'
require 'rspec-puppet'
require 'mocha'

PROJECT_ROOT = File.expand_path('..', File.dirname(__FILE__))
$LOAD_PATH.unshift(File.join(PROJECT_ROOT, 'lib'))

fixture_path = File.expand_path(File.join('spec', 'fixtures'), PROJECT_ROOT)

RSpec.configure do |config|
  config.mock_with :mocha

  # ---
  # Configuration for puppet-rspec

  config.module_path = File.join(fixture_path, 'modules')
  config.manifest_dir = File.join(fixture_path, 'manifests')
  config.environmentpath = File.expand_path(File.join(Dir.pwd, 'spec'))
end

# ---
# Add the fixture module libdirs to the modulepath

$LOAD_PATH.concat(Dir.glob(File.join(fixture_path, 'modules', '*', 'lib')))
