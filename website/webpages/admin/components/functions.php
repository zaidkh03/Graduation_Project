<?php
// functions.php - shared utility functions

function rebuildSubjectTeacherMap($conn, $class_id) {
    $map = [];
    $query = $conn->prepare("SELECT subject_id, teacher_id FROM teacher_subject_class WHERE class_id = ?");
    $query->bind_param("i", $class_id);
    $query->execute();
    $result = $query->get_result();

    while ($row = $result->fetch_assoc()) {
        $map[$row['subject_id']] = (int)$row['teacher_id'];
    }

    $json = json_encode($map);
    $update = $conn->prepare("UPDATE class SET subject_teacher_map = ? WHERE id = ?");
    $update->bind_param("si", $json, $class_id);
    $update->execute();
}
?>