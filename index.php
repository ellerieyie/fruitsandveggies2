<?php

session_start();

include("db.php");

/* CSRF token for the order forms */
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

/*
 * Use explicit columns instead of SELECT * ... GROUP BY, which errors
 * under ONLY_FULL_GROUP_BY and silently drops rows otherwise.
 * Replace "Productid" below with your actual column name.
 */
$sql = "SELECT Productid, Fruits, Price, Image
        FROM fruitsandveggies
        ORDER BY Fruits ASC";

$result = $conn->query($sql);

if (!$result) {
    die("Error loading products: " . $conn->error);
}

// Store all rows so we can loop over them once and render card + modal
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

$status = $_GET['status'] ?? '';
$showAlert = in_array($status, ['success', 'error'], true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Palengke Kiosk | Fresh Fruits and Vegetables</title>

    <!-- Bootstrap CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="style.css">
</head>

<body>

    <!-- NAVIGATION BAR -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">

            <a class="navbar-brand" href="index.php">
                <img src="images/logo.png" alt="Palengke Kiosk Logo">
                <span>Palengke Kiosk</span>
            </a>

            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarNav"
                aria-controls="navbarNav"
                aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact Us</a>
                    </li>
                </ul>
            </div>

        </div>
    </nav>

    <!-- ORDER STATUS MESSAGES -->
    <?php if ($showAlert): ?>
        <div class="container mt-3">
            <?php if ($status === 'success'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Order placed successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif ($status === 'error'): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Your order could not be placed. Please check your information and try again.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- PAGE HEADING -->
    <section class="page-heading">
        <h1>Fresh Fruits and Vegetables</h1>
        <p>Shop your favorite fresh products at Palengke Kiosk!</p>
    </section>

    <!-- PRODUCTS SECTION (HORIZONTAL GRID, 4 PER ROW) -->
    <section class="products-container">
        <div class="products-grid">

            <?php if (empty($products)): ?>

                <div class="empty-products">
                    <h4>No products available yet.</h4>
                    <p>Please check back later.</p>
                </div>

            <?php else: ?>

                <?php foreach ($products as $index => $row):

                    /* Replace 'Productid' with the real column from your schema */
                    $productId = (int) ($row['Productid'] ?? 0);

                    if ($productId <= 0) {
                        continue;
                    }

                    $productName  = $row['Fruits'] ?? 'Unknown Product';
                    $productPrice = (float) ($row['Price'] ?? 0);
                    $productImage = trim((string) ($row['Image'] ?? ''));

                    /* Store bare name only in DB; fall back to placeholder if missing */
                    $imageFile = 'images/' . ($productImage !== '' ? $productImage : 'default') . '.png';
                    $modalId   = 'product-modal-' . $productId;
                ?>

                    <!-- PRODUCT CARD -->
                    <div class="product-item">
                        <div class="product-card">

                            <img
                                src="<?= htmlspecialchars($imageFile, ENT_QUOTES, 'UTF-8') ?>"
                                alt="<?= htmlspecialchars($productName, ENT_QUOTES, 'UTF-8') ?>"
                                class="product-image"
                                loading="lazy"
                                onerror="this.src='images/default.png';"
                            >

                            <div class="product-info">
                                <span class="product-price">
                                    ₱<?= number_format($productPrice, 2) ?>
                                </span>
                                <span class="product-name">
                                    <?= htmlspecialchars($productName, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </div>

                            <button
                                type="button"
                                class="product-button"
                                data-bs-toggle="modal"
                                data-bs-target="#<?= $modalId ?>">
                                Add to Cart
                            </button>

                        </div>
                    </div>

                    <!-- PRODUCT ORDER MODAL -->
                    <div class="modal fade" id="<?= $modalId ?>" tabindex="-1" aria-labelledby="<?= $modalId ?>-label" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">

                                <div class="modal-header">
                                    <h5 class="modal-title" id="<?= $modalId ?>-label">
                                        Order <?= htmlspecialchars($productName, ENT_QUOTES, 'UTF-8') ?>
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <div class="modal-body">
                                    <div class="modal-product-card">

                                        <img
                                            src="<?= htmlspecialchars($imageFile, ENT_QUOTES, 'UTF-8') ?>"
                                            alt="<?= htmlspecialchars($productName, ENT_QUOTES, 'UTF-8') ?>"
                                            class="modal-product-image"
                                            onerror="this.src='images/default.png';"
                                        >

                                        <div class="modal-product-info">
                                            <span class="fs-5">₱<?= number_format($productPrice, 2) ?></span>
                                            <span class="fs-5"><?= htmlspecialchars($productName, ENT_QUOTES, 'UTF-8') ?></span>
                                        </div>

                                        <form
                                            action="insert.php"
                                            method="POST"
                                            class="order-form"
                                            data-price="<?= htmlspecialchars((string) $productPrice, ENT_QUOTES, 'UTF-8') ?>">

                                            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="product_id" value="<?= $productId ?>">

                                            <div class="input-group">
                                                <span class="input-group-text">Price</span>
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    value="₱<?= number_format($productPrice, 2) ?>"
                                                    readonly
                                                    aria-label="Price">
                                            </div>

                                            <div class="input-group">
                                                <label class="input-group-text" for="<?= $modalId ?>-qty">Quantity</label>
                                                <input
                                                    type="number"
                                                    class="form-control quantity-input"
                                                    id="<?= $modalId ?>-qty"
                                                    name="Qty"
                                                    min="1"
                                                    max="999"
                                                    step="1"
                                                    value="1"
                                                    required>
                                            </div>

                                            <div class="input-group">
                                                <span class="input-group-text">Total</span>
                                                <input
                                                    type="text"
                                                    class="form-control total-display"
                                                    value="₱<?= number_format($productPrice, 2) ?>"
                                                    readonly
                                                    aria-label="Total">
                                            </div>

                                            <div class="input-group">
                                                <label class="input-group-text" for="<?= $modalId ?>-name">Buyer's Name</label>
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    id="<?= $modalId ?>-name"
                                                    name="User"
                                                    maxlength="100"
                                                    placeholder="Enter your name"
                                                    required>
                                            </div>

                                            <div class="input-group">
                                                <label class="input-group-text" for="<?= $modalId ?>-contact">Mobile Number</label>
                                                <input
                                                    type="tel"
                                                    class="form-control"
                                                    id="<?= $modalId ?>-contact"
                                                    name="Contact"
                                                    maxlength="20"
                                                    pattern="[0-9+\-\s()]{7,20}"
                                                    placeholder="Enter mobile number"
                                                    required>
                                            </div>

                                            <button type="submit" class="submit-button">Submit Order</button>

                                        </form>

                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>

            <?php endif; ?>

        </div>
    </section>

    <!-- ABOUT US -->
    <section id="about" class="information-section">
        <h2>About Us</h2>
        <p>
            Palengke Kiosk is an online marketplace that makes buying fresh
            fruits and vegetables easier and more convenient. Customers can
            browse available products, check prices, and place their orders
            using a simple and user-friendly website.
        </p>
    </section>

    <!-- CONTACT US -->
    <section id="contact" class="information-section">
        <h2>Contact Us</h2>
        <p>
            Do you have questions or suggestions? Contact us for assistance
            with your orders and other concerns.
        </p>
        <p>
            Email: <a href="mailto:contact@palengkekiosk.com">contact@palengkekiosk.com</a>
        </p>
        <p>
            Phone: <a href="tel:+639123456789">+63 912 345 6789</a>
        </p>
    </section>

    <!-- FOOTER -->
    <footer>
        <p class="mb-2">
            &copy; <?= date("Y") ?> Palengke Kiosk. All Rights Reserved.
        </p>
        <p class="mb-0">
            <a href="#about">About Us</a> |
            <a href="#contact">Contact Us</a>
        </p>
    </footer>

    <!-- BOOTSTRAP JAVASCRIPT -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- QUANTITY AND TOTAL CALCULATION -->
    <script>
        document.querySelectorAll(".order-form").forEach(function (form) {
            const quantityInput = form.querySelector(".quantity-input");
            const totalDisplay  = form.querySelector(".total-display");
            const price = Number(form.dataset.price);

            function calculateTotal() {
                let quantity = Number(quantityInput.value);

                if (!Number.isFinite(quantity) || quantity < 1) {
                    quantity = 1;
                }

                totalDisplay.value = "₱" + (price * quantity).toLocaleString(
                    "en-PH",
                    {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }
                );
            }

            quantityInput.addEventListener("input", calculateTotal);
            calculateTotal();
        });
    </script>

</body>
</html>

<?php
$conn->close();
?>