require File.join(File.dirname(__FILE__), '..', 'vcsrepo')

Puppet::Type.type(:vcsrepo).provide(:dummy, parent: Puppet::Provider::Vcsrepo) do
  desc 'Dummy default provider'

  defaultfor feature: :posix
  defaultfor operatingsystem: :windows

  def working_copy_exists?
    providers = begin
                  @resource.class.providers.map { |x| x.to_s }.sort.reject { |x| x == 'dummy' }.join(', ')
                rescue
                  'none'
                end
    raise("vcsrepo resource must have a provider, available: #{providers}")
  end
end
