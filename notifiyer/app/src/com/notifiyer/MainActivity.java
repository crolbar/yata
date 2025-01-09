package com.notifiyer;

import android.content.Intent;
import android.os.Bundle;
import androidx.appcompat.app.AppCompatActivity;

import android.Manifest;
import android.content.pm.PackageManager;
import android.os.Build;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.Switch;
import android.widget.TextView;

import androidx.core.app.ActivityCompat;

import com.bumptech.glide.Glide;
import com.google.firebase.FirebaseApp;
import com.google.firebase.auth.FirebaseAuth;

public
class MainActivity extends AppCompatActivity
{
  private
    Auth auth;

  public
    String username = "";
  public
    String profileImageUri = "";

  public
    String fcm_token = "";
  public
    String sub = "";

  private
    ImageView profileImageView;
  private
    TextView usernameTextView;
  private
    Button signInOutButton;
  private
    Switch rcNotifSwitch;

    @Override protected void onCreate(Bundle savedInstanceState)
    {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.main);

        FirebaseApp.initializeApp(this);

        this.auth = new Auth(this);

        this.profileImageView = findViewById(R.id.profile_image_view);
        this.usernameTextView = findViewById(R.id.username_text_view);
        this.signInOutButton = findViewById(R.id.btn_sign_in_out);
        this.rcNotifSwitch = findViewById(R.id.rc_notif_switch);

        updateUI();

        this.rcNotifSwitch.setOnCheckedChangeListener((buttonView, isChecked)->{
            if (isChecked) {
                Log.d("Switch", "Switch is ON");
            } else {
                Log.d("Switch", "Switch is OFF");
            }
        });

        this.signInOutButton.setOnClickListener(new View.OnClickListener() {
            @Override public void onClick(View v)
            {
                auth.signInOut();
            }
        });
    }

  public
    void updateUI()
    {
        if (FirebaseAuth.getInstance().getCurrentUser() == null) {
            profileImageView.setVisibility(View.GONE);
            rcNotifSwitch.setVisibility(View.GONE);
            usernameTextView.setVisibility(View.GONE);

            usernameTextView.setText("");
            signInOutButton.setText("Sign In");

            return;
        }

        this.auth.updateInfo(()->{
            profileImageView.setVisibility(View.VISIBLE);
            rcNotifSwitch.setVisibility(View.VISIBLE);
            usernameTextView.setVisibility(View.VISIBLE);

            Glide.with(this).load(profileImageUri).into(profileImageView);

            usernameTextView.setText(this.username);
            signInOutButton.setText("Sign Out");
        });
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
