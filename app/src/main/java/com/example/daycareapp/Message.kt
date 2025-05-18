package com.example.daycareapp

data class Message(
    val sender: String,
    val content: String,         // This is the text message
    val isParent: Boolean,
    val time: String,
    val imageResId: Int? = null  // Optional image (if used)
)

