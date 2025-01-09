package com.notifiyer;

import android.content.Intent;
import android.util.Log;
import android.widget.Toast;

import com.google.firebase.auth.UserInfo;
import com.google.firebase.messaging.FirebaseMessaging;
import com.google.firebase.auth.AuthCredential;
import com.google.firebase.auth.FirebaseAuth;
import com.google.firebase.auth.FirebaseUser;
import com.google.firebase.auth.GoogleAuthProvider;
import com.google.android.gms.common.api.ApiException;
import com.google.android.gms.auth.api.signin.GoogleSignIn;
import com.google.android.gms.auth.api.signin.GoogleSignInAccount;
import com.google.android.gms.auth.api.signin.GoogleSignInClient;
import com.google.android.gms.auth.api.signin.GoogleSignInOptions;
import com.google.android.gms.tasks.Task;

public
class Auth
{
  private
    FirebaseAuth mAuth;
  private
    GoogleSignInClient mGoogleSignInClient;
  private
    final int RC_SIGN_IN = 100;
  private
    MainActivity mActivity;

    Auth(MainActivity activity)
    {
        GoogleSignInOptions gso =
          new GoogleSignInOptions.Builder(GoogleSignInOptions.DEFAULT_SIGN_IN)
            .requestIdToken(activity.getString(R.string.default_web_client_id))
            .requestEmail()
            .build();

        this.mGoogleSignInClient = GoogleSignIn.getClient(activity, gso);
        this.mAuth = FirebaseAuth.getInstance();
        this.mActivity = activity;

        this.mGoogleSignInClient.silentSignIn().addOnCompleteListener(task -> {
            if (task.isSuccessful()) {
                GoogleSignInAccount account = task.getResult();

                if (account != null) {
                    String googleIdToken = account.getIdToken();

                    if (googleIdToken != null) {
                        this.mActivity.storeJWT(googleIdToken);
                    }
                }
            } else {
                Log.e("Oauth", "Silent sign-in failed", task.getException());
            }
        });
    }

  public
    void signInOut()
    {
        if (FirebaseAuth.getInstance().getCurrentUser() == null) {
            this.signIn();
            return;
        }

        this.signOut();
    }

  private
    void signIn()
    {
        Intent signInIntent = mGoogleSignInClient.getSignInIntent();
        this.mActivity.startActivityForResult(signInIntent, RC_SIGN_IN);
    }

  private
    void signOut()
    {
        mAuth.signOut();

        mGoogleSignInClient.signOut().addOnCompleteListener(
          this.mActivity, task->{
              Toast
                .makeText(
                  this.mActivity, "Signed out successfully", Toast.LENGTH_SHORT)
                .show();

              this.mActivity.updateUI();
          });
    }

  public
    void handleSignInResult(int requestCode, int resultCode, Intent data)
    {
        if (requestCode == RC_SIGN_IN) {
            Task<GoogleSignInAccount> task =
              GoogleSignIn.getSignedInAccountFromIntent(data);

            try {
                GoogleSignInAccount account =
                  task.getResult(ApiException.class);

                firebaseAuthWithGoogle(account);
            } catch (ApiException e) {
                Log.w("Oauth", "Google sign-in failed", e);
            }
        }
    }

  private
    void firebaseAuthWithGoogle(GoogleSignInAccount account)
    {
        AuthCredential credential =
          GoogleAuthProvider.getCredential(account.getIdToken(), null);

        String subId = account.getId();
        this.mActivity.storeJWT(account.getIdToken());

        mAuth.signInWithCredential(credential)
          .addOnCompleteListener(
            this.mActivity, task->{
                if (task.isSuccessful()) {
                    this.mActivity.updateUI();

                    Log.d("Oauth",
                          "Sign-in successful: emal: " + account.getEmail() +
                            " sub: " + subId);
                } else {
                    Log.w("Oauth", "Sign-in failed", task.getException());
                }
            });
    }

  public
    void updateInfo(Runnable callback)
    {
        FirebaseUser user = mAuth.getCurrentUser();

        FirebaseMessaging.getInstance().getToken().addOnCompleteListener(task->{
            if (!task.isSuccessful()) {
                Log.w("fireshit", "Fetching FCM token failed");
                return;
            }
            String token = task.getResult();

            String sub = "";
            for (UserInfo profile : user.getProviderData()) {
                if (!GoogleAuthProvider.PROVIDER_ID.equals(
                      profile.getProviderId()))
                    continue;

                sub = profile.getUid();
                break;
            }

            Log.d("Oauth", "sub: " + sub + "token: " + token);

            this.mActivity.sub = sub;
            this.mActivity.fcm_token = token;
            this.mActivity.profileImageUri = user.getPhotoUrl().toString();
            this.mActivity.username = user.getDisplayName();

            callback.run();
            this.mActivity.updateDeviceToken();
        });
    }
}
