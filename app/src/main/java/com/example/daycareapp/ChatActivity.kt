package com.example.daycareapp

import android.os.Bundle
import android.widget.EditText
import android.widget.ImageButton
import android.widget.ImageView
import androidx.appcompat.app.AppCompatActivity
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import java.text.SimpleDateFormat
import java.util.*

class ChatActivity : AppCompatActivity() {

    private lateinit var recyclerChat: RecyclerView
    private lateinit var editMessage: EditText
    private lateinit var btnSend: ImageButton
    private lateinit var backIcon: ImageView

    private val messageList = mutableListOf<Message>()
    private lateinit var adapter: ChatAdapter

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_chat)

        recyclerChat = findViewById(R.id.recyclerChat)
        editMessage = findViewById(R.id.editMessage)
        btnSend = findViewById(R.id.btnSend)
        backIcon = findViewById(R.id.backIcon)

        adapter = ChatAdapter(messageList)
        recyclerChat.layoutManager = LinearLayoutManager(this)
        recyclerChat.adapter = adapter

        btnSend.setOnClickListener {
            val messageText = editMessage.text.toString().trim()
            if (messageText.isNotEmpty()) {
                val currentTime = SimpleDateFormat("hh:mm a", Locale.getDefault()).format(Date())
                val message = Message(
                    sender = "You",
                    content = messageText,
                    isParent = true,          // ✅ Set true or false depending on who sends the message
                    time = currentTime        // ✅ Add formatted current time
                )
                messageList.add(message)
                adapter.notifyItemInserted(messageList.size - 1)
                recyclerChat.scrollToPosition(messageList.size - 1)
                editMessage.text.clear()
            }
        }

        backIcon.setOnClickListener {
            finish()
        }
    }
}

