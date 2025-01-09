package com.notifiyer;

import android.util.Log;

import com.google.firebase.auth.FirebaseAuth;
import com.google.firebase.auth.FirebaseUser;
import com.google.firebase.auth.GoogleAuthProvider;
import com.google.firebase.auth.UserInfo;
import com.google.firebase.messaging.FirebaseMessagingService;
import com.google.firebase.messaging.RemoteMessage;

import java.util.Map;

public
class FireShit extends FirebaseMessagingService
{
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

        FirebaseUser user = FirebaseAuth.getInstance().getCurrentUser();

        if (user == null) {
            Log.e("Oauth", "User not signed in. new token tho: " + token);
            return;
        }

        String subId = null;
        for (UserInfo profile : user.getProviderData()) {
            if (!GoogleAuthProvider.PROVIDER_ID.equals(profile.getProviderId()))
                continue;

            subId = profile.getUid();
            break;
        }

        Log.w("Oauth", "new token: " + token);
        Log.w("Oauth", "sub after token: " + subId);
    }
}
