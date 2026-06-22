<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/db.php';

$parent_id = isset($_GET['parent_id']) ? intval($_GET['parent_id']) : 0;

if ($parent_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Parent ID is required']);
    exit();
}

$child_id = isset($_GET['child_id']) ? intval($_GET['child_id']) : 0;
$date = isset($_GET['date']) ? trim($_GET['date']) : '';

$sql = "SELECT 
            a.id,
            a.description,
            a.date,
            a.image_url,
            c.id as child_id,
            c.name as child_name,
            u.name as staff_name,
            DATE_FORMAT(a.date, '%W, %M %d, %Y') as formatted_date,
            DATE_FORMAT(a.date, '%b %d, %Y') as short_date
        FROM activities a
        INNER JOIN children c ON a.child_id = c.id
        INNER JOIN users u ON a.staff_id = u.id
        WHERE c.parent_id = $parent_id";

if ($child_id > 0) {
    $sql .= " AND a.child_id = $child_id";
}

// Only add date filter if date is not empty
if (!empty($date)) {
    $sql .= " AND a.date = '$date'";
}

$sql .= " ORDER BY a.date DESC, a.id DESC";

$result = $conn->query($sql);
$activities = [];

while ($row = $result->fetch_assoc()) {
    $image_url = $row['image_url'];
    if (!empty($image_url) && strpos($image_url, 'http') !== 0) {
        $image_url = "http://10.0.2.2/daycare_system/" . $image_url;
    }
    
    $activities[] = [
        'id' => $row['id'],
        'child_id' => $row['child_id'],
        'child_name' => $row['child_name'],
        'staff_name' => $row['staff_name'],
        'description' => $row['description'],
        'date' => $row['date'],
        'formatted_date' => $row['formatted_date'],
        'short_date' => $row['short_date'],
        'image_url' => $image_url
    ];
}

echo json_encode([
    'success' => true,
    'activities' => $activities,
    'total' => count($activities)
]);

$conn->close();
?>