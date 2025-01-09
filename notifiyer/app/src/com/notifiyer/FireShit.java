package com.notifiyer;

import android.util.Log;

import com.google.firebase.auth.FirebaseAuth;
import com.google.firebase.messaging.FirebaseMessagingService;
import com.google.firebase.messaging.RemoteMessage;

import java.util.Map;

public
class FireShit extends FirebaseMessagingService
{
  public
    static Auth auth;

    @Override public void onMessageReceived(RemoteMessage remoteMessage)
    {
        Map<String, String> data = remoteMessage.getData();
        Log.w("fireshit", "msg recived: " + data.toString());
        String text = data.get("text");
        String title = data.get("title");

        Notify.showNotification(title, text, this);
    }

    @Override public void onNewToken(String token)
    {
        super.onNewToken(token);

        if (FirebaseAuth.getInstance().getCurrentUser() == null) {
            Log.e("Oauth", "User not signed in. new token: " + token);
            return;
        }

        auth.updateInfo(()->{});
    }
}
