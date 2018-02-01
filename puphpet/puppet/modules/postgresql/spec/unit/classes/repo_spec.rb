require 'spec_helper'

describe 'postgresql::repo', :type => :class do
  let :facts do
    {
      :os => {
        :name                 => 'Debian',
        :family               => 'Debian',
        :release => {
          :full               => '6.0',
          :major              => '6'
        }
      },
      :osfamily               => 'Debian',
      :operatingsystem        => 'Debian',
      :operatingsystemrelease => '6.0',
      :lsbdistid              => 'Debian',
      :lsbdistcodename        => 'squeeze',
    }
  end

  describe 'with no parameters' do
    it 'should instantiate apt_postgresql_org class' do
      is_expected.to contain_class('postgresql::repo::apt_postgresql_org')
    end
  end
end
