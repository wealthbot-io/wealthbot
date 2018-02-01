$LOAD_PATH.unshift(File.join(File.dirname(__FILE__), '..', '..'))

require 'puppet_x/elastic/deep_to_i'
require 'puppet_x/elastic/deep_implode'
require 'puppet_x/elastic/elasticsearch_rest_resource'

# rubocop:disable Metrics/BlockLength
Puppet::Type.newtype(:elasticsearch_index) do
  extend ElasticsearchRESTResource

  desc 'Manages Elasticsearch index settings.'

  ensurable

  newparam(:name, :namevar => true) do
    desc 'Index name.'
  end

  newproperty(:settings) do
    desc 'Structured settings for the index in hash form.'

    # The Elasticsearch index settings API returns lots of fields, including
    # fields such as creation time, shard count, and shard replicas. When
    # comparing desired settings and extant settings, only indicate that
    # settings need to be flushed when user-desired settings differ from
    # existing settings - we ignore keys that exist in the cluster index
    # settings that aren't being controlled by Puppet.
    def asymmetric_compare(should_val, is_val)
      should_val.reduce(true) do |is_synced, (should_key, should_setting)|
        if is_val.key? should_key
          if is_val[should_key].is_a? Hash
            asymmetric_compare(should_setting, is_val[should_key])
          else
            is_synced && is_val[should_key] == should_setting
          end
        else
          is_synced && true
        end
      end
    end

    def insync?(is)
      asymmetric_compare(should, is)
    end

    munge do |value|
      Puppet_X::Elastic.deep_to_i(value)
    end

    validate do |value|
      raise Puppet::Error, 'hash expected' unless value.is_a? Hash
    end
  end
end # of newtype
