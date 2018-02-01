supervisord::rpcinterface {
  'foo':
    rpcinterface_factory => 'foo:bar';
  'bar':
    rpcinterface_factory => 'baz:bat',
    retries              => 1,
}
