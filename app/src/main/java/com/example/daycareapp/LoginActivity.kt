package com.example.daycareapp

import android.content.Intent
import android.os.Bundle
import android.widget.*
import androidx.appcompat.app.AppCompatActivity
import com.android.volley.Request
import com.android.volley.toolbox.StringRequest
import com.android.volley.toolbox.Volley
import org.json.JSONObject

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
                loginUser(username, password)
            }
        }
    }

    private fun loginUser(username: String, password: String) {
        val url = "http://10.0.2.2/carenest_daycare/GAHDSE241F-carenest/backend/login.php"  // Use your real IP for real device
        val queue = Volley.newRequestQueue(this)

        val stringRequest = object : StringRequest(Request.Method.POST, url,
            { response ->
                try {
                    val jsonResponse = JSONObject(response)
                    val status = jsonResponse.getString("status")

                    if (status == "success") {
                        Toast.makeText(this, "Login successful!", Toast.LENGTH_SHORT).show()
                        val intent = Intent(this, DashboardActivity::class.java)
                        startActivity(intent)
                        finish()
                    } else {
                        Toast.makeText(this, jsonResponse.getString("message"), Toast.LENGTH_SHORT).show()
                    }
                } catch (e: Exception) {
                    Toast.makeText(this, "Invalid server response", Toast.LENGTH_SHORT).show()
                }
            },
            { error ->
                Toast.makeText(this, "Error: ${error.message}", Toast.LENGTH_LONG).show()
            }) {
            override fun getParams(): Map<String, String> {
                val params = HashMap<String, String>()
                params["username"] = username
                params["password"] = password
                return params
            }
        }

        queue.add(stringRequest)
    }
}