package com.notifiyer;

import android.content.Intent;
import android.content.SharedPreferences;
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

import com.android.volley.AuthFailureError;
import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.toolbox.StringRequest;
import com.android.volley.toolbox.Volley;
import com.bumptech.glide.Glide;
import com.google.firebase.FirebaseApp;
import com.google.firebase.auth.FirebaseAuth;

import java.util.HashMap;
import java.util.Map;

import org.json.JSONObject;

public
class MainActivity extends AppCompatActivity
{
  private
    final String API_URL = "https://yata.fly.dev";

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
        FireShit.auth = this.auth;

        this.profileImageView = findViewById(R.id.profile_image_view);
        this.usernameTextView = findViewById(R.id.username_text_view);
        this.signInOutButton = findViewById(R.id.btn_sign_in_out);
        this.rcNotifSwitch = findViewById(R.id.rc_notif_switch);

        updateUI();

        this.rcNotifSwitch.setOnCheckedChangeListener((buttonView, isChecked)->{
            if (isChecked) {
                Log.d("Switch", "Switch is ON");
                updateReciveNotification(true);
            } else {
                Log.d("Switch", "Switch is OFF");
                updateReciveNotification(false);
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
            usernameTextView.setVisibility(View.VISIBLE);

            Glide.with(this).load(profileImageUri).into(profileImageView);

            usernameTextView.setText(this.username);
            signInOutButton.setText("Sign Out");

            updateSwitchState();
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
    void updateSwitchState()
    {
        String url = this.API_URL + "/api/get-notification-status";
        RequestQueue queue = Volley.newRequestQueue(this);

        StringRequest postRequest = new StringRequest(
          Request.Method.POST,
          url,
          response->{
              Log.d("SwitchStateResponse", response.toString());

              try {
                  JSONObject json = new JSONObject(response);

                  if (json.get("wants_notifications").equals("true")) {
                      rcNotifSwitch.setChecked(true);
                  } else {
                      rcNotifSwitch.setChecked(false);
                  }

                  rcNotifSwitch.setVisibility(View.VISIBLE);

              } catch (Exception e) {
                  e.printStackTrace();
              }
          },
          error->{ Log.e("Error", error.toString()); })
        {
            @Override public Map<String, String> getHeaders()
              throws AuthFailureError
            {
                Map<String, String> headers = new HashMap<>();

                headers.put("Cookie", "jwt=" + getJWT());
                return headers;
            }

            @Override protected Map<String, String> getParams()
              throws AuthFailureError
            {
                Map<String, String> params = new HashMap<>();
                params.put("sub", sub);
                return params;
            }
        };

        queue.add(postRequest);
    }

  private
    void updateReciveNotification(boolean wants_notifications)
    {
        String url = this.API_URL + "/api/update-notification-status";
        RequestQueue queue = Volley.newRequestQueue(this);

        StringRequest postRequest = new StringRequest(
          Request.Method.POST,
          url,
          response->{ Log.d("Response", response.toString()); },
          error->{ Log.e("Error", error.toString()); })
        {
            @Override public Map<String, String> getHeaders()
              throws AuthFailureError
            {
                Map<String, String> headers = new HashMap<>();
                headers.put("Cookie", "jwt=" + getJWT());
                return headers;
            }

            @Override protected Map<String, String> getParams()
              throws AuthFailureError
            {
                Map<String, String> params = new HashMap<>();
                params.put("sub", sub);
                params.put("wants_notifications", wants_notifications ? "true" : "false");
                return params;
            }
        };

        queue.add(postRequest);
    }

  public
    void updateDeviceToken()
    {
        String url = this.API_URL + "/api/set-fcm-token";
        RequestQueue queue = Volley.newRequestQueue(this);

        StringRequest postRequest = new StringRequest(
          Request.Method.POST,
          url,
          response->{ Log.d("Response", response.toString()); },
          error->{ Log.e("Error", error.toString()); })
        {

            @Override public Map<String, String> getHeaders()
              throws AuthFailureError
            {
                Map<String, String> headers = new HashMap<>();

                headers.put("Cookie", "jwt=" + getJWT());
                return headers;
            }

            @Override protected Map<String, String> getParams()
              throws AuthFailureError
            {
                Map<String, String> params = new HashMap<>();
                params.put("sub", sub);
                params.put("token", fcm_token);
                return params;
            }
        };

        queue.add(postRequest);
    }

  public
    void storeJWT(String token)
    {
        SharedPreferences prefs =
          getSharedPreferences("app_prefs", MODE_PRIVATE);
        SharedPreferences.Editor editor = prefs.edit();
        editor.putString("jwt", token);
        editor.apply();
    }

  public
    String getJWT()
    {
        SharedPreferences prefs =
          getSharedPreferences("app_prefs", MODE_PRIVATE);
        return prefs.getString("jwt", null);
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
