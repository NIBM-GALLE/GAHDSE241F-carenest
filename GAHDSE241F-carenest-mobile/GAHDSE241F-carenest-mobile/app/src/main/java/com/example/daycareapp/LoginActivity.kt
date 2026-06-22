package com.example.daycareapp

import android.content.Intent
import android.os.Bundle
import android.widget.Button
import android.widget.EditText
import android.widget.ImageView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import kotlinx.coroutines.*

class LoginActivity : AppCompatActivity() {

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_login)

        val backIcon: ImageView = findViewById(R.id.backIcon)
        val usernameEditText: EditText = findViewById(R.id.userName)
        val passwordEditText: EditText = findViewById(R.id.password)
        val loginButton: Button = findViewById(R.id.btn_sign_in)

        backIcon.setOnClickListener {
            finish()
        }

        loginButton.setOnClickListener {
            val username = usernameEditText.text.toString().trim()
            val password = passwordEditText.text.toString().trim()

            if (username.isEmpty()) {
                usernameEditText.error = "Please enter your username"
                usernameEditText.requestFocus()
            } else if (password.isEmpty()) {
                passwordEditText.error = "Please enter your password"
                passwordEditText.requestFocus()
            } else {
                checkLoginWithDatabase(username, password, loginButton, passwordEditText)
            }
        }
    }

    private fun checkLoginWithDatabase(username: String, password: String, loginButton: Button, passwordEditText: EditText) {
        loginButton.isEnabled = false
        loginButton.text = "LOGGING IN..."

        CoroutineScope(Dispatchers.IO).launch {
            try {
                val request = LoginRequest(username, password)
                val response = RetrofitClient.instance.login(request)

                withContext(Dispatchers.Main) {
                    loginButton.isEnabled = true
                    loginButton.text = "LOGIN"

                    if (response.isSuccessful && response.body()?.success == true) {
                        val user = response.body()!!

                        val sharedPref = getSharedPreferences("DaycarePref", MODE_PRIVATE)
                        sharedPref.edit().apply {
                            putInt("user_id", user.user_id)
                            putString("user_name", user.name)
                            putString("user_email", user.email)
                            putString("user_phone", user.phone)
                            putString("user_role", user.role)
                            apply()
                        }

                        Toast.makeText(this@LoginActivity, "Welcome ${user.name}!", Toast.LENGTH_LONG).show()

                        val intent = Intent(this@LoginActivity, DashboardActivity::class.java)
                        startActivity(intent)
                        finish()

                    } else {
                        val errorMsg = response.body()?.message ?: "Invalid username or password"
                        Toast.makeText(this@LoginActivity, errorMsg, Toast.LENGTH_LONG).show()
                        passwordEditText.text?.clear()
                        passwordEditText.requestFocus()
                    }
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    loginButton.isEnabled = true
                    loginButton.text = "LOGIN"
                    Toast.makeText(this@LoginActivity, "Connection Error: ${e.message}", Toast.LENGTH_LONG).show()
                }
            }
        }
    }
}