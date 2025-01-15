{
  outputs = inputs: let
    eachSystem = inputs.nixpkgs.lib.genAttrs ["x86_64-linux"];
    pkgsFor = eachSystem (system:
      import inputs.nixpkgs {
        inherit system;
        config = {
          android_sdk.accept_license = true;
          allowUnfree = true;
        };
      });
  in {
    devShells = eachSystem (
      system: let
        pkgs = pkgsFor.${system};

        inherit (pkgs) lib;
        scripts = import ./scripts.nix {inherit pkgs lib;};
        notifiyer-pkgs = import ./notifiyer/pkgs.nix {inherit pkgs;};
      in {
        default = pkgs.mkShell {
          packages = with pkgs;
            [
              phpactor # lsp
              php83Packages.php-cs-fixer # formatter

              tailwindcss # stylesheet builder

              docker-compose
              flyctl # deployment
            ]
            ++ scripts
            ++ notifiyer-pkgs;

          buildInputs = with pkgs; [
            php83
          ];
        };
      }
    );
  };

  inputs.nixpkgs.url = "github:NixOS/nixpkgs/nixos-unstable";
}
