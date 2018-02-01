require 'spec_helper_acceptance'

test_name 'Augeasproviders Sysctl'

describe 'Sysctl Tests' do
  hosts.each do |host|
    context 'file based updates' do
      let(:manifest) {
        <<-EOM
          sysctl { 'fs.nr_open':
            value => '100000',
            apply => false
          }
        EOM
      }

      # Using puppet_apply as a helper
      it 'should work with no errors' do
        apply_manifest_on(host, manifest, :catch_failures => true)
      end

      it 'should be idempotent' do
        apply_manifest_on(host, manifest, {:catch_changes => true})
      end

      it 'should have applied successfuly in the sysctl config but not live' do
        result = on(host, 'sysctl -n fs.nr_open').stdout.strip
        expect(result).to_not eql('100000')

        on(host, 'sysctl -p', :accept_all_exit_codes => true)
        result = on(host, 'sysctl -n fs.nr_open').stdout.strip
        expect(result).to eql('100000')
      end
    end

    context 'full updates' do
      let(:manifest) {
        <<-EOM
          sysctl { 'fs.nr_open':
            value => '100001'
          }
        EOM
      }

      # Using puppet_apply as a helper
      it 'should work with no errors' do
        apply_manifest_on(host, manifest, :catch_failures => true)
      end

      it 'should be idempotent' do
        apply_manifest_on(host, manifest, {:catch_changes => true})
      end

      it 'should apply successfuly in the config and live' do
        result = on(host, 'sysctl -n fs.nr_open').stdout.strip
        expect(result).to eql('100001')
      end

      context 'when given an invalid key' do
        let(:manifest) {
          <<-EOM
            sysctl { 'fs.this_cannot_exist':
              value => 'I like bread'
            }
          EOM
        }

        it 'should fail to apply to the system' do
          apply_manifest_on(host, manifest, :expect_failures => true)
        end
      end

      context 'when silent' do
        let(:manifest) {
          <<-EOM
            sysctl { 'fs.nr_open':
              value  => '100002',
              silent => true
            }
          EOM
        }

        # Using puppet_apply as a helper
        it 'should work with no errors' do
          apply_manifest_on(host, manifest, :catch_failures => true)
        end

        it 'should be idempotent' do
          apply_manifest_on(host, manifest, {:catch_changes => true})
        end
      end

      context 'when silent and given a key that does not exist' do
        let(:manifest) {
          <<-EOM
            sysctl { 'fs.this_cannot_exist':
              value  => 'I like bread',
              silent => true
            }
          EOM
        }

        # Using puppet_apply as a helper
        it 'should work with no errors' do
          apply_manifest_on(host, manifest, :catch_failures => true)
        end

        it 'should be idempotent' do
          apply_manifest_on(host, manifest, {:catch_changes => true})
        end
      end

      context 'when only applying to the running system' do
        let(:manifest) {
          <<-EOM
            sysctl { 'kernel.pty.max':
              value   => 4097,
              apply   => true,
              persist => false
            }
          EOM
        }

        # Using puppet_apply as a helper
        it 'should work with no errors' do
          apply_manifest_on(host, manifest, :catch_failures => true)
        end

        it 'should be idempotent' do
          apply_manifest_on(host, manifest, {:catch_changes => true})
        end

        it 'should not be in /etc/sysctl.conf' do
          on(host, 'grep kernel.pty.max /etc/sysctl.conf', :acceptable_exit_codes => [1])
        end
      end

      context 'when only applying to the filesystem' do
        let(:manifest) {
          <<-EOM
            sysctl { 'kernel.pty.max':
              comment => 'just a test',
              value   => 4098,
              apply   => false,
              persist => true
            }
          EOM
        }

        # Using puppet_apply as a helper
        it 'should work with no errors' do
          apply_manifest_on(host, manifest, :catch_failures => true)
        end

        it 'should be idempotent' do
          apply_manifest_on(host, manifest, {:catch_changes => true})
        end

        it 'should be in /etc/sysctl.conf' do
          on(host, 'grep "kernel.pty.max = 4098" /etc/sysctl.conf')
        end

        it 'should not be changed on the running system' do
          result = on(host, 'sysctl -n kernel.pty.max').stdout.strip
          expect(result).to eql('4097')
        end
      end

      context 'when deleting an entry' do
        let(:manifest) {
          <<-EOM
            sysctl { 'kernel.pty.max':
              ensure  => absent
            }
          EOM
        }

        # Using puppet_apply as a helper
        it 'should work with no errors' do
          apply_manifest_on(host, manifest, :catch_failures => true)
        end

        it 'should be idempotent' do
          apply_manifest_on(host, manifest, {:catch_changes => true})
        end

        it 'should not be in /etc/sysctl.conf' do
          on(host, 'grep "kernel.pty.max" /etc/sysctl.conf', :acceptable_exit_codes => [1])
        end

        it 'should not be changed on the running system' do
          result = on(host, 'sysctl -n kernel.pty.max').stdout.strip
          expect(result).to eql('4097')
        end
      end
    end
  end
end
