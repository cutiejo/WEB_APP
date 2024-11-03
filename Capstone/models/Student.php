<?php

class Student {
    private $conn;

    public function __construct() {
        // Include the database connection
        include_once '../db_model.php';
        $this->conn = $conn;
    }

    public function getStudentByUserId($user_id) {
        $query = "SELECT students.id, students.user_id, students.lrn, students.full_name, students.email,
                         students.birth_date, students.rfid_uid, students.address, students.sex, students.guardian,
                         students.contact, students.image, grade_levels.grade_level, sections.section
                  FROM students
                  LEFT JOIN grade_levels ON students.grade_level_id = grade_levels.id
                  LEFT JOIN sections ON students.section_id = sections.id
                  WHERE students.user_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $student = $result->fetch_assoc();

        return $student ? $student : null;
    }
}
