require 'json'

class NodeVersion < Array
  def initialize s
    super(s.split('.').map { |e| e.to_i })
  end
  def < x
    (self <=> x) < 0
  end
  def > x
    (self <=> x) > 0
  end
  def == x
    (self <=> x) == 0
  end
end

class NodeJSListStore
  @@list = nil
  def self.set_list(list)
    @@list = list
  end
  def self.is_cached
    @@list.nil? == false
  end
  def self.get_list
    return @@list
  end
end

# inspired by https://github.com/visionmedia/n/blob/5630984059fb58f47def8dca2f25163456181ed3/bin/n#L363-L372
def get_version_list
  if NodeJSListStore::is_cached
    return NodeJSListStore::get_list
  end

  uri = URI('https://nodejs.org/dist/index.json')

  http_proxy = ENV["http_proxy"]
  if http_proxy.to_s != ''
    if http_proxy =~ /^http[s]{0,1}:\/\/.*/
      proxy = URI.parse(http_proxy)
    else
      proxy = URI.parse('http://' + http_proxy)
    end
    request = Net::HTTP::Proxy(proxy.host, proxy.port).new(uri.host, uri.port)
  else
    request = Net::HTTP.new(uri.host, uri.port)
  end
  request.use_ssl = (uri.scheme == 'https')
  request.open_timeout = 2
  request.read_timeout = 2

  result = JSON.parse(request.get(uri.request_uri).body)
  NodeJSListStore::set_list(result)

  return result
end

def get_latest_version
  version_data = get_version_list.map { |o| o['version'].gsub(/^v/, '') }
  version_data.sort! { |a,b| NodeVersion.new(a) <=> NodeVersion.new(b) };
  'v' + version_data.last
end

def get_lts_version
  # in this case it needs to be checked whether NOT false as the `lts` value can either be `false`
  # or the name of the LTS (e.g. Argon)
  version_data = get_version_list.select { |o| o['lts'] != false }
  version_data.first['version']
end

def get_version_from_branch(version)
  if version =~ /^[0-9]+\.x$/
    version.gsub!(/\.x$/, '')
    regex = /^v#{version}\.[0-9]+\.[0-9]+$/
  else
    regex = /^v#{version}\.([0-9]+)$/ 
  end

  version_data = get_version_list
    .map { |o| o['version'] }
    .select { |v| v =~ regex }

  version_data.first
end
