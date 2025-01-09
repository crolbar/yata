package com.notifiyer;

import android.content.Intent;
import android.util.Log;
import android.os.Bundle;
import android.os.Handler;
import androidx.appcompat.app.AppCompatActivity;

import android.Manifest;
import android.content.pm.PackageManager;
import android.os.Build;
import androidx.core.app.ActivityCompat;
import com.google.firebase.FirebaseApp;

public
class MainActivity extends AppCompatActivity
{
    Auth auth;

    @Override protected void onCreate(Bundle savedInstanceState)
    {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.main);

        FirebaseApp.initializeApp(this);

        this.auth = new Auth(this);



        findViewById(R.id.my_button).setOnClickListener(view->{
            new Handler().postDelayed(
              ()->Notify.showNotification(
                "title for notif", "msg for notif", this),
              2000);
            Log.w("button", "button click");
        });

        findViewById(R.id.btn_google_sign_in)
          .setOnClickListener(v->this.auth.signIn());

        findViewById(R.id.btn_google_sign_out)
          .setOnClickListener(v->this.auth.signOut());
    }

    @Override protected void onStart()
    {
        super.onStart();
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            requestNotificationPermission();
        }
    }

  private
    void requestNotificationPermission()
    {
        String permsKind = Manifest.permission.POST_NOTIFICATIONS;
        int perms = ActivityCompat.checkSelfPermission(this, permsKind);

        if (perms != PackageManager.PERMISSION_GRANTED) {
            ActivityCompat.requestPermissions(
              this, new String[]{ permsKind }, 1);
        }
    }

    @Override protected void onActivityResult(int requestCode,
                                              int resultCode,
                                              Intent data)
    {
        super.onActivityResult(requestCode, resultCode, data);
        auth.handleSignInResult(requestCode, resultCode, data);
    }
}
