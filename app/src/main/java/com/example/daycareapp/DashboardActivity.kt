package com.example.daycareapp

import android.content.Intent
import android.os.Bundle
import android.widget.ImageView
import android.widget.LinearLayout
import android.widget.TextView
import androidx.appcompat.app.AppCompatActivity
import androidx.cardview.widget.CardView

class DashboardActivity : AppCompatActivity() {

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_dashboard)

        val backIcon: ImageView = findViewById(R.id.backIcon)
        val profileImageView: ImageView = findViewById(R.id.profileImageView)
        val mealPlanCard: CardView = findViewById(R.id.MealPlanCard)
        val chatCard: CardView = findViewById(R.id.ChatCard)
        val activityCard: CardView = findViewById(R.id.ActivityCard)

        // Back button functionality
        backIcon.setOnClickListener {
            finish() // Close current activity
        }

        // Profile image click listener
        profileImageView.setOnClickListener {
            // Navigate to profile activity (example)
            val intent = Intent(this, ProfileActivity::class.java)
            startActivity(intent)
        }

        // Meal Plan Card click listener
        mealPlanCard.setOnClickListener {
            // Navigate to meal plan activity (example)
            val intent = Intent(this, MealPlanActivity::class.java)
            startActivity(intent)
        }

        // Chat Card click listener
        chatCard.setOnClickListener {
            // Navigate to chat activity (example)
            val intent = Intent(this, ChatActivity::class.java)
            startActivity(intent)
        }

        // Activity Card click listener
        activityCard.setOnClickListener {
            // Navigate to activities activity (example)
            val intent = Intent(this, ActivityPageActivity::class.java)
            startActivity(intent)
        }
    }
}
