package com.notifiyer;

import android.util.Log;
import com.google.firebase.FirebaseApp;
import com.google.firebase.messaging.FirebaseMessaging;
import com.google.firebase.messaging.FirebaseMessagingService;
import com.google.firebase.messaging.RemoteMessage;

public
class FireShit extends FirebaseMessagingService
{
  public
    static void initFireShit(MainActivity ma)
    {
        FirebaseApp.initializeApp(ma);

        FirebaseMessaging.getInstance()
          .subscribeToTopic("topic")
          .addOnCompleteListener(task->{
              if (task.isSuccessful()) {
                  Log.w("fireshit", "Subscribed to topic!");
              }
          });

        // Get the FCM registration token
        FirebaseMessaging.getInstance().getToken().addOnCompleteListener(task->{
            if (!task.isSuccessful()) {
                System.err.println("Fetching FCM token failed");
                return;
            }
            String token = task.getResult();

            Log.w("fireshit", "Device Token: " + token);
        });
    }

    @Override public void onMessageReceived(RemoteMessage remoteMessage)
    {
        String message = remoteMessage.getData().toString();
        Log.w("fireshit", "msg recived: " + message);

        Notify.showNotification("title for notif", message, this);
    }
}
