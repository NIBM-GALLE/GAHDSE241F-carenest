package com.example.daycareapp

import android.content.Intent
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.*
import androidx.appcompat.app.AppCompatActivity
import androidx.cardview.widget.CardView
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import androidx.swiperefreshlayout.widget.SwipeRefreshLayout
import com.google.android.material.chip.ChipGroup
import kotlinx.coroutines.*
import org.json.JSONObject
import java.net.URL
import java.text.SimpleDateFormat
import java.util.*

class MealPlanActivity : AppCompatActivity() {

    private lateinit var recyclerView: RecyclerView
    private lateinit var progressBar: ProgressBar
    private lateinit var tvNoData: TextView
    private lateinit var swipeRefreshLayout: SwipeRefreshLayout
    private lateinit var chipGroup: ChipGroup
    private lateinit var tvSelectedChild: TextView
    private lateinit var btnBack: ImageView
    private lateinit var tvMonthYear: TextView
    private lateinit var btnPrevWeek: ImageButton
    private lateinit var btnNextWeek: ImageButton

    // Bottom Navigation
    private lateinit var bottomNavHome: LinearLayout
    private lateinit var bottomNavMealPlan: LinearLayout
    private lateinit var bottomNavChat: LinearLayout
    private lateinit var bottomNavActivities: LinearLayout
    private lateinit var bottomNavProfile: LinearLayout

