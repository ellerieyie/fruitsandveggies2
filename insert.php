<?php
include("db.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$productId = filter_input(INPUT_POST, "product_id", FILTER_VALIDATE_INT);
$qty = filter_input(INPUT_POST, "Qty", FILTER_VALIDATE_INT);

$user = trim($_POST["User"] ?? "");
$contact = trim($_POST["Contact"] ?? "");

if (
    !$productId ||
    !$qty ||
    $qty < 1 ||
    $qty > 999 ||
    $user === "" ||
    strlen($user) > 100 ||
    $contact === "" ||
    strlen($contact) > 20 ||
    !preg_match('/^[0-9+\-\s()]{7,20}$/', $contact)
) {
    $conn->close();
    header("Location: index.php?status=error");
    exit;
}

// Kunin ang presyo
$stmt = $conn->prepare("SELECT Price FROM fruitsandveggies WHERE Productid = ?");
if (!$stmt) {
    $conn->close();
    header("Location: index.php?status=error");
    exit;
}

$stmt->bind_param("i", $productId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    $conn->close();
    header("Location: index.php?status=error");
    exit;
}

$price = (float) $product["Price"];
$total = $price * $qty;

// BINAGO NATIN ITO: productsss ang table name, hindi orders
$stmt = $conn->prepare("INSERT INTO productsss (Productid, Qty, Total, `User`, Contact) VALUES (?, ?, ?, ?, ?)");
if (!$stmt) {
    $conn->close();
    header("Location: index.php?status=error");
    exit;
}

$stmt->bind_param("iidss", $productId, $qty, $total, $user, $contact);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    header("Location: index.php?status=success");
    exit;
}

$stmt->close();
$conn->close();
header("Location: index.php?status=error");
exit;
?>