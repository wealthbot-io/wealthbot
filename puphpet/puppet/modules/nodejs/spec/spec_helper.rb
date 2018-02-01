require 'rubygems'
require 'puppetlabs_spec_helper/module_spec_helper'
require 'webmock/rspec'
require 'rspec'

WebMock.disable_net_connect!()

nodejs_response = <<JSON
[
{"version":"v6.3.1","date":"2016-07-21","files":["headers","linux-arm64","linux-armv6l","linux-armv7l","linux-ppc64le","linux-x64","linux-x86","osx-x64-pkg","osx-x64-tar","src","sunos-x64","sunos-x86","win-x64-msi","win-x86-msi"],"npm":"3.10.3","v8":"5.0.71.57","uv":"1.9.1","zlib":"1.2.8","openssl":"1.0.2h","modules":"48","lts":false},
{"version":"v6.3.0","date":"2016-07-06","files":["headers","linux-arm64","linux-armv6l","linux-armv7l","linux-ppc64le","linux-x64","linux-x86","osx-x64-pkg","osx-x64-tar","src","sunos-x64","sunos-x86","win-x64-msi","win-x86-msi"],"npm":"3.10.3","v8":"5.0.71.52","uv":"1.9.1","zlib":"1.2.8","openssl":"1.0.2h","modules":"48","lts":false},
{"version":"v6.2.2","date":"2016-06-16","files":["headers","linux-arm64","linux-armv6l","linux-armv7l","linux-ppc64le","linux-x64","linux-x86","osx-x64-pkg","osx-x64-tar","src","sunos-x64","sunos-x86","win-x64-msi","win-x86-msi"],"npm":"3.9.5","v8":"5.0.71.52","uv":"1.9.1","zlib":"1.2.8","openssl":"1.0.2h","modules":"48","lts":false},
{"version":"v6.2.1","date":"2016-06-02","files":["headers","linux-arm64","linux-armv6l","linux-armv7l","linux-ppc64le","linux-x64","linux-x86","osx-x64-pkg","osx-x64-tar","src","sunos-x64","sunos-x86","win-x64-msi","win-x86-msi"],"npm":"3.9.3","v8":"5.0.71.52","uv":"1.9.1","zlib":"1.2.8","openssl":"1.0.2h","modules":"48","lts":false},
{"version":"v6.2.0","date":"2016-05-17","files":["headers","linux-arm64","linux-armv6l","linux-armv7l","linux-ppc64le","linux-x64","linux-x86","osx-x64-pkg","osx-x64-tar","src","sunos-x64","sunos-x86","win-x64-msi","win-x86-msi"],"npm":"3.8.9","v8":"5.0.71.47","uv":"1.9.1","zlib":"1.2.8","openssl":"1.0.2h","modules":"48","lts":false},
{"version":"v6.1.0","date":"2016-05-05","files":["headers","linux-arm64","linux-armv6l","linux-armv7l","linux-ppc64le","linux-x64","linux-x86","osx-x64-pkg","osx-x64-tar","src","sunos-x64","sunos-x86","win-x64-msi","win-x86-msi"],"npm":"3.8.6","v8":"5.0.71.35","uv":"1.9.0","zlib":"1.2.8","openssl":"1.0.2h","modules":"48","lts":false},
{"version":"v6.0.0","date":"2016-04-26","files":["headers","linux-arm64","linux-armv6l","linux-armv7l","linux-ppc64le","linux-x64","linux-x86","osx-x64-pkg","osx-x64-tar","src","sunos-x64","sunos-x86","win-x64-msi","win-x86-msi"],"npm":"3.8.6","v8":"5.0.71.35","uv":"1.9.0","zlib":"1.2.8","openssl":"1.0.2g","modules":"48","lts":false},
{"version":"v5.12.0","date":"2016-06-23","files":["headers","linux-arm64","linux-armv6l","linux-armv7l","linux-ppc64le","linux-x64","linux-x86","osx-x64-pkg","osx-x64-tar","src","sunos-x64","sunos-x86","win-x64-msi","win-x86-msi"],"npm":"3.8.6","v8":"4.6.85.32","uv":"1.8.0","zlib":"1.2.8","openssl":"1.0.2h","modules":"47","lts":false},
{"version":"v5.11.1","date":"2016-05-05","files":["headers","linux-arm64","linux-armv6l","linux-armv7l","linux-ppc64le","linux-x64","linux-x86","osx-x64-pkg","osx-x64-tar","src","sunos-x64","sunos-x86","win-x64-msi","win-x86-msi"],"npm":"3.8.6","v8":"4.6.85.31","uv":"1.8.0","zlib":"1.2.8","openssl":"1.0.2h","modules":"47","lts":false},
{"version":"v5.11.0","date":"2016-04-21","files":["headers","linux-arm64","linux-armv6l","linux-armv7l","linux-ppc64le","linux-x64","linux-x86","osx-x64-pkg","osx-x64-tar","src","sunos-x64","sunos-x86","win-x64-msi","win-x86-msi"],"npm":"3.8.6","v8":"4.6.85.31","uv":"1.8.0","zlib":"1.2.8","openssl":"1.0.2g","modules":"47","lts":false},
{"version":"v5.10.1","date":"2016-04-05","files":["headers","linux-arm64","linux-armv6l","linux-armv7l","linux-ppc64le","linux-x64","linux-x86","osx-x64-pkg","osx-x64-tar","src","sunos-x64","sunos-x86","win-x64-msi","win-x86-msi"],"npm":"3.8.3","v8":"4.6.85.31","uv":"1.8.0","zlib":"1.2.8","openssl":"1.0.2g","modules":"47","lts":false},
{"version":"v4.4.7","date":"2016-06-28","files":["headers","linux-arm64","linux-armv6l","linux-armv7l","linux-ppc64le","linux-x64","linux-x86","osx-x64-pkg","osx-x64-tar","src","sunos-x64","sunos-x86","win-x64-msi","win-x86-msi"],"npm":"2.15.8","v8":"4.5.103.36","uv":"1.8.0","zlib":"1.2.8","openssl":"1.0.2h","modules":"46","lts":"Argon"}
]
JSON

RSpec.configure do |config|
  config.before(:each) do
    stub_request(:get, "https://nodejs.org/dist/index.json")
      .to_return(:status => 200, :body => nodejs_response, :headers => {})
  end
end

$:.unshift File.join(File.dirname(__FILE__),  'fixtures', 'modules', 'stdlib', 'lib')