    private lateinit var adapter: MealAdapter
    private var currentChildType = "toddler"
    private var currentWeekOffset = 0

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_meal_plan)

        initViews()
        setupBottomNavigation()
        setupListeners()

        updateWeekDisplay()
        loadMealPlan()
    }

    private fun initViews() {
        recyclerView = findViewById(R.id.recyclerViewMeal)
        progressBar = findViewById(R.id.progressBar)
        tvNoData = findViewById(R.id.tvNoData)
        swipeRefreshLayout = findViewById(R.id.swipeRefreshLayout)
        chipGroup = findViewById(R.id.chipGroup)
        tvSelectedChild = findViewById(R.id.tvSelectedChild)
        btnBack = findViewById(R.id.btnBack)
        tvMonthYear = findViewById(R.id.tvMonthYear)
        btnPrevWeek = findViewById(R.id.btnPrevWeek)
        btnNextWeek = findViewById(R.id.btnNextWeek)

        // Bottom Navigation
        bottomNavHome = findViewById(R.id.bottomNavHome)
        bottomNavMealPlan = findViewById(R.id.bottomNavMealPlan)
        bottomNavChat = findViewById(R.id.bottomNavChat)
        bottomNavActivities = findViewById(R.id.bottomNavActivities)
        bottomNavProfile = findViewById(R.id.bottomNavProfile)

        adapter = MealAdapter()
        recyclerView.layoutManager = LinearLayoutManager(this)
        recyclerView.adapter = adapter
    }

    private fun setupBottomNavigation() {
        bottomNavHome.setOnClickListener {
            startActivity(Intent(this, DashboardActivity::class.java))
            finish()
        }

        bottomNavMealPlan.setOnClickListener {
            // Already on Meal Plan
        }

        bottomNavChat.setOnClickListener {
            startActivity(Intent(this, ChatActivity::class.java))
            finish()
        }

        bottomNavActivities.setOnClickListener {
            startActivity(Intent(this, ActivityPageActivity::class.java))
            finish()
        }

        bottomNavProfile.setOnClickListener {
            startActivity(Intent(this, ProfileActivity::class.java))
            finish()
        }
    }

    private fun setupListeners() {
        chipGroup.setOnCheckedChangeListener { _, checkedId ->
            when (checkedId) {
                R.id.chipToddler -> {
                    currentChildType = "toddler"
                    tvSelectedChild.text = "👧 Toddler (2-4 years)"
                    loadMealPlan()
                }
                R.id.chipInfant -> {
                    currentChildType = "infant"
                    tvSelectedChild.text = "🍼 Infant (0-2 years)"
                    loadMealPlan()
                }
            }
        }

        btnPrevWeek.setOnClickListener {
            currentWeekOffset--
            updateWeekDisplay()
            loadMealPlan()
        }

        btnNextWeek.setOnClickListener {
            currentWeekOffset++
            updateWeekDisplay()
            loadMealPlan()
        }

        btnBack.setOnClickListener {
            finish()
        }

        swipeRefreshLayout.setOnRefreshListener {
            loadMealPlan()
        }
    }

    private fun updateWeekDisplay() {
        val calendar = Calendar.getInstance().apply {
            add(Calendar.WEEK_OF_YEAR, currentWeekOffset)
            set(Calendar.DAY_OF_WEEK, Calendar.MONDAY)
        }

        val startDate = SimpleDateFormat("MMM dd", Locale.US).format(calendar.time)
        calendar.add(Calendar.DAY_OF_WEEK, 6)
        val endDate = SimpleDateFormat("MMM dd, yyyy", Locale.US).format(calendar.time)

        tvMonthYear.text = "$startDate - $endDate"
    }

    private fun getWeekRange(): Pair<String, String> {
        val calendar = Calendar.getInstance().apply {
            add(Calendar.WEEK_OF_YEAR, currentWeekOffset)
            set(Calendar.DAY_OF_WEEK, Calendar.MONDAY)
        }

        val startDate = SimpleDateFormat("yyyy-MM-dd", Locale.US).format(calendar.time)
        calendar.add(Calendar.DAY_OF_WEEK, 6)
        val endDate = SimpleDateFormat("yyyy-MM-dd", Locale.US).format(calendar.time)

        return Pair(startDate, endDate)
    }

    private fun loadMealPlan() {
        progressBar.visibility = View.VISIBLE
        tvNoData.visibility = View.GONE
        recyclerView.visibility = View.GONE

        val (startDate, endDate) = getWeekRange()

        CoroutineScope(Dispatchers.IO).launch {
            try {
                val url = "http://10.0.2.2/daycare_system/api/mobile/get_meal_plan.php?start_date=$startDate&end_date=$endDate"

                val response = URL(url).readText()
                val json = JSONObject(response)

                withContext(Dispatchers.Main) {
                    progressBar.visibility = View.GONE
                    swipeRefreshLayout.isRefreshing = false

                    if (json.getBoolean("success")) {
                        val array = json.getJSONArray("meal_plan")
                        val meals = mutableListOf<MealItem>()

                        for (i in 0 until array.length()) {
                            val obj = array.getJSONObject(i)

                            val breakfast = if (currentChildType == "toddler")
                                obj.optString("breakfast_toddler", "Not specified")
                            else
                                obj.optString("breakfast_infant", "Not specified")

                            val lunch = if (currentChildType == "toddler")
                                obj.optString("lunch_toddler", "Not specified")
                            else
                                obj.optString("lunch_infant", "Not specified")

                            val snacks = if (currentChildType == "toddler")
                                obj.optString("snacks_toddler", "Not specified")
                            else
                                obj.optString("snacks_infant", "Not specified")

                            val dateStr = obj.getString("date")
                            val dayName = obj.optString("day_name", getDayOfWeek(dateStr))
                            val shortDate = obj.optString("short_date", formatDate(dateStr))
                            val notes = obj.optString("notes", "")

                            meals.add(MealItem(
                                date = dateStr,
                                dayName = dayName,
                                shortDate = shortDate,
                                breakfast = breakfast.ifEmpty { "Not specified" },
                                lunch = lunch.ifEmpty { "Not specified" },
                                snacks = snacks.ifEmpty { "Not specified" },
                                notes = notes
                            ))
                        }

                        if (meals.isEmpty()) {
                            tvNoData.visibility = View.VISIBLE
                            recyclerView.visibility = View.GONE
                        } else {
                            tvNoData.visibility = View.GONE
                            recyclerView.visibility = View.VISIBLE
                            adapter.updateList(meals)
                        }
                    } else {
                        tvNoData.visibility = View.VISIBLE
                        recyclerView.visibility = View.GONE
                    }
                }

            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    progressBar.visibility = View.GONE
                    swipeRefreshLayout.isRefreshing = false
                    tvNoData.visibility = View.VISIBLE
                    recyclerView.visibility = View.GONE
                    Toast.makeText(this@MealPlanActivity, "Error: ${e.message}", Toast.LENGTH_LONG).show()
                }
            }
        }
    }

    private fun getDayOfWeek(dateStr: String): String {
        return try {
            val format = SimpleDateFormat("yyyy-MM-dd", Locale.US)
            val date = format.parse(dateStr)
            val dayFormat = SimpleDateFormat("EEEE", Locale.US)
            dayFormat.format(date ?: Date())
        } catch (e: Exception) {
            ""
        }
    }

    private fun formatDate(dateStr: String): String {
        return try {
            val format = SimpleDateFormat("yyyy-MM-dd", Locale.US)
            val date = format.parse(dateStr)
            val newFormat = SimpleDateFormat("MMM dd", Locale.US)
            newFormat.format(date ?: Date())
        } catch (e: Exception) {
            dateStr
        }
    }

    data class MealItem(
        val date: String,
        val dayName: String,
        val shortDate: String,
        val breakfast: String,
        val lunch: String,
        val snacks: String,
        val notes: String
    )

    inner class MealAdapter : RecyclerView.Adapter<MealAdapter.ViewHolder>() {

        private var list = listOf<MealItem>()

        fun updateList(newList: List<MealItem>) {
            list = newList
            notifyDataSetChanged()
        }

        override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ViewHolder {
            val view = LayoutInflater.from(parent.context)
                .inflate(R.layout.item_meal_plan, parent, false)
            return ViewHolder(view)
        }

        override fun onBindViewHolder(holder: ViewHolder, position: Int) {
            holder.bind(list[position])
        }

        override fun getItemCount() = list.size

        inner class ViewHolder(itemView: View) : RecyclerView.ViewHolder(itemView) {
            private val tvDayName: TextView = itemView.findViewById(R.id.tvDayName)
            private val tvDate: TextView = itemView.findViewById(R.id.tvDate)
            private val tvBreakfastValue: TextView = itemView.findViewById(R.id.tvBreakfastValue)
            private val tvLunchValue: TextView = itemView.findViewById(R.id.tvLunchValue)
            private val tvSnacksValue: TextView = itemView.findViewById(R.id.tvSnacksValue)
            private val tvNotes: TextView = itemView.findViewById(R.id.tvNotes)

            fun bind(meal: MealItem) {
                tvDayName.text = meal.dayName
                tvDate.text = meal.shortDate

                tvBreakfastValue.text = meal.breakfast
                tvLunchValue.text = meal.lunch
                tvSnacksValue.text = meal.snacks

                if (meal.notes.isNotEmpty()) {
                    tvNotes.text = "📝 ${meal.notes}"
                    tvNotes.visibility = View.VISIBLE
                } else {
                    tvNotes.visibility = View.GONE
                }
            }
        }
    }
}