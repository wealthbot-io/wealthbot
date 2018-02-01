require 'spec_helper'

shared_examples :epel_testing_debuginfo do
  it do
    is_expected.to contain_yumrepo('epel-testing-debuginfo').with(
      failovermethod: 'priority',
      proxy:          'absent',
      enabled:        '0',
      gpgcheck:       '1'
    )
  end
end

shared_examples_for :epel_testing_debuginfo_7 do
  include_context :epel_testing_debuginfo

  it do
    is_expected.to contain_yumrepo('epel-testing-debuginfo').with(
      mirrorlist: 'https://mirrors.fedoraproject.org/metalink?repo=testing-debug-epel7&arch=$basearch',
      gpgkey:     'file:///etc/pki/rpm-gpg/RPM-GPG-KEY-EPEL-7',
      descr:      'Extra Packages for Enterprise Linux 7 - Testing - $basearch - Debug'
    )
  end
end

shared_examples_for :epel_testing_debuginfo_6 do
  include_context :epel_testing_debuginfo

  it do
    is_expected.to contain_yumrepo('epel-testing-debuginfo').with(
      mirrorlist: 'https://mirrors.fedoraproject.org/metalink?repo=testing-debug-epel6&arch=$basearch',
      gpgkey:     'file:///etc/pki/rpm-gpg/RPM-GPG-KEY-EPEL-6',
      descr:      'Extra Packages for Enterprise Linux 6 - Testing - $basearch - Debug'
    )
  end
end

shared_examples_for :epel_testing_debuginfo_5 do
  include_context :epel_testing_debuginfo

  it do
    is_expected.to contain_yumrepo('epel-testing-debuginfo').with(
      mirrorlist: 'https://mirrors.fedoraproject.org/mirrorlist?repo=testing-debug-epel5&arch=$basearch',
      gpgkey:     'file:///etc/pki/rpm-gpg/RPM-GPG-KEY-EPEL-5',
      descr:      'Extra Packages for Enterprise Linux 5 - Testing - $basearch - Debug'
    )
  end
end
