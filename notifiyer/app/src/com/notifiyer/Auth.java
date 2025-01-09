package com.notifiyer;

import android.content.Intent;
import android.net.Uri;
import android.util.Log;
import android.widget.Toast;

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
    }

  public
    void signIn()
    {
        Intent signInIntent = mGoogleSignInClient.getSignInIntent();
        this.mActivity.startActivityForResult(signInIntent, RC_SIGN_IN);
    }

  public
    void signOut()
    {
        mAuth.signOut();

        mGoogleSignInClient.signOut().addOnCompleteListener(
          this.mActivity, task->{
              Toast
                .makeText(
                  this.mActivity, "Signed out successfully", Toast.LENGTH_SHORT)
                .show();
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

        mAuth.signInWithCredential(credential)
          .addOnCompleteListener(
            this.mActivity, task->{
                if (task.isSuccessful()) {
                    firebaseGetUserInfo(subId);
                } else {
                    Log.w("Oauth", "Sign-in failed", task.getException());
                }
            });
    }

  private
    void firebaseGetUserInfo(String sub)
    {
        FirebaseUser user = mAuth.getCurrentUser();
        String email = user.getEmail();
        Uri photo = user.getPhotoUrl();

        FirebaseMessaging.getInstance().getToken().addOnCompleteListener(task->{
            if (!task.isSuccessful()) {
                Log.w("fireshit", "Fetching FCM token failed");
                return;
            }
            String token = task.getResult();

            Log.d("Oauth",
                  "Sign-in successful: emal: " + email + " sub: " + sub +
                    " token: " + token + " photo: " + photo.toString());
        });
    }
}
