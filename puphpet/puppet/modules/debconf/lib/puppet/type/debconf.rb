# debconf.rb --- The debconf type

Puppet::Type.newtype(:debconf) do
  desc <<-EOT
    Manage debconf database entries on Debian based systems. This type
    can either set or remove a value for a debconf database entry. It
    uses multiple programs from the 'debconf' package.

    Examples:

        debconf { 'tzdata/Areas':
          type  => 'select',
          value => 'Europe',
        }

        debconf { 'dash/sh':
          type  => 'boolean',
          value => 'true',
        }

        debconf { 'libraries/restart-without-asking':
          package => 'libc6',
          type    => 'boolean',
          value   => 'true',
        }
  EOT

  ensurable do
    defaultvalues
    defaultto :present
  end

  newparam(:item, :namevar => true) do
    desc %{The item name. This string must have the following format: the
      package name, a literal slash char and the name of the question (e.g.
      'tzdata/Areas').}

    newvalues(/^[a-z0-9][a-z0-9:.+-]+\/[a-zA-Z0-9\/_.+-]+$/)
  end

  newparam(:package) do
    desc %{The package the item belongs to. The default is the first part (up
      to the first '/') of the item parameter (e.g. 'tzdata').}

    newvalues(/^[a-z0-9][a-z0-9:.+-]+$/)
    defaultto { @resource[:item].split('/', 2).first }
  end

  newparam(:type) do
    desc %{The type of the item. This can only be one of the following
      values: string, boolean, select, multiselect, note, text, password,
      title.}

    newvalues(:string, :boolean, :select, :multiselect,
              :note, :text, :password, :title)
  end

  newproperty(:value) do
    desc %{The value for the item (e.g. 'Europe').}

    newvalues(/\S/)
    munge { |value| value.strip } # Remove leading and trailing spaces
  end

  validate do
    unless self[:type]
      unless (self[:ensure].to_s == 'absent')
        raise(Puppet::Error, "type is a required attribute")
      end
    end
  end
end
