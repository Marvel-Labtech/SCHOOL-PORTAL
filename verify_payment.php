<?php
session_start();
require_once 'db.php';

/* ==========================================================================
   GATEKEEPER SECURITY ROUTE PROTECTION
   ========================================================================== */
if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit();
}

// If no reference token is passed in the URL string, kick back to payments
if (!isset($_GET['reference']) || empty(trim($_GET['reference']))) {
    $_SESSION['payment_alert'] = [
        "type" => "error", 
        "msg" => "No explicit transaction tracking token provided by the gateway channel."
    ];
    header("Location: student_dashboard.php#payments");
    exit();
}

$reference = trim($_GET['reference']);

/* ==========================================================================
   LOCAL GATEWAY SIMULATOR (MOCK VERIFICATION LOGIC)
   ========================================================================== */
// Instead of calling Paystack's server with cURL, we directly approve the local transaction!

// Update your institutional_payments table status to 'success'
$update_query = "UPDATE institutional_payments SET status = 'success' WHERE reference = ? AND status = 'pending'";
$update_stmt  = $conn->prepare($update_query);

if ($update_stmt) {
    $update_stmt->bind_param("s", $reference);
    $update_stmt->execute();
    
    if ($update_stmt->affected_rows > 0) {
        // First time verifying this transaction
        $_SESSION['payment_alert'] = [
            "type" => "success", 
            "msg" => "MOCK GATEWAY SUCCESS: Transaction Reference: " . htmlspecialchars($reference) . " approved locally!"
        ];
    } else {
        // Already updated, or reference string doesn't match a pending record in database
        $_SESSION['payment_alert'] = [
            "type" => "success", 
            "msg" => "Payment transaction was already processed and verified on this account profile."
        ];
    }
    $update_stmt->close();
} else {
    $_SESSION['payment_alert'] = [
        "type" => "error", 
        "msg" => "Critical Database Ledger Engine fault encountered during validation write."
    ];
}

// Route them smoothly back into your student interface portal dashboard hash view panel
header("Location: student_dashboard.php#payments");
exit();

$reference = trim($_GET['reference']);

/* ==========================================================================
   GATEWAY API CONFIGURATION CREDENTIALS
   ========================================================================== */
// IMPORTANT: This key must match your secret key inside pay_invoice.php perfectly
$secret_key = "sk_test_70d47dd315b48f42fb12290f386af482cc940f0d"; 

/* ==========================================================================
   API GATEWAY cURL ENGAGEMENT PROTOCOL (VERIFICATION FETCH)
   ========================================================================== */
$url = "https://api.paystack.co/transaction/verify/" . rawurlencode($reference);

// Open secure channel connection stream to fetch status from Paystack
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . trim($secret_key),
    "Cache-Control: no-cache"
]);

$gateway_response = curl_exec($ch);
$curl_error       = curl_error($ch);
curl_close($ch);

// Intercept low-level host server network connection drops
if ($curl_error) {
    $_SESSION['payment_alert'] = [
        "type" => "error", 
        "msg" => "Verification link timed out due to a network connection drop. Please reload."
    ];
    header("Location: student_dashboard.php#payments");
    exit();
}

$result = json_decode($gateway_response, true);

/* ==========================================================================
   RESPONSE ROUTING & DATABASE UPDATE ROUTINE
   ========================================================================== */
if (isset($result['status']) && $result['status'] === true && $result['data']['status'] === 'success') {
    
    // The payment cleared! Pull down transaction log variables to double-check against tinkering
    $amount_paid_naira = $result['data']['amount'] / 100; // Convert back from Kobo to Naira
    
    // Update your institutional_payments table status to 'success'
    $update_query = "UPDATE institutional_payments SET status = 'success' WHERE reference = ? AND status = 'pending'";
    $update_stmt  = $conn->prepare($update_query);
    
    if ($update_stmt) {
        $update_stmt->bind_param("s", $reference);
        $update_stmt->execute();
        
        if ($update_stmt->affected_rows > 0) {
            // Logged successfully
            $_SESSION['payment_alert'] = [
                "type" => "success", 
                "msg" => "Payment verified! Transaction Reference: " . htmlspecialchars($reference) . " was processed successfully."
            ];
        } else {
            // Already updated or tracking reference missing from database records
            $_SESSION['payment_alert'] = [
                "type" => "success", 
                "msg" => "Payment transaction was already processed and verified on this account profile."
            ];
        }
        $update_stmt->close();
    } else {
        $_SESSION['payment_alert'] = [
            "type" => "error", 
            "msg" => "Critical Database Ledger Engine fault encountered during validation write."
        ];
    }

    header("Location: student_dashboard.php#payments");
    exit();

} else {
    // Payment was canceled, abandoned, or rejected by the card bank engine
    $fail_query = "UPDATE institutional_payments SET status = 'failed' WHERE reference = ? AND status = 'pending'";
    $fail_stmt  = $conn->prepare($fail_query);
    
    if ($fail_stmt) {
        $fail_stmt->bind_param("s", $reference);
        $fail_stmt->execute();
        $fail_stmt->close();
    }
    
    // Gather error message context from response payload if present
    $gateway_msg = $result['message'] ?? 'Transaction unverified or abandoned by user.';
    
    $_SESSION['payment_alert'] = [
        "type" => "error", 
        "msg" => "Payment Failed: " . htmlspecialchars($gateway_msg)
    ];
    header("Location: student_dashboard.php#payments");
    exit();
}