package com.notifiyer;

import android.app.NotificationManager;
import android.util.Log;
import android.os.Bundle;
import android.os.Handler;
import android.widget.Button;
import androidx.appcompat.app.AppCompatActivity;

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
