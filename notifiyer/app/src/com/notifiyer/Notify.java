package com.notifiyer;

import android.app.NotificationChannel;
import android.app.NotificationManager;
import android.os.Build;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.app.NotificationCompat;

public
class Notify extends AppCompatActivity
{
  public
    static final String CHANNEL_ID = "notifiyer_channel";
  public
    NotificationManager notificationManager;

    Notify(NotificationManager nm)
    {
        notificationManager = nm;
        this.createNotificationChannel();
    }

  private
    void createNotificationChannel()
    {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            NotificationChannel channel =
              new NotificationChannel(CHANNEL_ID,
                                      "Main Channel",
                                      NotificationManager.IMPORTANCE_DEFAULT);

            notificationManager.createNotificationChannel(channel);
        }
    }

  public
    void showNotification(MainActivity ma, String title, String msg)
    {
        NotificationCompat.Builder builder =
          new NotificationCompat.Builder(ma, CHANNEL_ID)
            .setSmallIcon(R.drawable.notif_icon)
            .setContentTitle(title)
            .setContentText(msg)
            .setPriority(NotificationCompat.PRIORITY_DEFAULT);

        this.notificationManager.notify(1, builder.build());
    }
}
