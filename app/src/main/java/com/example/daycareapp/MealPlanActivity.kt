package com.example.daycareapp

import android.os.Bundle
import android.widget.TextView
import android.widget.ImageView
import androidx.appcompat.app.AppCompatActivity

class MealPlanActivity : AppCompatActivity() {

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_mealplan)

        val backIcon: ImageView = findViewById(R.id.backIcon)
        backIcon.setOnClickListener {
            finish() // This will go back to the previous screen
        }



        // Get references to TextViews
        val mealPlanDateTextView: TextView = findViewById(R.id.mealPlanDateTextView)
        val breakfastTextView: TextView = findViewById(R.id.breakfastTextView)
        val lunchTextView: TextView = findViewById(R.id.lunchTextView)
        val snacksTextView: TextView = findViewById(R.id.snacksTextView)

        // Example: Updating UI with fetched data (Replace with actual SQL data)
        mealPlanDateTextView.text = "Meal Plan for: March 30, 2025"
        breakfastTextView.text = "• Scrambled eggs with toast"
        lunchTextView.text = "• Grilled fish with steamed vegetables"
        snacksTextView.text = "• Fresh fruit salad with yogurt"
    }
}
