// Check authentication on page load
document.addEventListener('DOMContentLoaded', function() {
    const currentPage = window.location.pathname;
    
    if (currentPage.includes('admin')) {
        loadAdminData();
    } else if (currentPage.includes('staff')) {
        loadStaffData();
    }
    
    // Setup login form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }
});

// Handle login
async function handleLogin(e) {
    e.preventDefault();
    
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    
    try {
        const response = await fetch('../api/login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password })
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            showError(data.error);
        }
    } catch (error) {
        showError('Connection error. Please try again.');
    }
}

// Load admin dashboard data
async function loadAdminData() {
    await loadDashboardStats();
    if (document.getElementById('childrenTableBody')) {
        await loadChildren();
        await loadParentSelect();
        await loadStaffSelect();
    }
    if (document.getElementById('staffTableBody')) {
        await loadStaff();
    }
    if (document.getElementById('parentsTableBody')) {
        await loadParents();
    }
    if (document.getElementById('paymentsTableBody')) {
        await loadPayments();
    }
    if (document.getElementById('mealPlanDisplay')) {
        await loadMealPlan();
    }
    if (document.getElementById('messageHistory')) {
        await loadMessages();
    }
}

// Load dashboard stats
async function loadDashboardStats() {
    try {
        const response = await fetch('../api/admin/get_stats.php');
        const stats = await response.json();
        
        document.getElementById('totalChildren').textContent = stats.total_children || 0;
        document.getElementById('totalStaff').textContent = stats.total_staff || 0;
        document.getElementById('totalParents').textContent = stats.total_parents || 0;
        document.getElementById('totalRevenue').textContent = `$${stats.total_revenue || 0}`;
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

// Load children list
async function loadChildren() {
    try {
        const response = await fetch('../api/admin/get_children.php');
        const children = await response.json();
        
        const tbody = document.getElementById('childrenTableBody');
        if (!tbody) return;
        
        if (children.error) {
            tbody.innerHTML = `<tr><td colspan="7">${children.error}</td></tr>`;
            return;
        }
        
        tbody.innerHTML = children.map(child => `
            <tr>
                <td>${child.id}</td>
                <td>${child.name}</td>
                <td>${child.date_of_birth}</td>
                <td>${child.parent_name || 'Not assigned'}</td>
                <td>${child.staff_name || 'Not assigned'}</td>
                <td>${child.allergies || 'None'}</td>
                <td>
                    <button onclick="deleteChild(${child.id})" class="btn-sm btn-danger">Delete</button>
                </td>
            </tr>
        `).join('');
    } catch (error) {
        console.error('Error loading children:', error);
    }
}

// Add child
document.getElementById('addChildForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = {};
    formData.forEach((value, key) => { data[key] = value; });
    
    try {
        const response = await fetch('../api/admin/add_child.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage('Child added successfully!', 'success');
            closeModal('addModal');
            loadChildren();
        } else {
            showMessage(result.error, 'error');
        }
    } catch (error) {
        showMessage('Error adding child', 'error');
    }
});

// Delete child
async function deleteChild(id) {
    if (!confirm('Are you sure you want to delete this child?')) return;
    
    try {
        const response = await fetch(`../api/admin/delete_child.php?id=${id}`);
        const result = await response.json();
        
        if (result.success) {
            showMessage('Child deleted successfully!', 'success');
            loadChildren();
        } else {
            showMessage(result.error, 'error');
        }
    } catch (error) {
        showMessage('Error deleting child', 'error');
    }
}

// Load parent select dropdown
async function loadParentSelect() {
    try {
        const response = await fetch('../api/admin/get_parents.php');
        const parents = await response.json();
        
        const select = document.getElementById('parentSelect');
        if (select) {
            select.innerHTML = '<option value="">Select Parent</option>' + 
                parents.map(p => `<option value="${p.id}">${p.name}</option>`).join('');
        }
    } catch (error) {
        console.error('Error loading parents:', error);
    }
}

// Load staff select dropdown
async function loadStaffSelect() {
    try {
        const response = await fetch('../api/admin/get_staff.php');
        const staff = await response.json();
        
        const select = document.getElementById('staffSelect');
        if (select) {
            select.innerHTML = '<option value="">Select Staff</option>' + 
                staff.map(s => `<option value="${s.id}">${s.name}</option>`).join('');
        }
    } catch (error) {
        console.error('Error loading staff:', error);
    }
}

// Load staff list
async function loadStaff() {
    try {
        const response = await fetch('../api/admin/get_staff.php');
        const staff = await response.json();
        
        const tbody = document.getElementById('staffTableBody');
        if (!tbody) return;
        
        tbody.innerHTML = staff.map(member => `
            <tr>
                <td>${member.id}</td>
                <td>${member.name}</td>
                <td>${member.email}</td>
                <td>${member.phone}</td>
                <td>${member.username}</td>
                <td>
                    <button onclick="deleteStaff(${member.id})" class="btn-sm btn-danger">Delete</button>
                </td>
            </tr>
        `).join('');
    } catch (error) {
        console.error('Error loading staff:', error);
    }
}

// Add staff
document.getElementById('addStaffForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = {};
    formData.forEach((value, key) => { data[key] = value; });
    
    try {
        const response = await fetch('../api/admin/add_staff.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage('Staff added successfully!', 'success');
            closeModal('addStaffModal');
            loadStaff();
        } else {
            showMessage(result.error, 'error');
        }
    } catch (error) {
        showMessage('Error adding staff', 'error');
    }
});

