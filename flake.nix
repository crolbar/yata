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

        sdkVersion = "34";
        buildToolsVersion = "34.0.0";
        # platformToolsVersion = "34.0.1";

        androidComposition = pkgs.androidenv.composeAndroidPackages {
          includeNDK = false;
          includeSystemImages = false;
          includeEmulator = false;
          platformVersions = [sdkVersion];
          buildToolsVersions = [buildToolsVersion];
          # platformToolsVersion = platformToolsVersion;
        };
        androidSdk = androidComposition.androidsdk;
      in {
        default = pkgs.mkShell rec {
          packages = with pkgs;
            [
              phpactor # lsp
              php83Packages.php-cs-fixer # formatter

              tailwindcss # stylesheet builder

              docker-compose
              flyctl # deployment

              #android-studio
              gradle

              jdk

              androidComposition.build-tools

              (pkgs.writers.writeBashBin "prun" ''
                # 192.168.1.3:42531
                # adb connect 192.168.1.3:42531
                # adb -s $dev uninstall com.notifiyer

                dev=""

                if [[ "$1" == "e" ]]; then
                    dev="emulator-5554"
                else
                    dev="192.168.1.2:40819"
                fi

                adb -s $dev install app/build/outputs/apk/debug/app-debug.apk
                adb -s $dev shell am start -n com.notifiyer/.MainActivity
              '')
            ]
            ++ scripts;

          buildInputs = with pkgs; [
            php83
          ];

          ANDROID_SDK_ROOT = "${androidSdk}/libexec/android-sdk";
          GRADLE_OPTS = "-Dorg.gradle.project.android.aapt2FromMavenOverride=${ANDROID_SDK_ROOT}/build-tools/${buildToolsVersion}/aapt2";
        };
      }
    );
  };

  inputs.nixpkgs.url = "github:NixOS/nixpkgs/nixos-unstable";
}
