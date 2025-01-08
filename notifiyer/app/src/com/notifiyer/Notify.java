package com.notifiyer;

import android.app.NotificationChannel;
import android.app.NotificationManager;
import android.content.Context;
import android.os.Build;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.app.NotificationCompat;

public
class Notify extends AppCompatActivity
{
  private
    static final String CHANNEL_ID = "default_channel";

  private
    static void createNotificationChannel(
      NotificationManager notificationManager)
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
    static void showNotification(String title, String text, Context ctx)
    {
        NotificationManager notificationManager =
          (NotificationManager)ctx.getSystemService(
            Context.NOTIFICATION_SERVICE);

        Notify.createNotificationChannel(notificationManager);

        NotificationCompat.Builder builder =
          new NotificationCompat.Builder(ctx, CHANNEL_ID)
            .setSmallIcon(R.drawable.notif_icon)
            .setContentTitle(title)
            .setContentText(text)
            .setPriority(NotificationCompat.PRIORITY_DEFAULT);

        notificationManager.notify(0, builder.build());
    }
}
