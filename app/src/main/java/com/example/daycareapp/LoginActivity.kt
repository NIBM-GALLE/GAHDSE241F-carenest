package com.example.daycareapp

import android.content.Intent
import android.os.Bundle
import android.widget.Button
import android.widget.EditText
import android.widget.ImageView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity

class LoginActivity : AppCompatActivity() {

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_login)

        // Find UI elements
        val backIcon: ImageView = findViewById(R.id.backIcon)
        val usernameEditText: EditText = findViewById(R.id.userName)
        val passwordEditText: EditText = findViewById(R.id.password)
        val loginButton: Button = findViewById(R.id.btn_sign_in)

        // Handle back button click (if needed)
        backIcon.setOnClickListener {
            finish() // Closes the current activity and goes back to the previous one
        }

        // Handle login button click
        loginButton.setOnClickListener {
            val username = usernameEditText.text.toString().trim()
            val password = passwordEditText.text.toString().trim()

            // Validation checks
            if (username.isEmpty()) {
                usernameEditText.error = "Please enter your username"
                usernameEditText.requestFocus()
            } else if (password.isEmpty()) {
                passwordEditText.error = "Please enter your password"
                passwordEditText.requestFocus()
            } else if (password.length < 6) {
                passwordEditText.error = "Password must be at least 6 characters"
                passwordEditText.requestFocus()
            } else {
                // Proceed to DashboardActivity if validation is successful
                val intent = Intent(this, DashboardActivity::class.java)
                startActivity(intent)
                finish()
            }
        }
    }
}


