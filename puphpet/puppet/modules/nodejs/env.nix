with import <nixpkgs> {};

stdenv.mkDerivation rec {
  name = "willdurand-puppet-nodejs-env";

  env = bundlerEnv {
    name = "willdurand-puppet-nodejs-env-gems";

    gemfile  = ./Gemfile;
    lockfile = ./Gemfile-Nix.lock;
    gemset   = ./gemset.nix;

    inherit ruby;
  };

  buildInputs = [ makeWrapper ];

  phases = [ "installPhase" ];
  installPhase = ''
    export PUPPET_VERSION="~> 4.9.0"

    mkdir -p $out/bin
    makeWrapper ${env}/bin/puppet $out/bin/puppet
    makeWrapper ${env}/bin/rake $out/bin/rake
    makeWrapper ${env}/bin/puppet-lint $out/bin/puppet-lint
  '';
}
