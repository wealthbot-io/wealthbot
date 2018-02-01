require 'spec_helper_acceptance'

describe 'rabbitmq binding:' do
  context 'create binding and queue resources when using default management port' do
    it 'runs successfully' do
      pp = <<-EOS
      if $facts['os']['family'] == 'RedHat' {
        class { 'erlang': epel_enable => true }
        Class['erlang'] -> Class['rabbitmq']
      }
      class { 'rabbitmq':
        service_manage    => true,
        port              => 5672,
        delete_guest_user => true,
        admin_enable      => true,
      } ->

      rabbitmq_user { 'dan':
        admin    => true,
        password => 'bar',
        tags     => ['monitoring', 'tag1'],
      } ->

      rabbitmq_user_permissions { 'dan@host1':
        configure_permission => '.*',
        read_permission      => '.*',
        write_permission     => '.*',
      }

      rabbitmq_vhost { 'host1':
        ensure => present,
      } ->

      rabbitmq_exchange { 'exchange1@host1':
        user     => 'dan',
        password => 'bar',
        type     => 'topic',
        ensure   => present,
      } ->

      rabbitmq_queue { 'queue1@host1':
        user        => 'dan',
        password    => 'bar',
        durable     => true,
        auto_delete => false,
        ensure      => present,
      } ->

      rabbitmq_binding { 'exchange1@queue1@host1':
        user             => 'dan',
        password         => 'bar',
        destination_type => 'queue',
        routing_key      => '#',
        ensure           => present,
      }

      EOS

      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)
    end

    # rubocop:disable RSpec/MultipleExpectations
    it 'has the binding' do
      shell('rabbitmqctl list_bindings -q -p host1') do |r|
        expect(r.stdout).to match(%r{exchange1\sexchange\squeue1\squeue\s#})
        expect(r.exit_code).to be_zero
      end
    end

    it 'has the queue' do
      shell('rabbitmqctl list_queues -q -p host1') do |r|
        expect(r.stdout).to match(%r{queue1})
        expect(r.exit_code).to be_zero
      end
    end
    # rubocop:enable RSpec/MultipleExpectations
  end

  context 'create multiple bindings when same source / destination / vhost but different routing keys' do
    it 'runs successfully' do
      pp = <<-EOS
      if $facts['os']['family'] == 'RedHat' {
        class { 'erlang': epel_enable => true }
        Class['erlang'] -> Class['rabbitmq']
      }
      class { 'rabbitmq':
        service_manage    => true,
        port              => 5672,
        delete_guest_user => true,
        admin_enable      => true,
      } ->

      rabbitmq_user { 'dan':
        admin    => true,
        password => 'bar',
        tags     => ['monitoring', 'tag1'],
      } ->

      rabbitmq_user_permissions { 'dan@host1':
        configure_permission => '.*',
        read_permission      => '.*',
        write_permission     => '.*',
      }

      rabbitmq_vhost { 'host1':
        ensure => present,
      } ->

      rabbitmq_exchange { 'exchange1@host1':
        user     => 'dan',
        password => 'bar',
        type     => 'topic',
        ensure   => present,
      } ->

      rabbitmq_queue { 'queue1@host1':
        user        => 'dan',
        password    => 'bar',
        durable     => true,
        auto_delete => false,
        ensure      => present,
      } ->

      rabbitmq_binding { 'binding 1':
        source           => 'exchange1',
        destination      => 'queue1',
        user             => 'dan',
        vhost            => 'host1',
        password         => 'bar',
        destination_type => 'queue',
        routing_key      => 'test1',
        ensure           => present,
      } ->

      rabbitmq_binding { 'binding 2':
        source           => 'exchange1',
        destination      => 'queue1',
        user             => 'dan',
        vhost            => 'host1',
        password         => 'bar',
        destination_type => 'queue',
        routing_key      => 'test2',
        ensure           => present,
      }

      EOS

      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)
    end

    # rubocop:disable RSpec/MultipleExpectations
    it 'has the bindings' do
      shell('rabbitmqctl list_bindings -q -p host1') do |r|
        expect(r.stdout).to match(%r{exchange1\sexchange\squeue1\squeue\stest1})
        expect(r.stdout).to match(%r{exchange1\sexchange\squeue1\squeue\stest2})
        expect(r.exit_code).to be_zero
      end
    end
    # rubocop:enable RSpec/MultipleExpectations

    it 'puppet resource shows a binding' do
      shell('puppet resource rabbitmq_binding') do |r|
        expect(r.stdout).to match(%r{source\s+=>\s+'exchange1',})
      end
    end
  end

  context 'create binding and queue resources when using a non-default management port' do
    it 'runs successfully' do
      pp = <<-EOS
      if $facts['os']['family'] == 'RedHat' {
        class { 'erlang': epel_enable => true }
        Class['erlang'] -> Class['rabbitmq']
      }
      class { 'rabbitmq':
        service_manage    => true,
        port              => 5672,
        management_port   => 11111,
        delete_guest_user => true,
        admin_enable      => true,
      } ->

      rabbitmq_user { 'dan':
        admin    => true,
        password => 'bar',
        tags     => ['monitoring', 'tag1'],
      } ->

      rabbitmq_user_permissions { 'dan@host2':
        configure_permission => '.*',
        read_permission      => '.*',
        write_permission     => '.*',
      }

      rabbitmq_vhost { 'host2':
        ensure => present,
      } ->

      rabbitmq_exchange { 'exchange2@host2':
        user     => 'dan',
        password => 'bar',
        type     => 'topic',
        ensure   => present,
      } ->

      rabbitmq_queue { 'queue2@host2':
        user        => 'dan',
        password    => 'bar',
        durable     => true,
        auto_delete => false,
        ensure      => present,
      } ->

      rabbitmq_binding { 'exchange2@queue2@host2':
        user             => 'dan',
        password         => 'bar',
        destination_type => 'queue',
        routing_key      => '#',
        ensure           => present,
      }

      EOS

      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)
    end

    # rubocop:disable RSpec/MultipleExpectations
    it 'has the binding' do
      shell('rabbitmqctl list_bindings -q -p host2') do |r|
        expect(r.stdout).to match(%r{exchange2\sexchange\squeue2\squeue\s#})
        expect(r.exit_code).to be_zero
      end
    end

    it 'has the queue' do
      shell('rabbitmqctl list_queues -q -p host2') do |r|
        expect(r.stdout).to match(%r{queue2})
        expect(r.exit_code).to be_zero
      end
    end
    # rubocop:enable RSpec/MultipleExpectations
  end
end
