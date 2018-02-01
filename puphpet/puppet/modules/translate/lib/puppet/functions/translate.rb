# A function that calls the _() function in gettext. This is because _ is protected in the puppet language
Puppet::Functions.create_function(:translate) do
  dispatch :translate do
    param 'String', :message
    optional_param 'Hash', :interpolation_values
  end

  def translate(message, interpolation_values=nil)
    if interpolation_values.nil?
      _(message)
    else
      # convert keys to symbols
      interpolation_values = Hash[interpolation_values.map{ |k, v| [k.to_sym, v] }]
      _(message) % interpolation_values
   end
  end
end
