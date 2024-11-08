{
  outputs = inputs: let
    eachSystem = inputs.nixpkgs.lib.genAttrs ["x86_64-linux"];
    pkgsFor = eachSystem (system: import inputs.nixpkgs {inherit system;});
  in {
    devShells = eachSystem (
      system: let
        pkgs = pkgsFor.${system};
      in {
        default = pkgs.mkShell {
          buildInputs = with pkgs; [
            php83
            php83Packages.php-cs-fixer
            phpactor

            docker-compose
          ];
        };
      }
    );
  };

  inputs.nixpkgs.url = "github:NixOS/nixpkgs/nixos-unstable";
}
