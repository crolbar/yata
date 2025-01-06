package com.notifiyer;

import android.app.NotificationManager;
import android.util.Log;
import android.os.Bundle;
import android.os.Handler;
import android.widget.Button;
import androidx.appcompat.app.AppCompatActivity;


import android.Manifest;
import android.content.pm.PackageManager;
import android.os.Build;
import androidx.annotation.RequiresApi;
import androidx.core.app.ActivityCompat;

public
class MainActivity extends AppCompatActivity
{
    Notify notify;

    @Override protected void onCreate(Bundle savedInstanceState)
    {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.main);
        this.notify = new Notify(
          (NotificationManager)getSystemService(NOTIFICATION_SERVICE), this);

        notifyButton();

        FireShit.initFireShit(this, this.notify);
    }

    @Override
    protected void onStart() {
        super.onStart();
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            requestNotificationPermission();
        }
    }


    @RequiresApi(api = Build.VERSION_CODES.TIRAMISU)
    private void requestNotificationPermission() {
        if (ActivityCompat.checkSelfPermission(this, Manifest.permission.POST_NOTIFICATIONS) != PackageManager.PERMISSION_GRANTED) {
            ActivityCompat.requestPermissions(
                    this,
                    new String[]{Manifest.permission.POST_NOTIFICATIONS},
                    1
            );
        }
    }

  private
    void notifyButton()
    {
        Runnable n =
          ()->this.notify.showNotification("title for notif", "msg for notif");

        Button button = findViewById(R.id.my_button);
        button.setOnClickListener(view->{
            new Handler().postDelayed(n, 2000);
            Log.w("button", "button click");
        });
    }
}
