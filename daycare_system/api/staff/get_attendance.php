<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

require_once '../../config/db.php';

$staff_id = $_SESSION['user_id'];
$is_report = isset($_GET['report']) && $_GET['report'] == 1;

if ($is_report) {
    // Generate Report
    $child_id = $_GET['child_id'] ?? 'all';
    $month = $_GET['month'] ?? date('Y-m');
    $year = $_GET['year'] ?? date('Y');
    $days_in_month = cal_days_in_month(CAL_GREGORIAN, substr($month, 5, 2), substr($month, 0, 4));
    
    if ($child_id !== 'all') {
        // Verify child belongs to this staff
        $check = $conn->query("SELECT id FROM children WHERE id = $child_id AND assigned_staff_id = $staff_id");
        if ($check->num_rows == 0) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit();
        }
        
        $sql = "SELECT a.*, c.name as child_name FROM attendance a JOIN children c ON a.child_id = c.id WHERE a.child_id = $child_id AND DATE_FORMAT(a.date, '%Y-%m') = '$month' ORDER BY a.date ASC";
        $result = $conn->query($sql);
        $attendance = [];
        $present_days = 0;
        
        while ($row = $result->fetch_assoc()) {
            $attendance[] = $row;
            if ($row['status'] == 'Present') $present_days++;
        }
        
        $absent_days = $days_in_month - $present_days;
        $percentage = $days_in_month > 0 ? round(($present_days / $days_in_month) * 100, 2) : 0;
        
        echo json_encode([
            'success' => true,
            'child_name' => $attendance[0]['child_name'] ?? 'Child',
            'month' => $month,
            'year' => $year,
            'present_days' => $present_days,
            'absent_days' => $absent_days,
            'total_days' => $days_in_month,
            'percentage' => $percentage,
            'details' => $attendance
        ]);
    } else {
        // All children under this staff
        $sql = "SELECT c.id, c.name, 
                SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) as present_days, 
                SUM(CASE WHEN a.status = 'Absent' THEN 1 ELSE 0 END) as absent_days 
                FROM children c 
                LEFT JOIN attendance a ON c.id = a.child_id AND DATE_FORMAT(a.date, '%Y-%m') = '$month' 
                WHERE c.assigned_staff_id = $staff_id 
                GROUP BY c.id ORDER BY c.name";
        
        $result = $conn->query($sql);
        $report = [];
        
        while ($row = $result->fetch_assoc()) {
            $percentage = $days_in_month > 0 ? round(($row['present_days'] / $days_in_month) * 100, 2) : 0;
            $report[] = [
                'name' => $row['name'],
                'present_days' => (int)$row['present_days'],
                'absent_days' => (int)$row['absent_days'],
                'total_days' => $days_in_month,
                'percentage' => $percentage
            ];
        }
        
        echo json_encode(['success' => true, 'report' => $report, 'month' => $month, 'year' => $year]);
    }
} else {
    // Get children for attendance marking
    $date = $_GET['date'] ?? date('Y-m-d');
    
    $sql = "SELECT c.*, u.name as parent_name, a.status, a.entry_time, a.leaving_time 
            FROM children c 
            LEFT JOIN users u ON c.parent_id = u.id 
            LEFT JOIN attendance a ON c.id = a.child_id AND a.date = '$date' 
            WHERE c.assigned_staff_id = $staff_id 
            ORDER BY c.name";
    
    $result = $conn->query($sql);
    $children = [];
    
    while ($row = $result->fetch_assoc()) {
        $children[] = $row;
    }
    
    echo json_encode(['success' => true, 'children' => $children]);
}

$conn->close();
?>