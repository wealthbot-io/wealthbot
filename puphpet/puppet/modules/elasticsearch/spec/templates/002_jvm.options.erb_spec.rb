require 'spec_helper'
require 'yaml'

describe 'jvm.options.erb' do
  let :harness do
    TemplateHarness.new(
      'templates/etc/elasticsearch/jvm.options.erb'
    )
  end

  it 'render the same string each time' do
    harness.set(
      '@jvm_options', [
        '-Xms2g',
        '-Xmx2g'
      ]
    )

    first_render = harness.run
    second_render = harness.run

    expect(first_render).to eq(second_render)
  end
end
