package com.example.daycareapp

// Login Models
data class LoginRequest(val username: String, val password: String)

data class LoginResponse(
    val success: Boolean,
    val user_id: Int,
    val name: String,
    val email: String,
    val phone: String,
    val role: String,
    val message: String?
)

// Children Models
data class ChildrenResponse(
    val success: Boolean,
    val children: List<Child>
)

data class Child(
    val id: Int,
    val name: String,
    val date_of_birth: String? = null,
    val parent_id: Int
)

// Activity Models
data class Activity(
    val id: Int,
    val child_id: Int,
    val child_name: String,
    val staff_name: String,
    val description: String,
    val date: String,
    val formatted_date: String,
    val short_date: String,
    val image_url: String
)

data class ActivitiesResponse(
    val success: Boolean,
    val activities: List<Activity>,
    val total: Int,
    val error: String? = null
)

// Chat Models
data class SendMessageRequest(val sender_id: Int, val message: String)

data class SendMessageResponse(val success: Boolean, val message: String)

data class GetMessagesResponse(val success: Boolean, val messages: List<ChatMessage>)

data class ChatMessage(
    val id: Int,
    val sender_id: Int,
    val sender_name: String,
    val sender_role: String,
    val message: String,
    val timestamp: String,
    val image_url: String? = null
)

data class MealPlanResponse(
    val success: Boolean,
    val meal_plan: List<Meal>
)

data class Meal(
    val id: Int,
    val date: String,
    val breakfast: String,
    val lunch: String,
    val snacks: String,
    val notes: String?
)
