with import <nixpkgs> {};

stdenv.mkDerivation rec {
  name = "willdurand-puppet-nodejs";

  buildInputs =
  [
    (import ./env.nix)
  ];
}
