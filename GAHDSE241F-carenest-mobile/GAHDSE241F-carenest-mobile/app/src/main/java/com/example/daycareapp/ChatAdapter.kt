package com.example.daycareapp

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ImageView
import android.widget.TextView
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide

class ChatAdapter(private val messages: List<Message>) :
    RecyclerView.Adapter<ChatAdapter.ChatViewHolder>() {

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ChatViewHolder {
        val layout = if (viewType == 1) R.layout.item_message_parent else R.layout.item_message_staff
        val view = LayoutInflater.from(parent.context).inflate(layout, parent, false)
        return ChatViewHolder(view)
    }

    override fun getItemViewType(position: Int): Int {
        return if (messages[position].isParent) 1 else 0
    }

    override fun onBindViewHolder(holder: ChatViewHolder, position: Int) {
        holder.bind(messages[position])
    }

    override fun getItemCount() = messages.size

    inner class ChatViewHolder(itemView: View) : RecyclerView.ViewHolder(itemView) {
        private val messageText: TextView = itemView.findViewById(R.id.textMessage)
        private val messageImage: ImageView = itemView.findViewById(R.id.imageMessage)
        private val messageTime: TextView = itemView.findViewById(R.id.textTime)

        fun bind(message: Message) {
            // Show image if exists
            if (!message.imageUrl.isNullOrEmpty()) {
                messageImage.visibility = View.VISIBLE
                messageText.visibility = View.GONE
                val fullUrl = "http://10.0.2.2/daycare_system/${message.imageUrl}"
                Glide.with(itemView.context).load(fullUrl).into(messageImage)
            } else {
                messageImage.visibility = View.GONE
                messageText.visibility = View.VISIBLE
                messageText.text = message.content
            }
            messageTime.text = message.time
        }
    }
}