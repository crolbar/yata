{
  pkgs,
  lib,
  ...
}: let
  tw = lib.getExe pkgs.tailwindcss;
  php = lib.getExe pkgs.php83;
in
  with pkgs; [
    (writers.writeBashBin "serve" ''
      cd public && \
      ${php} -S localhost:8000 index.php
    '')

    (writers.writeBashBin "btw" ''
      ${tw} -i ./src/views/css/TW_input.css -o ./src/views/css/tailwind.css --minify
    '')

    (writers.writeBashBin "wtw" ''
      ${tw} -i ./src/views/css/TW_input.css -o ./src/views/css/tailwind.css --watch
    '')
  ]
