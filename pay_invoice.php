<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['student_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: student_dashboard.php#payments");
    exit();
}

$student_id = $_SESSION['student_id'];
$fee_type   = filter_input(INPUT_POST, 'fee_type', FILTER_SANITIZE_SPECIAL_CHARS);
$amount     = filter_input(INPUT_POST, 'fee_amount', FILTER_VALIDATE_FLOAT);

if (!$amount || $amount <= 0 || empty($fee_type)) {
    $_SESSION['payment_alert'] = ["type" => "error", "msg" => "Invalid billing configurations."];
    header("Location: student_dashboard.php#payments");
    exit();
}

// 1. Generate a unique reference
$tx_ref = 'GLD-MOCK-' . time() . '-' . rand(1000, 9999);

// 2. Log payment as pending in your database
$insert_query = "INSERT INTO institutional_payments (student_id, reference, payment_type, amount, status) VALUES (?, ?, ?, ?, 'pending')";
$ledger_stmt = $conn->prepare($insert_query);
if ($ledger_stmt) {
    $ledger_stmt->bind_param("issd", $student_id, $tx_ref, $fee_type, $amount);
    $ledger_stmt->execute();
    $ledger_stmt->close();
}

// 3. Instead of hitting Paystack API, redirect immediately to your verify_payment.php file 
// with the reference attached to the URL, exactly like Paystack would!
header("Location: verify_payment.php?reference=" . urlencode($tx_ref));
exit();

/* ==========================================================================
   GATEWAY API CONFIGURATION CREDENTIALS
   ========================================================================== */
// Replace this string with your actual Paystack Secret Key from your dashboard Settings -> API Keys
$secret_key = "sk_test_70d47dd315b48f42fb12290f386af482cc940f0d"; 

/* ==========================================================================
   INCOMING PAYLOAD INGESTION & VALIDATION
   ========================================================================== */
$student_id = $_SESSION['student_id'];
$fee_type   = filter_input(INPUT_POST, 'fee_type', FILTER_SANITIZE_SPECIAL_CHARS);
$amount     = filter_input(INPUT_POST, 'fee_amount', FILTER_VALIDATE_FLOAT);

// Fallback email retrieval if not globally stored during student login phase
$email = $_SESSION['student_email'] ?? '';
if (empty($email)) {
    $email_stmt = $conn->prepare("SELECT email FROM students WHERE student_id = ?");
    if ($email_stmt) {
        $email_stmt->bind_param("i", $student_id);
        $email_stmt->execute();
        $email_res = $email_stmt->get_result()->fetch_assoc();
        $email = $email_res['email'] ?? 'student.' . $student_id . '@graceland.edu.ng';
        $email_stmt->close();
    }
}

// Fail gracefully if the amount parameter is corrupted or missing
if (!$amount || $amount <= 0 || empty($fee_type)) {
    $_SESSION['payment_alert'] = [
        "type" => "error", 
        "msg" => "Invalid billing calculations detected. Initiation terminated."
    ];
    header("Location: student_dashboard.php#payments");
    exit();
}

/* ==========================================================================
   TRANSACTION REFERENCE GENERATION & DATABASE LEDGER WRITE
   ========================================================================== */
// Generates an alphanumeric reference block string e.g., GLD-1717972345-8492
$tx_ref = 'GLD-' . time() . '-' . rand(1000, 9999);

// Write entry into the verification history table as 'pending'
$insert_query = "INSERT INTO institutional_payments (student_id, reference, payment_type, amount, status) VALUES (?, ?, ?, ?, 'pending')";
$ledger_stmt = $conn->prepare($insert_query);
if (!$ledger_stmt) {
    $_SESSION['payment_alert'] = [
        "type" => "error", 
        "msg" => "Database ledger connection failure. Please contact system admin."
    ];
    header("Location: student_dashboard.php#payments");
    exit();
}

$ledger_stmt->bind_param("issd", $student_id, $tx_ref, $fee_type, $amount);
$ledger_stmt->execute();
$ledger_stmt->close();

/* ==========================================================================
   API GATEWAY cURL ENGAGEMENT PROTOCOL
   ========================================================================== */
$url = "https://api.paystack.co/transaction/initialize";

// Paystack takes values in Kobo (Amount in Naira multiplied by 100)
$payable_amount_kobo = $amount * 100; 

// Build the dynamic return callback location absolute address path
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
// Grabs current directory containing your portal files automatically
$current_dir = dirname($_SERVER['REQUEST_URI']); 
$callback_url = $protocol . $host . $current_dir . "/verify_payment.php";

$payload_fields = [
    'email'        => $email,
    'amount'       => $payable_amount_kobo,
    'reference'    => $tx_ref,
    'callback_url' => $callback_url,
    'metadata'     => [
        'student_id' => $student_id,
        'fee_item'   => $fee_type
    ]
];

// Open secure channel connection stream
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload_fields));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . trim($secret_key),
    "Content-Type: application/json",
    "Cache-Control: no-cache"
]);

$gateway_response = curl_exec($ch);
$curl_error       = curl_error($ch);
curl_close($ch);

// Intercept low-level host server network connection drops
if ($curl_error) {
    $_SESSION['payment_alert'] = [
        "type" => "error", 
        "msg" => "Gateway connectivity link lost: " . $curl_error
    ];
    header("Location: student_dashboard.php#payments");
    exit();
}

$transaction_data = json_decode($gateway_response, true);

/* ==========================================================================
   RESPONSE ROUTING ROUTINE
   ========================================================================== */
if (isset($transaction_data['status']) && $transaction_data['status'] === true) {
    // Successfully authorized transaction initialization. Redirecting out to checkout terminal portal
    header("Location: " . $transaction_data['data']['authorization_url']);
    exit();
} else {
    // Gateway rejected key credentials or payload configuration format
    $error_details = $transaction_data['message'] ?? 'API Token Disallowed.';
    $_SESSION['payment_alert'] = [
        "type" => "error", 
        "msg" => "Gateway Engine Rejection: " . $error_details
    ];
    header("Location: student_dashboard.php#payments");
    exit();
}