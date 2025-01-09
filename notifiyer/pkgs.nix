{pkgs, ...}:
with pkgs; [
  android-studio
  openjdk
  gradle
  jdk
  jdt-language-server
  android-tools

  (pkgs.writers.writeBashBin "prun" ''
    # 192.168.1.3:42531
    # adb connect 192.168.1.3:42531
    # adb -s $dev uninstall com.notifiyer

    dev=""

    if [[ "$1" == "e" ]]; then
        dev="emulator-5554"
    else
        dev="192.168.1.2:41251"
    fi

    adb -s $dev install app/build/outputs/apk/debug/app-debug.apk
    adb -s $dev shell am start -n com.notifiyer/.MainActivity
  '')
]
