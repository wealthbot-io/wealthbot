describe 'formatting in README.markdown' do
  it 'does not contain badly formatted heading markers' do
    content = File.read('README.markdown')
    expect(content).not_to match %r{^#+[^# ]}
  end
end