// Load parents list
async function loadParents() {
    try {
        const response = await fetch('../api/admin/get_parents.php');
        const parents = await response.json();
        
        const tbody = document.getElementById('parentsTableBody');
        if (!tbody) return;
        
        tbody.innerHTML = parents.map(parent => `
            <tr>
                <td>${parent.id}</td>
                <td>${parent.name}</td>
                <td>${parent.email}</td>
                <td>${parent.phone}</td>
                <td>${parent.username}</td>
                <td>
                    <button onclick="deleteParent(${parent.id})" class="btn-sm btn-danger">Delete</button>
                </td>
            </tr>
        `).join('');
    } catch (error) {
        console.error('Error loading parents:', error);
    }
}

// Load payments list
async function loadPayments() {
    try {
        const response = await fetch('../api/admin/get_payments.php');
        const payments = await response.json();
        
        const tbody = document.getElementById('paymentsTableBody');
        if (!tbody) return;
        
        tbody.innerHTML = payments.map(payment => `
            <tr>
                <td>${payment.id}</td>
                <td>${payment.parent_name}</td>
                <td>$${payment.amount}</td>
                <td><span class="status-badge ${payment.status.toLowerCase()}">${payment.status}</span></td>
                <td>${payment.month}</td>
                <td>${payment.due_date}</td>
                <td>
                    <button onclick="updatePaymentStatus(${payment.id})" class="btn-sm">Update</button>
                </td>
            </tr>
        `).join('');
    } catch (error) {
        console.error('Error loading payments:', error);
    }
}

// Load meal plan
async function loadMealPlan() {
    try {
        const response = await fetch('../api/get_meal_plan.php');
        const mealPlan = await response.json();
        
        const container = document.getElementById('mealPlanDisplay');
        if (!container) return;
        
        if (mealPlan.length === 0) {
            container.innerHTML = '<p>No meal plans available.</p>';
            return;
        }
        
        container.innerHTML = `
            <div class="meal-plan-grid">
                ${mealPlan.map(meal => `
                    <div class="meal-card">
                        <h3>${meal.date}</h3>
                        <p><strong>Breakfast:</strong> ${meal.breakfast}</p>
                        <p><strong>Lunch:</strong> ${meal.lunch}</p>
                        <p><strong>Snacks:</strong> ${meal.snacks}</p>
                        ${meal.notes ? `<p><strong>Notes:</strong> ${meal.notes}</p>` : ''}
                    </div>
                `).join('')}
            </div>
        `;
    } catch (error) {
        console.error('Error loading meal plan:', error);
    }
}

// Update meal plan
document.getElementById('updateMealForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = {};
    formData.forEach((value, key) => { data[key] = value; });
    
    try {
        const response = await fetch('../api/admin/update_meal_plan.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage('Meal plan updated successfully!', 'success');
            closeModal('updateMealModal');
            loadMealPlan();
        } else {
            showMessage(result.error, 'error');
        }
    } catch (error) {
        showMessage('Error updating meal plan', 'error');
    }
});

