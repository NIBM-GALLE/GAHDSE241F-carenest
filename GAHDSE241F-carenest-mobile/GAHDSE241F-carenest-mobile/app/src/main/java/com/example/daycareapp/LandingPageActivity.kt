package com.example.daycareapp // Replace with your actual package name

import android.content.Intent
import android.os.Bundle
import android.widget.Button
import android.widget.ImageView
import androidx.appcompat.app.AppCompatActivity
import com.example.daycareapp.R


class LandingPageActivity : AppCompatActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_landingpage)


        // Get Started Button functionality
        val getStartedButton: Button = findViewById(R.id.btn_started)
        getStartedButton.setOnClickListener {
            // Navigate to SignUpActivity
            val intent = Intent(this, LoginActivity::class.java)
            startActivity(intent)
        }
    }
}



