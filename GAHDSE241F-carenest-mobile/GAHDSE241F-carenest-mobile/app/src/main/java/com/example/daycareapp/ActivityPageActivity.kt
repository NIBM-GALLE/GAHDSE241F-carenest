package com.example.daycareapp

import android.app.DatePickerDialog
import android.content.Intent
import android.os.Bundle
import android.view.View
import android.view.ViewGroup
import android.widget.*
import androidx.appcompat.app.AppCompatActivity
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import androidx.swiperefreshlayout.widget.SwipeRefreshLayout
import com.bumptech.glide.Glide
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import java.text.SimpleDateFormat
import java.util.*

class ActivityPageActivity : AppCompatActivity() {

    private lateinit var recyclerView: RecyclerView
    private lateinit var progressBar: ProgressBar
    private lateinit var spinnerChild: Spinner
    private lateinit var etDate: EditText
    private lateinit var btnFilter: ImageButton
    private lateinit var btnRefresh: ImageButton
    private lateinit var tvNoData: TextView
    private lateinit var swipeRefreshLayout: SwipeRefreshLayout

    // Bottom Navigation
    private lateinit var bottomNavHome: LinearLayout
    private lateinit var bottomNavMealPlan: LinearLayout
    private lateinit var bottomNavChat: LinearLayout
    private lateinit var bottomNavActivities: LinearLayout
    private lateinit var bottomNavProfile: LinearLayout

