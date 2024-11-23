package com.notifiyer;

import android.app.NotificationManager;
import android.os.Bundle;
import android.os.Handler;
import android.util.Log;
import android.widget.Button;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;

import com.onesignal.OneSignal;

public
class MainActivity extends AppCompatActivity
{
    Notify notify;
  private
    static final String ONESIGNAL_APP_ID =
      "8fb119b2-c4da-4a33-95c4-44e8ea21697a";

    @Override protected void onCreate(Bundle savedInstanceState)
    {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.main);

        this.notify = new Notify(
          (NotificationManager)getSystemService(NOTIFICATION_SERVICE));

        Runnable n =
          ()->this.notify.showNotification(this, "title for notif", "msg for notif");

        Button button = findViewById(R.id.my_button);
        button.setOnClickListener(view->{
            new Handler().postDelayed(n, 2000);
            Log.w("button", "button click");
        });

        OneSignal.initWithContext(this, ONESIGNAL_APP_ID);
    }
}
