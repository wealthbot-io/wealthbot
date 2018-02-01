class apache::mod::negotiation (
  Variant[Array[String], String] $force_language_priority = 'Prefer Fallback',
  Variant[Array[String], String] $language_priority = [ 'en', 'ca', 'cs', 'da', 'de', 'el', 'eo', 'es', 'et',
                        'fr', 'he', 'hr', 'it', 'ja', 'ko', 'ltz', 'nl', 'nn',
                        'no', 'pl', 'pt', 'pt-BR', 'ru', 'sv', 'zh-CN',
                        'zh-TW' ],
) {
  include ::apache

  ::apache::mod { 'negotiation': }
  # Template uses no variables
  file { 'negotiation.conf':
    ensure  => file,
    mode    => $::apache::file_mode,
    path    => "${::apache::mod_dir}/negotiation.conf",
    content => template('apache/mod/negotiation.conf.erb'),
    require => Exec["mkdir ${::apache::mod_dir}"],
    before  => File[$::apache::mod_dir],
    notify  => Class['apache::service'],
  }
}
