# Declare Apt key for apt.puppetlabs.com source
apt::key { 'puppetlabs':
  id      => '6F6B15509CF8E59E6E469F327F438280EF8D349F',
  server  => 'hkps.pool.sks-keyservers.net',
  options => 'http-proxy="http://proxyuser:proxypass@example.org:3128"',
}
