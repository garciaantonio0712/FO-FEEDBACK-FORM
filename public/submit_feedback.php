<?php
// submit_feedback.php - Path: C:\xampp\htdocs\frontoffice_feedback\public\submit_feedback.php

// Go up one level to find the config folder
require_once '../config/db_connect.php'; 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

/**
 * Helper function to convert empty strings to NULL
 */
function nullify($value) {
    // If it's an array, handle it (though not expected here)
    // Trim string and check if it's empty
    return (isset($value) && trim($value) !== '') ? trim($value) : null;
}

try {
    // 1. Process Purpose of Stay (Dropdown + Other field)
    $purpose_input = $_POST['purpose_of_stay'] ?? null;
    if ($purpose_input === 'Other' && !empty(trim($_POST['other_purpose_text'] ?? ''))) {
        $final_purpose = "Other: " . trim($_POST['other_purpose_text']);
    } else {
        $final_purpose = nullify($purpose_input);
    }

    // 2. Process Dates (Combine Check-in and Check-out for the DB column)
    $check_in = $_POST['check_in'] ?? '';
    $check_out = $_POST['check_out'] ?? '';
    if (!empty($check_in) && !empty($check_out)) {
        $final_dates = $check_in . " to " . $check_out;
    } else {
        $final_dates = $check_in ?: $check_out ?: null;
    }

    // 3. Prepare Data for Database with NULL handling
    $data = [
        'frontdesk'           => nullify($_POST['frontdesk'] ?? null),
        'reservations'        => nullify($_POST['reservations'] ?? null),
        'telephone_operator'  => nullify($_POST['telephone_operator'] ?? null),
        'valet'               => nullify($_POST['valet'] ?? null),
        'housekeeping'        => nullify($_POST['housekeeping'] ?? null),
        'accommodation'       => nullify($_POST['accommodation'] ?? null),
        'safety'              => nullify($_POST['safety'] ?? null),
        'security'            => nullify($_POST['security'] ?? null),
        'overall_service'     => nullify($_POST['overall_service'] ?? null),
        'frontdesk_comments'  => nullify($_POST['frontdesk_comments'] ?? null),

        'food_quality'        => nullify($_POST['food_quality'] ?? null),
        'serving_time'        => nullify($_POST['serving_time'] ?? null),
        'wait_staff'          => nullify($_POST['wait_staff'] ?? null),
        'grooming'            => nullify($_POST['grooming'] ?? null),
        'behavior'            => nullify($_POST['behavior'] ?? null),
        'fnb_service'         => nullify($_POST['fnb_service'] ?? null),
        'bar'                 => nullify($_POST['bar'] ?? null),
        'bartender'           => nullify($_POST['bartender'] ?? null),
        'fnb_comments'        => nullify($_POST['fnb_comments'] ?? null),

        'helpful_staff_names' => nullify($_POST['helpful_staff_names'] ?? null),
        'suggestions_future'  => nullify($_POST['suggestions_future'] ?? null),
        'other_comments'      => nullify($_POST['other_comments'] ?? null),

        'guest_name'          => nullify($_POST['guest_name'] ?? null),
        'email'               => nullify($_POST['email'] ?? null),
        'address'             => nullify($_POST['address'] ?? null),
        'contact_no'          => nullify($_POST['contact_no'] ?? null),
        'room_no'             => nullify($_POST['room_no'] ?? null),
        'date_of_stay'        => $final_dates,
        'first_stay'          => nullify($_POST['first_stay'] ?? null),
        'purpose_of_stay'     => $final_purpose,
        
        'ip_address'          => $_SERVER['REMOTE_ADDR'] ?? null
    ];

    // 4. SQL Statement
    $sql = "INSERT INTO guest_feedbacks (
        frontdesk, reservations, telephone_operator, valet, housekeeping, accommodation, safety, security, overall_service, frontdesk_comments,
        food_quality, serving_time, wait_staff, grooming, behavior, fnb_service, bar, bartender, fnb_comments,
        helpful_staff_names, suggestions_future, other_comments,
        guest_name, email, address, contact_no, room_no, date_of_stay, first_stay, purpose_of_stay,
        ip_address
    ) VALUES (
        :frontdesk, :reservations, :telephone_operator, :valet, :housekeeping, :accommodation, :safety, :security, :overall_service, :frontdesk_comments,
        :food_quality, :serving_time, :wait_staff, :grooming, :behavior, :fnb_service, :bar, :bartender, :fnb_comments,
        :helpful_staff_names, :suggestions_future, :other_comments,
        :guest_name, :email, :address, :contact_no, :room_no, :date_of_stay, :first_stay, :purpose_of_stay,
        :ip_address
    )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);

    // 5. Success - Redirect to the Thank You UI page
    header("Location: thank_you.php");
    exit;

} catch (PDOException $e) {
    error_log($e->getMessage());
    die("Database Error: " . $e->getMessage());
}
?>