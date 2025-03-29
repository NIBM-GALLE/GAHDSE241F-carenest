import android.app.Activity
import android.content.Intent
import android.os.Bundle
import android.widget.Button
import android.widget.EditText
import android.widget.Toast
import androidx.activity.result.contract.ActivityResultContracts
import androidx.appcompat.app.AppCompatActivity
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import java.io.File

class ChatActivity : AppCompatActivity() {

    private lateinit var messageEditText: EditText
    private lateinit var sendButton: Button
    private lateinit var attachButton: Button
    private lateinit var chatRecyclerView: RecyclerView

    private val chatMessages = mutableListOf<Message>()
    private lateinit var chatAdapter: ChatAdapter

    // This result launcher will handle image attachment
    private val imagePickerLauncher =
        registerForActivityResult(ActivityResultContracts.GetContent()) { uri ->
            uri?.let {
                sendMessage("Image attached", it)
            }
        }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_chat)

        messageEditText = findViewById(R.id.messageEditText)
        sendButton = findViewById(R.id.sendButton)
        attachButton = findViewById(R.id.attachButton)
        chatRecyclerView = findViewById(R.id.chatRecyclerView)

        // Set up RecyclerView for chat
        chatAdapter = ChatAdapter(chatMessages)
        chatRecyclerView.layoutManager = LinearLayoutManager(this)
        chatRecyclerView.adapter = chatAdapter

        // Set up send button
        sendButton.setOnClickListener {
            val messageText = messageEditText.text.toString()
            if (messageText.isNotEmpty()) {
                sendMessage(messageText)
            }
        }

        // Set up attach button for images
        attachButton.setOnClickListener {
            imagePickerLauncher.launch("image/*")  // Allows selection of images
        }

        // Simulate receiving a message from the staff
        receiveMessage("Hello, how can we help you today?")
    }

    private fun sendMessage(messageText: String, imageUri: Uri? = null) {
        val message = Message(messageText, isParent = true, imageUri = imageUri)
        chatMessages.add(message)
        chatAdapter.notifyItemInserted(chatMessages.size - 1)
        messageEditText.text.clear()

        // Simulate sending the message to the staff (could be an API call here)
        // For now, just display a Toast
        Toast.makeText(this, "Message sent!", Toast.LENGTH_SHORT).show()
    }

    private fun receiveMessage(messageText: String) {
        val message = Message(messageText, isParent = false)
        chatMessages.add(message)
        chatAdapter.notifyItemInserted(chatMessages.size - 1)
    }
}
