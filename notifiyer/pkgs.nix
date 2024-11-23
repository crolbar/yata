{pkgs, ...}:
with pkgs; [
  android-studio
  openjdk
  gradle
  jdk
  jdt-language-server

  (pkgs.writers.writeBashBin "prun" ''
    # 192.168.1.3:42531
    # adb connect 192.168.1.3:42531
    # adb -s $dev uninstall com.notifiyer

    dev=""

    if [[ "$1" == "e" ]]; then
        dev="emulator-5554"
    else
        dev="adb-73d726a0-gl9U2r._adb-tls-connect._tcp"
    fi

    adb -s $dev install app/build/outputs/apk/debug/app-debug.apk
    adb -s $dev shell am start -n com.notifiyer/.MainActivity
  '')
]
