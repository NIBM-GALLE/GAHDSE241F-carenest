package com.example.daycareapp

data class Message(
    val sender: String,
    val content: String,
    val isParent: Boolean,
    val time: String,
    val imageResId: Int? = null,
    val imageUrl: String? = null  // <-- ONLY ADD THIS LINE
)