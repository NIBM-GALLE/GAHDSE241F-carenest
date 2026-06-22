package com.example.daycareapp

import retrofit2.Retrofit
import retrofit2.Response

import retrofit2.converter.gson.GsonConverterFactory
import retrofit2.http.*

object RetrofitClient {
    private const val BASE_URL = "http://10.0.2.2/daycare_system/"

    val instance: ApiService by lazy {
        Retrofit.Builder()
            .baseUrl(BASE_URL)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
            .create(ApiService::class.java)
    }
}

interface ApiService {
    @POST("api/mobile/login.php")
    @Headers("Content-Type: application/json")
    suspend fun login(@Body request: LoginRequest): retrofit2.Response<LoginResponse>

    @GET("api/mobile/get_children.php")
    suspend fun getChildren(
        @Query("parent_id") parentId: Int
    ): retrofit2.Response<ChildrenResponse>

    @GET("api/mobile/get_activities.php")
    suspend fun getActivities(
        @Query("parent_id") parentId: Int,
        @Query("child_id") childId: Int = 0,
        @Query("date") date: String = ""
    ): retrofit2.Response<ActivitiesResponse>

    @POST("api/mobile/chat.php")
    @Headers("Content-Type: application/json")
    suspend fun sendMessage(@Body request: SendMessageRequest): retrofit2.Response<SendMessageResponse>

    @GET("api/mobile/chat.php")
    suspend fun getMessages(@Query("parent_id") parentId: Int): retrofit2.Response<GetMessagesResponse>

    @GET("api/mobile/get_meal_plan.php")
    suspend fun getMealPlan(): Response<MealPlanResponse>
}