    private lateinit var activityAdapter: ActivityAdapter
    private var childList = mutableListOf<Child>()
    private var currentParentId = 0

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_activities)

        initViews()
        setupBottomNavigation()
        setupRecyclerView()
        setupDatePicker()
        setupSwipeRefresh()

        val sharedPref = getSharedPreferences("DaycarePref", MODE_PRIVATE)
        currentParentId = sharedPref.getInt("user_id", 0)

        loadChildren()

        btnFilter.setOnClickListener { loadActivities() }
        btnRefresh.setOnClickListener { loadActivities() }
    }

    private fun initViews() {
        recyclerView = findViewById(R.id.recyclerViewActivities)
        progressBar = findViewById(R.id.progressBar)
        spinnerChild = findViewById(R.id.spinnerChild)
        etDate = findViewById(R.id.etDate)
        btnFilter = findViewById(R.id.btnFilter)
        btnRefresh = findViewById(R.id.btnRefresh)
        tvNoData = findViewById(R.id.tvNoData)
        swipeRefreshLayout = findViewById(R.id.swipeRefreshLayout)

        // Bottom Navigation
        bottomNavHome = findViewById(R.id.bottomNavHome)
        bottomNavMealPlan = findViewById(R.id.bottomNavMealPlan)
        bottomNavChat = findViewById(R.id.bottomNavChat)
        bottomNavActivities = findViewById(R.id.bottomNavActivities)
        bottomNavProfile = findViewById(R.id.bottomNavProfile)
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
        bottomNavChat.setOnClickListener {
            startActivity(Intent(this, ChatActivity::class.java))
            finish()
        }
        bottomNavActivities.setOnClickListener {
            // Already on Activities page
        }
        bottomNavProfile.setOnClickListener {
            startActivity(Intent(this, ProfileActivity::class.java))
            finish()
        }
    }

    private fun setupRecyclerView() {
        activityAdapter = ActivityAdapter { activity ->
            showImageDialog(activity.image_url, activity.child_name)
        }
        recyclerView.layoutManager = LinearLayoutManager(this)
        recyclerView.adapter = activityAdapter
    }

    private fun setupSwipeRefresh() {
        swipeRefreshLayout.setOnRefreshListener {
            loadActivities()
        }
    }

    private fun setupDatePicker() {
        etDate.setOnClickListener {
            val calendar = Calendar.getInstance()
            val datePicker = DatePickerDialog(
                this,
                { _, year, month, dayOfMonth ->
                    val selectedCalendar = Calendar.getInstance()
                    selectedCalendar.set(year, month, dayOfMonth)
                    val dateFormat = SimpleDateFormat("yyyy-MM-dd", Locale.getDefault())
                    etDate.setText(dateFormat.format(selectedCalendar.time))
                },
                calendar.get(Calendar.YEAR),
                calendar.get(Calendar.MONTH),
                calendar.get(Calendar.DAY_OF_MONTH)
            )
            datePicker.show()
        }

        etDate.setOnLongClickListener {
            etDate.text.clear()
            loadActivities()
            true
        }
    }

    private fun loadChildren() {
        if (currentParentId == 0) {
            Toast.makeText(this, "Invalid Parent ID", Toast.LENGTH_SHORT).show()
            return
        }

        CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = RetrofitClient.instance.getChildren(currentParentId)
                withContext(Dispatchers.Main) {
                    if (response.isSuccessful && response.body()?.success == true) {
                        childList.clear()
                        childList.add(Child(0, "All Children", null, 0))
                        childList.addAll(response.body()?.children ?: emptyList())
                        setupSpinner()
                        loadActivities()
                    } else {
                        Toast.makeText(this@ActivityPageActivity, "Failed to load children", Toast.LENGTH_SHORT).show()
                    }
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    Toast.makeText(this@ActivityPageActivity, "Error: ${e.message}", Toast.LENGTH_SHORT).show()
                }
            }
        }
    }

    private fun setupSpinner() {
        val childNames = childList.map { it.name }
        val adapter = ArrayAdapter(this, android.R.layout.simple_spinner_item, childNames)
        adapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
        spinnerChild.adapter = adapter
    }

    private fun loadActivities() {
        if (currentParentId == 0) {
            tvNoData.visibility = View.VISIBLE
            tvNoData.text = "Please login again"
            return
        }

        progressBar.visibility = View.VISIBLE
        tvNoData.visibility = View.GONE

        val selectedChildId = if (spinnerChild.selectedItemPosition > 0) {
            childList[spinnerChild.selectedItemPosition].id
        } else 0

        val date = etDate.text.toString().trim()

        CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = RetrofitClient.instance.getActivities(currentParentId, selectedChildId, date)
                withContext(Dispatchers.Main) {
                    progressBar.visibility = View.GONE
                    swipeRefreshLayout.isRefreshing = false

                    if (response.isSuccessful && response.body()?.success == true) {
                        val activities = response.body()?.activities ?: emptyList()
                        if (activities.isEmpty()) {
                            tvNoData.visibility = View.VISIBLE
                            tvNoData.text = if (date.isNotEmpty()) "No activities for this date" else "No activities found"
                            activityAdapter.updateActivities(emptyList())
                        } else {
                            tvNoData.visibility = View.GONE
                            activityAdapter.updateActivities(activities)
                        }
                    } else {
                        tvNoData.visibility = View.VISIBLE
                        tvNoData.text = "Failed to load activities"
                        activityAdapter.updateActivities(emptyList())
                    }
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    progressBar.visibility = View.GONE
                    swipeRefreshLayout.isRefreshing = false
                    tvNoData.visibility = View.VISIBLE
                    tvNoData.text = "Error: ${e.message}"
                }
            }
        }
    }

    private fun showImageDialog(imageUrl: String, childName: String) {
        val dialog = android.app.AlertDialog.Builder(this)
        val imageView = ImageView(this)
        imageView.layoutParams = FrameLayout.LayoutParams(
            FrameLayout.LayoutParams.MATCH_PARENT,
            FrameLayout.LayoutParams.WRAP_CONTENT
        )
        imageView.adjustViewBounds = true

        Glide.with(this)
            .load(imageUrl)
            .placeholder(android.R.drawable.ic_menu_gallery)
            .error(android.R.drawable.ic_menu_report_image)
            .into(imageView)

        dialog.setTitle("Activity - $childName")
        dialog.setView(imageView)
        dialog.setPositiveButton("Close") { d, _ -> d.dismiss() }
        dialog.show()
    }

    override fun onSupportNavigateUp(): Boolean {
        finish()
        return true
    }

    inner class ActivityAdapter(
        private val onItemClick: (Activity) -> Unit
    ) : RecyclerView.Adapter<ActivityAdapter.ActivityViewHolder>() {

        private var activities = listOf<Activity>()

        fun updateActivities(newActivities: List<Activity>) {
            activities = newActivities
            notifyDataSetChanged()
        }

        override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ActivityViewHolder {
            val view = layoutInflater.inflate(R.layout.item_activity, parent, false)
            return ActivityViewHolder(view)
        }

        override fun onBindViewHolder(holder: ActivityViewHolder, position: Int) {
            holder.bind(activities[position])
            holder.itemView.setOnClickListener { onItemClick(activities[position]) }
        }

        override fun getItemCount(): Int = activities.size

        inner class ActivityViewHolder(itemView: View) : RecyclerView.ViewHolder(itemView) {
            private val ivActivity: ImageView = itemView.findViewById(R.id.ivActivity)
            private val tvChildName: TextView = itemView.findViewById(R.id.tvChildName)
            private val tvStaffName: TextView = itemView.findViewById(R.id.tvStaffName)
            private val tvDescription: TextView = itemView.findViewById(R.id.tvDescription)
            private val tvDate: TextView = itemView.findViewById(R.id.tvDate)
            private val tvShortDate: TextView = itemView.findViewById(R.id.tvShortDate)

            fun bind(activity: Activity) {
                tvChildName.text = activity.child_name
                tvStaffName.text = "by ${activity.staff_name}"
                tvDescription.text = activity.description
                tvDate.text = activity.formatted_date
                tvShortDate.text = activity.short_date

                Glide.with(itemView.context)
                    .load(activity.image_url)
                    .placeholder(android.R.drawable.ic_menu_gallery)
                    .error(android.R.drawable.ic_menu_report_image)
                    .centerCrop()
                    .into(ivActivity)
            }
        }
    }
}