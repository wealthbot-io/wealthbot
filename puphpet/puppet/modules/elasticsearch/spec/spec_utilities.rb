def to_agent_version(puppet_version)
  # REF: https://docs.puppet.com/puppet/latest/reference/about_agent.html
  {
    # Puppet => Agent
    '4.10.4' => '1.10.4',
    '4.10.3' => '1.10.3',
    '4.10.2' => '1.10.2',
    '4.10.1' => '1.10.1',
    '4.10.0' => '1.10.0',
    '4.9.4' => '1.9.3',
    '4.8.2' => '1.8.3',
    '4.7.1' => '1.7.2',
    '4.7.0' => '1.7.1',
    '4.6.2' => '1.6.2',
    '4.5.3' => '1.5.3',
    '4.4.2' => '1.4.2',
    '4.4.1' => '1.4.1',
    '4.4.0' => '1.4.0',
    '4.3.2' => '1.3.6',
    '4.3.1' => '1.3.2',
    '4.3.0' => '1.3.0',
    '4.2.3' => '1.2.7',
    '4.2.2' => '1.2.6',
    '4.2.1' => '1.2.2',
    '4.2.0' => '1.2.1',
    '4.1.0' => '1.1.1',
    '4.0.0' => '1.0.1'
  }[puppet_version]
end

def artifact(file)
  File.join(%w[spec fixtures artifacts] + [File.basename(file)])
end

def get(url, file_path)
  puts "Fetching #{url}..."
  found = false
  until found
    uri = URI.parse(url)
    conn = Net::HTTP.new(uri.host, uri.port)
    conn.use_ssl = true
    res = conn.get(uri.path)
    if res.header['location']
      url = res.header['location']
    else
      found = true
    end
  end
  File.open(file_path, 'w+') { |fh| fh.write res.body }
end

def fetch_archives(archives)
  archives.each do |url, orig_fp|
    fp = "spec/fixtures/artifacts/#{orig_fp}"
    if File.exist? fp
      if fp.end_with? 'tar.gz' and !system("tar -tzf #{fp} &>/dev/null")
        puts "Archive #{fp} corrupt, re-fetching..."
        File.delete fp
      else
        puts "Already retrieved intact archive #{fp}..."
        next
      end
    end
    get url, fp
  end
end
