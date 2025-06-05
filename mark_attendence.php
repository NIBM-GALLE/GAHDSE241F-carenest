<form action="submit_attendance.php" method="post">
  <table>
    <thead>
      <tr>
        <th>Child Name</th>
        <th>Status</th>
        <th>Entry Time</th>
        <th>Leaving Time</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($children as $child): ?>
        <tr>
          <td><?= htmlspecialchars($child['name']) ?></td>
          <td>
            <select name="status[<?= $child['id'] ?>]" onchange="toggleTimeFields(this, <?= $child['id'] ?>)">
              <option value="Present" selected>Present</option>
              <option value="Absent">Absent</option>
            </select>
          </td>
          <td>
            <input type="time" name="entry_time[<?= $child['id'] ?>]" id="entry_<?= $child['id'] ?>" />
          </td>
          <td>
            <input type="time" name="leaving_time[<?= $child['id'] ?>]" id="leave_<?= $child['id'] ?>" />
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <button type="submit">Submit Attendance</button>
</form>

<script>
  function toggleTimeFields(select, id) {
    const isPresent = select.value === "Present";
    document.getElementById('entry_' + id).disabled = !isPresent;
    document.getElementById('leave_' + id).disabled = !isPresent;
  }
</script>
