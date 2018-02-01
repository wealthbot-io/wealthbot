module Puppet_X
  module Elastic
    def self.plugin_name(raw_name)
      plugin_split(raw_name, 1)
    end

    # Attempt to guess at the plugin's final directory name
    def self.plugin_split(original_string, position)
      # Try both colon (maven) and slash-delimited (github/elastic.co) names
      %w[/ :].each do |delimiter|
        parts = original_string.split(delimiter)
        # If the string successfully split, assume we found the right format
        return parts[position].gsub(/(elasticsearch-|es-)/, '') unless parts[position].nil?
      end

      # Fallback to the originally passed plugin name
      original_string
    end
  end # of Elastic
end # of Puppet_X