// Load staff assigned children
async function loadAssignedChildren() {
    try {
        const response = await fetch('../api/staff/get_assigned_children.php');
        const children = await response.json();
        
        const container = document.getElementById('assignedChildrenCount');
        if (container) {
            container.textContent = children.length;
        }
        
        const tbody = document.getElementById('attendanceTableBody');
        if (tbody) {
            tbody.innerHTML = children.map(child => `
                <tr>
                    <td>${child.name}</td>
                    <td>
                        <select id="status_${child.id}">
                            <option value="Present" ${child.status === 'Present' ? 'selected' : ''}>Present</option>
                            <option value="Absent" ${child.status === 'Absent' ? 'selected' : ''}>Absent</option>
                        </select>
                    </td>
                    <td><input type="time" id="entry_${child.id}" value="${child.entry_time || '08:00'}"></td>
                    <td><input type="time" id="leaving_${child.id}" value="${child.leaving_time || '16:00'}"></td>
                    <td><button onclick="markAttendance(${child.id})" class="btn-sm">Mark</button></td>
                </tr>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading assigned children:', error);
    }
}

// Mark attendance
async function markAttendance(childId) {
    const status = document.getElementById(`status_${childId}`).value;
    const entryTime = document.getElementById(`entry_${childId}`).value;
    const leavingTime = document.getElementById(`leaving_${childId}`).value;
    const date = document.getElementById('attendanceDate')?.value || new Date().toISOString().split('T')[0];
    
    try {
        const response = await fetch('../api/staff/mark_attendance.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ child_id: childId, date, status, entry_time: entryTime, leaving_time: leavingTime })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage('Attendance marked successfully!', 'success');
        } else {
            showMessage(result.error, 'error');
        }
    } catch (error) {
        showMessage('Error marking attendance', 'error');
    }
}

// Add activity
document.getElementById('addActivityForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    
    try {
        const response = await fetch('../api/staff/add_activity.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage('Activity added successfully!', 'success');
            closeModal('addActivityModal');
            loadActivities();
        } else {
            showMessage(result.error, 'error');
        }
    } catch (error) {
        showMessage('Error adding activity', 'error');
    }
});

// Load activities
async function loadActivities() {
    try {
        const response = await fetch('../api/staff/get_activities.php');
        const activities = await response.json();
        
        const container = document.getElementById('activitiesList');
        if (!container) return;
        
        if (activities.length === 0) {
            container.innerHTML = '<p>No activities recorded yet.</p>';
            return;
        }
        
        container.innerHTML = `
            <div class="activities-grid">
                ${activities.map(activity => `
                    <div class="activity-card">
                        <div class="activity-header">
                            <strong>${activity.child_name}</strong>
                            <small>${activity.date}</small>
                        </div>
                        <p>${activity.description}</p>
                        ${activity.image_url ? `<img src="../../${activity.image_url}" alt="Activity" class="activity-image">` : ''}
                    </div>
                `).join('')}
            </div>
        `;
    } catch (error) {
        console.error('Error loading activities:', error);
    }
}

// Utility functions
function showAddModal() {
    document.getElementById('addModal').style.display = 'block';
}

function showAddStaffModal() {
    document.getElementById('addStaffModal').style.display = 'block';
}

function showAddParentModal() {
    document.getElementById('addParentModal').style.display = 'block';
}

function showAddPaymentModal() {
    document.getElementById('addPaymentModal').style.display = 'block';
}

function showUpdateMealModal() {
    document.getElementById('updateMealModal').style.display = 'block';
}

function showAddActivityModal() {
    document.getElementById('addActivityModal').style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function showMessage(message, type) {
    const msgBox = document.getElementById('messageBox');
    if (msgBox) {
        msgBox.textContent = message;
        msgBox.className = `alert alert-${type}`;
        msgBox.style.display = 'block';
        setTimeout(() => {
            msgBox.style.display = 'none';
        }, 3000);
    } else {
        alert(message);
    }
}

function showError(message) {
    const errorDiv = document.getElementById('errorMsg');
    if (errorDiv) {
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
        setTimeout(() => {
            errorDiv.style.display = 'none';
        }, 3000);
    } else {
        alert(message);
    }
}

// Close modals when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}