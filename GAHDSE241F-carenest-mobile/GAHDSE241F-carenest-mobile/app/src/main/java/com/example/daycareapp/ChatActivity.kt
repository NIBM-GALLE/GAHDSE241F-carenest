package com.example.daycareapp

import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.provider.MediaStore
import android.widget.*
import androidx.activity.result.contract.ActivityResultContracts
import androidx.appcompat.app.AppCompatActivity
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import kotlinx.coroutines.*
import okhttp3.*
import okhttp3.MediaType.Companion.toMediaTypeOrNull
import okhttp3.MultipartBody
import okhttp3.RequestBody.Companion.asRequestBody
import java.io.File
import java.io.FileOutputStream
import java.text.SimpleDateFormat
import java.util.*

class ChatActivity : AppCompatActivity() {

    private lateinit var recyclerChat: RecyclerView
    private lateinit var editMessage: EditText
    private lateinit var btnSend: ImageButton
    private lateinit var btnAttachment: ImageButton
    private lateinit var btnCall: ImageView
    private lateinit var backIcon: ImageView

    // Bottom Navigation
    private lateinit var bottomNavHome: LinearLayout
    private lateinit var bottomNavMealPlan: LinearLayout
    private lateinit var bottomNavChat: LinearLayout
    private lateinit var bottomNavActivities: LinearLayout
    private lateinit var bottomNavProfile: LinearLayout

    private val messageList = mutableListOf<Message>()
    private lateinit var adapter: ChatAdapter
    private var parentId: Int = 0
    private val client = OkHttpClient()

    private val pickImageLauncher = registerForActivityResult(ActivityResultContracts.StartActivityForResult()) { result ->
        if (result.resultCode == RESULT_OK) {
            val imageUri = result.data?.data
            imageUri?.let { sendImageMessage(it) }
        }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_chat)

        val sharedPref = getSharedPreferences("DaycarePref", MODE_PRIVATE)
        parentId = sharedPref.getInt("user_id", 0)

        initViews()
        setupBottomNavigation()
        loadMessages()
        startAutoRefresh()
    }

    private fun initViews() {
        recyclerChat = findViewById(R.id.recyclerChat)
        editMessage = findViewById(R.id.editMessage)
        btnSend = findViewById(R.id.btnSend)
        btnAttachment = findViewById(R.id.btnAttachment)
        btnCall = findViewById(R.id.btnCall)
        backIcon = findViewById(R.id.backIcon)

        bottomNavHome = findViewById(R.id.bottomNavHome)
        bottomNavMealPlan = findViewById(R.id.bottomNavMealPlan)
        bottomNavChat = findViewById(R.id.bottomNavChat)
        bottomNavActivities = findViewById(R.id.bottomNavActivities)
        bottomNavProfile = findViewById(R.id.bottomNavProfile)

        adapter = ChatAdapter(messageList)
        recyclerChat.layoutManager = LinearLayoutManager(this)
        recyclerChat.adapter = adapter

        btnSend.setOnClickListener {
            val messageText = editMessage.text.toString().trim()
            if (messageText.isNotEmpty()) {
                sendMessage(messageText, null)
                editMessage.text.clear()
            }
        }

        btnAttachment.setOnClickListener {
            val intent = Intent(Intent.ACTION_PICK, MediaStore.Images.Media.EXTERNAL_CONTENT_URI)
            pickImageLauncher.launch(intent)
        }

        btnCall.setOnClickListener {
            val intent = Intent(Intent.ACTION_DIAL, Uri.parse("tel:+1234567890"))
            startActivity(intent)
        }

        backIcon.setOnClickListener {
            finish()
        }
    }

    private fun setupBottomNavigation() {
        bottomNavHome.setOnClickListener {
            startActivity(Intent(this, DashboardActivity::class.java))
            finish()
        }
        bottomNavMealPlan.setOnClickListener {
            startActivity(Intent(this, MealPlanActivity::class.java))
            finish()
        }
        bottomNavChat.setOnClickListener { }
        bottomNavActivities.setOnClickListener {
            startActivity(Intent(this, ActivityPageActivity::class.java))
            finish()
        }
        bottomNavProfile.setOnClickListener {
            startActivity(Intent(this, ProfileActivity::class.java))
            finish()
        }
    }

    private fun sendMessage(messageText: String, imageUri: Uri?) {
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val multipartBody = MultipartBody.Builder().setType(MultipartBody.FORM)
                    .addFormDataPart("sender_id", parentId.toString())
                    .addFormDataPart("sender_role", "parent")
                    .addFormDataPart("message", messageText)
                    .addFormDataPart("receiver_all", "1")

                if (imageUri != null) {
                    val file = getFileFromUri(imageUri)
                    val fileBody = file.asRequestBody("image/jpeg".toMediaTypeOrNull())
                    multipartBody.addFormDataPart("image", file.name, fileBody)
                }

                val request = Request.Builder()
                    .url("http://10.0.2.2/daycare_system/api/mobile/send_message.php")
                    .post(multipartBody.build())
                    .build()

                client.newCall(request).execute()

                withContext(Dispatchers.Main) {
                    loadMessages()
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    Toast.makeText(this@ChatActivity, "Error: ${e.message}", Toast.LENGTH_SHORT).show()
                }
            }
        }
    }

    private fun sendImageMessage(uri: Uri) {
        sendMessage("", uri)
    }

    private fun getFileFromUri(uri: Uri): File {
        val file = File(cacheDir, "temp_image_${System.currentTimeMillis()}.jpg")
        val inputStream = contentResolver.openInputStream(uri)
        val outputStream = FileOutputStream(file)
        inputStream?.copyTo(outputStream)
        outputStream.close()
        inputStream?.close()
        return file
    }

    private fun loadMessages() {
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = RetrofitClient.instance.getMessages(parentId)

                withContext(Dispatchers.Main) {
                    if (response.isSuccessful && response.body()?.success == true) {
                        val chatMessages = response.body()?.messages ?: emptyList()
                        messageList.clear()

                        for (chatMsg in chatMessages) {
                            val isParent = chatMsg.sender_role == "parent"
                            val time = formatTime(chatMsg.timestamp)

                            messageList.add(
                                Message(
                                    sender = chatMsg.sender_name,
                                    content = chatMsg.message,
                                    isParent = isParent,
                                    time = time,
                                    imageUrl = chatMsg.image_url  // Now this works!
                                )
                            )
                        }

                        adapter.notifyDataSetChanged()
                        if (messageList.isNotEmpty()) {
                            recyclerChat.scrollToPosition(messageList.size - 1)
                        }
                    }
                }
            } catch (e: Exception) {
                // Silent fail
            }
        }
    }

    private fun startAutoRefresh() {
        android.os.Handler().postDelayed(object : Runnable {
            override fun run() {
                loadMessages()
                android.os.Handler().postDelayed(this, 3000)
            }
        }, 3000)
    }

    private fun formatTime(timestamp: String): String {
        return try {
            val sdf = SimpleDateFormat("yyyy-MM-dd HH:mm:ss", Locale.getDefault())
            val date = sdf.parse(timestamp)
            val timeFormat = SimpleDateFormat("hh:mm a", Locale.getDefault())
            timeFormat.format(date)
        } catch (e: Exception) {
            "Just now"
        }
    }
}