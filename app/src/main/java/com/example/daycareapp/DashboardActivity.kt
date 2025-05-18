package com.example.daycareapp

import android.content.Intent
import android.os.Bundle
import android.widget.ImageView
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

        val sharedPref = getSharedPreferences("ProfilePref", MODE_PRIVATE)
        val imageUriString = sharedPref.getString("profile_image_uri", null)

        if (imageUriString != null) {
            val imageUri = android.net.Uri.parse(imageUriString)
            profileImageView.setImageURI(imageUri)
        }


        // Navigate to Profile Page
        profileImageView.setOnClickListener {
            val intent = Intent(this, ProfileActivity::class.java)
            startActivity(intent)
        }

        // Meal Plan Card click listener
        mealPlanCard.setOnClickListener {
            val intent = Intent(this, MealPlanActivity::class.java)
            startActivity(intent)
        }

        // Chat Card click listener
        chatCard.setOnClickListener {
            val intent = Intent(this, ChatActivity::class.java)
            startActivity(intent)
        }

        // Activity Card click listener
        activityCard.setOnClickListener {
            val intent = Intent(this, ActivityPageActivity::class.java)
            startActivity(intent)
        }
    }
}
