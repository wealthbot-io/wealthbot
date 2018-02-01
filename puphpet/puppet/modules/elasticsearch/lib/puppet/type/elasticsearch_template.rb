$LOAD_PATH.unshift(File.join(File.dirname(__FILE__), '..', '..'))

require 'puppet/file_serving/content'
require 'puppet/file_serving/metadata'

require 'puppet_x/elastic/deep_implode'
require 'puppet_x/elastic/deep_to_i'
require 'puppet_x/elastic/elasticsearch_rest_resource'

# rubocop:disable Metrics/BlockLength
Puppet::Type.newtype(:elasticsearch_template) do
  extend ElasticsearchRESTResource

  desc 'Manages Elasticsearch index templates.'

  ensurable

  newparam(:name, :namevar => true) do
    desc 'Template name.'
  end

  newproperty(:content) do
    desc 'Structured content of template.'

    validate do |value|
      raise Puppet::Error, 'hash expected' unless value.is_a? Hash
    end

    munge do |value|
      # The Elasticsearch API will return default empty values for
      # order, aliases, and mappings if they aren't defined in the
      # user mapping, so we need to set defaults here to keep the
      # `in` and `should` states consistent if the user hasn't
      # provided any.
      #
      # We use deep_to_i to ensure any numeric values are properly
      # parsed, whether from user-defined resources or when reading
      # from the API.
      #
      # We also need to fully qualify index settings, since users
      # can define those with the index json key absent, but the API
      # always fully qualifies them.
      { 'order' => 0, 'aliases' => {}, 'mappings' => {} }.merge(
        Puppet_X::Elastic.deep_to_i(
          value.tap do |val|
            if val.key? 'settings'
              val['settings']['index'] = {} unless val['settings'].key? 'index'
              (val['settings'].keys - ['index']).each do |setting|
                new_key = if setting.start_with? 'index.'
                            setting[6..-1]
                          else
                            setting
                          end
                val['settings']['index'][new_key] = \
                  val['settings'].delete setting
              end
            end
          end
        )
      )
    end

    def insync?(is)
      Puppet_X::Elastic.deep_implode(is) == \
        Puppet_X::Elastic.deep_implode(should)
    end
  end

  newparam(:source) do
    desc 'Puppet source to file containing template contents.'

    validate do |value|
      raise Puppet::Error, 'string expected' unless value.is_a? String
    end
  end

  # rubocop:disable Style/SignalException
  validate do
    # Ensure that at least one source of template content has been provided
    if self[:ensure] == :present
      fail Puppet::ParseError, '"content" or "source" required' \
        if self[:content].nil? and self[:source].nil?
      if !self[:content].nil? and !self[:source].nil?
        fail(
          Puppet::ParseError,
          "'content' and 'source' cannot be simultaneously defined"
        )
      end
    end

    # If a source was passed, retrieve the source content from Puppet's
    # FileServing indirection and set the content property
    unless self[:source].nil?
      unless Puppet::FileServing::Metadata.indirection.find(self[:source])
        fail(format('Could not retrieve source %s', self[:source]))
      end

      if !self.catalog.nil? \
          and self.catalog.respond_to?(:environment_instance)
        tmp = Puppet::FileServing::Content.indirection.find(
          self[:source],
          :environment => self.catalog.environment_instance
        )
      else
        tmp = Puppet::FileServing::Content.indirection.find(self[:source])
      end

      fail(format('Could not find any content at %s', self[:source])) unless tmp
      self[:content] = PSON.load(tmp.content)
    end
  end
end # of newtype
