package com.example.daycareapp

import android.content.Intent
import android.os.Bundle
import android.widget.ImageView
import android.widget.LinearLayout
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

        // Bottom Navigation Views
        val bottomNavHome: LinearLayout = findViewById(R.id.bottomNavHome)
        val bottomNavMealPlan: LinearLayout = findViewById(R.id.bottomNavMealPlan)
        val bottomNavChat: LinearLayout = findViewById(R.id.bottomNavChat)
        val bottomNavActivities: LinearLayout = findViewById(R.id.bottomNavActivities)
        val bottomNavProfile: LinearLayout = findViewById(R.id.bottomNavProfile)

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

        // ========== BOTTOM NAVIGATION CLICK LISTENERS ==========

        // Bottom Navigation - Home
        bottomNavHome.setOnClickListener {
            // Already on Dashboard, just refresh or do nothing
            // You can add a toast or refresh animation if desired
        }

        // Bottom Navigation - Meal Plan
        bottomNavMealPlan.setOnClickListener {
            val intent = Intent(this, MealPlanActivity::class.java)
            startActivity(intent)
        }

        // Bottom Navigation - Chat
        bottomNavChat.setOnClickListener {
            val intent = Intent(this, ChatActivity::class.java)
            startActivity(intent)
        }

        // Bottom Navigation - Activities
        bottomNavActivities.setOnClickListener {
            val intent = Intent(this, ActivityPageActivity::class.java)
            startActivity(intent)
        }

        // Bottom Navigation - Profile
        bottomNavProfile.setOnClickListener {
            val intent = Intent(this, ProfileActivity::class.java)
            startActivity(intent)
        }
    }
}