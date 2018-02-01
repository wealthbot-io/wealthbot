# = Class: yum::repo::elasticsearch2
#
# This class installs the elasticsearch2.x repo
#
class yum::repo::elasticsearch2 (
  $baseurl = 'http://packages.elastic.co/elasticsearch/2.x/centos',
) {

  yum::managed_yumrepo { 'elasticsearch-2.x':
    descr         => 'Elasticsearch repository for 2.x packages',
    baseurl       => $baseurl,
    enabled       => 1,
    gpgcheck      => 1,
    gpgkey        => 'file:///etc/pki/rpm-gpg/RPM-GPG-KEY-elasticsearch',
    gpgkey_source => 'puppet:///modules/yum/rpm-gpg/RPM-GPG-KEY-elasticsearch',
  }

}
