require 'beaker-rspec'
require 'beaker/puppet_install_helper'
require 'beaker/module_install_helper'

UNSUPPORTED_PLATFORMS = [].freeze

run_puppet_install_helper
install_module
install_module_dependencies

# Install aditional modules for soft deps
install_module_from_forge('puppetlabs-apt', '>= 4.1.0 < 5.0.0') if fact('os.family') == 'Debian'
install_module_from_forge('garethr-erlang', '>= 0.3.0 < 1.0.0') if fact('os.family') == 'RedHat'

RSpec.configure do |c|
  # Readable test descriptions
  c.formatter = :documentation
  c.before :suite do
    hosts.each do |host|
      if fact('os.family') == 'RedHat' && fact('selinux') == 'true'
        # Make sure selinux is disabled so the tests work.
        on host, puppet('apply', '-e',
                        %("exec { 'setenforce 0': path   => '/bin:/sbin:/usr/bin:/usr/sbin', onlyif => 'which setenforce && getenforce | grep Enforcing', }"))
      end

      # Fake certs
      on host, 'echo "-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQDw1uXI+EAgxk4dOxArPqMNnnCQqmXeQ61XQQXoAgWWjRvY4LAJ
4ALoACYWFlWVKQkLLQfQ2YYM3vNDG/eYfL2tjhp9APeJrJYJEcbmr5COURtuIh/Z
fVKRgV5vtOMdlAHS0HFpk/DP1g520Da9wKwv2nDbfRui0y0ImPWz1uqK8wIDAQAB
AoGBAJ/cNMgyJ/bZSk5SvwfFWtuWWGdeA6IF0BBDo9T9SpJE9b/+XDshyywNtToh
9wq8Izmc2TxCtpzifBwGe1FnM0qvioQhwQwJsGa4JJFzSkIt/MgPmojzutnP59RJ
ozjCgAahX6xEkB6Kn/b2S6D7jgixyMTQ3FdPKq6lJVhXz6Q5AkEA+tSgg/xhrcTs
5W//hYZP/xjrE27xXzfkNrr/cAtVH5ZM2j2qqKFv3wEn9tAKrUvM+KNDhEjgLUSd
MjvvH5DYZQJBAPXNjsg4AG09KqWbvcbB2Cgaf6RRTAe+Hx6A7aB01OQohc5uF0ws
cPxZ8CE/KDbHtNSlx1MAvn8IOoitA06s5HcCQErP/mw/a3bjxHCOXh0aOWPxr7Ol
JHLs/bFhRuzJRINeVd/GAs+3DuHpu1y/ImAbuq/yKiIbhlmaHHSuMZ0tm40CQHHi
FzE0oR37pPKtwbOAxEFwZYsgD3XW5Fwhp/cbqjc7fyMxZqHoRUDl+pesx1j6FhIf
7MXMJnZ8vYHthwbAm+kCQHFH0HULyNcki4zEYSqiLaVMcbB7QybQmBBDA0mJR2HO
KYdYDeFG3kfeBOReFM9jOt39OBS/nNP0GXFBJU2ahpQ=
-----END RSA PRIVATE KEY-----" > /tmp/rabbitmq.key'
      on host, 'echo "-----BEGIN CERTIFICATE-----
MIIEYTCCAkmgAwIBAgIQVAIiKvJ6YmTSszWEfassuDANBgkqhkiG9w0BAQUFADCB
qjELMAkGA1UEBhMCVVMxEzARBgNVBAgMCkNhbGlmb3JuaWExFDASBgNVBAcMC0xv
cyBBbmdlbGVzMRUwEwYDVQQKDAxBY21lIFdpZGdldHMxETAPBgNVBAsMCFNlY3Vy
aXR5MR0wGwYDVQQDDBRyYWJiaXRtcS5leGFtcGxlLmNvbTEnMCUGCSqGSIb3DQEJ
ARYYcm9nZXIucmFiYml0QGV4YW1wbGUuY29tMB4XDTE3MDkxMzAzMzExMloXDTI3
MDkxMTAzMzExMlowgZUxCzAJBgNVBAYTAlVTMQswCQYDVQQIDAJDQTEQMA4GA1UE
BwwHQ2hpY2FnbzENMAsGA1UECgwEQWNtZTEQMA4GA1UECwwHV2lkZ2V0czEdMBsG
A1UEAwwUcmFiYml0bXEuZXhhbXBsZS5jb20xJzAlBgkqhkiG9w0BCQEWGHJvZ2Vy
LnJhYmJpdEBleGFtcGxlLmNvbTCBnzANBgkqhkiG9w0BAQEFAAOBjQAwgYkCgYEA
8NblyPhAIMZOHTsQKz6jDZ5wkKpl3kOtV0EF6AIFlo0b2OCwCeAC6AAmFhZVlSkJ
Cy0H0NmGDN7zQxv3mHy9rY4afQD3iayWCRHG5q+QjlEbbiIf2X1SkYFeb7TjHZQB
0tBxaZPwz9YOdtA2vcCsL9pw230botMtCJj1s9bqivMCAwEAAaMaMBgwCQYDVR0T
BAIwADALBgNVHQ8EBAMCBeAwDQYJKoZIhvcNAQEFBQADggIBAIPXqHlxbo+BpBWz
H+E7lFDYZkUQD8c34jybxZbwya7od/f9x5jU3F/+6g1Y7A8NAPPxbs+m3Sye0/sz
WmEz+smKFseGVPWQJ6NgvYJjr8P/MpBUfA7zkkaKs2uuTbPx0tgU1+XGluC1hsZ+
KQCI0LIEGQlSuT3miPSYL6MPR9o/PTp6TPlYGbL+LLNQedriZRLuiUpOWJzsFyz/
CcdayANtlWxhuy50A9qUwAa5RzgIJnqfxk5OYuJHq7+p1oCKk+XhgHccHHq7t97M
Ye/xC18NDTMKkAiggCad0+kYTUgUYnuwvYg4eRpQytsncp7Ck83JChb56eJKlsj1
HnEkQDyi1B/cKxRKwG81fzkSgw7+0nFY6GAIaGz1GdYBkoxlzN9JNd3/MmS+xAtm
HXtCqkTWUJQ5ZBinszauPtG4Inu6H28blstlqGjzKTzwo1w8N27shii14IhEHhEV
tVRxA+ORIt14fZrl04AROsYLqJxqBHy8vJEX4OMm2JTzaO2KdPwEi2gmQK0SkfR8
TCQc43SF2rqpYs3ZE4fPNlyQR3ZCUcYwjv99s6Nz0Ue4vD9+PuT0McE8NtMgiAMf
DQqmYIMEiYNm15qKW6jJaY7VMUPGNohfEvubsWPlXPQ6/I7e9bNyk5OnA6OGi4qz
M2Fcgn+pqLU7M+epynzgz4bsPsEw
-----END CERTIFICATE-----" > /tmp/rabbitmq.crt'
      on host, 'echo "-----BEGIN CERTIFICATE-----
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
-----END CERTIFICATE-----" > /tmp/cacert.crt'
    end
  end
end

shared_examples 'an idempotent resource' do
  it 'applies with no errors' do
    apply_manifest(pp, catch_failures: true)
  end

  it 'applies a second time without changes' do
    apply_manifest(pp, catch_changes: true)
  end
end
