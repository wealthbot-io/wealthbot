# == Class: composer::project_factory
#
# Class responsible for creating a load of projects.
#
# [*projects*]
#   A hash with the projects to be created.
#
#
class composer::project_factory (
  $projects = lookup('composer::project_factory::projects', Hash, {'strategy' => 'deep', 'merge_hash_arrays' => true}, {}),
  $execs    = lookup('composer::project_factory::execs', Hash, {'strategy' => 'deep', 'merge_hash_arrays' => true}, {}),
) {

  if $projects {
    create_resources('composer::project', $projects)
  }

  if $execs {
    create_resources('composer::exec', $execs)
  }
}
