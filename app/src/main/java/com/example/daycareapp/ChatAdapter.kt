package com.example.daycareapp

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ImageView
import android.widget.TextView
import androidx.recyclerview.widget.RecyclerView

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

    inner class ChatViewHolder(view: View) : RecyclerView.ViewHolder(view) {
        private val messageText: TextView = view.findViewById(R.id.textMessage)
        private val messageImage: ImageView = view.findViewById(R.id.imageMessage)
        private val messageTime: TextView = view.findViewById(R.id.textTime)

        fun bind(message: Message) {
            if (message.content.isNotEmpty()) {
                messageText.visibility = View.VISIBLE
                messageText.text = message.content
            } else {
                messageText.visibility = View.GONE
            }

            if (message.imageResId != null) {
                messageImage.visibility = View.VISIBLE
                messageImage.setImageResource(message.imageResId)
            } else {
                messageImage.visibility = View.GONE
            }

            messageTime.text = message.time
        }
    }
}




