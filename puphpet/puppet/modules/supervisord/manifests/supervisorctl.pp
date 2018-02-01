# Define: supervisord:supervisorctl
#
# This define executes command with the supervisorctl tool
#
define supervisord::supervisorctl(
  $command,
  $process       = undef,
  $refreshonly   = false,
  $unless        = undef
) {

  validate_legacy(String, 'validate_string', $command)
  validate_legacy(String, 'validate_string', $process)

  $supervisorctl = $::supervisord::executable_ctl

  if $process {
    $cmd = join([$supervisorctl, $command, $process], ' ')
  }
  else {
    $cmd = join([$supervisorctl, $command], ' ')
  }

  if $unless {
    $unless_cmd = join([$supervisorctl, 'status', $process, '|', 'awk', '{\'print $2\'}', '|', 'grep', '-i', $unless], ' ')
  }
  else {
    $unless_cmd = undef
  }

  exec { "supervisorctl_command_${name}":
    command     => $cmd,
    refreshonly => $refreshonly,
    unless      => $unless_cmd
  }
}
