<?php
include("db.php");

// Fetch products from database
$sql = "SELECT * FROM fruitsandveggies GROUP BY Fruits ORDER BY Fruits ASC";
$result = $conn->query($sql);

if (!$result) {
    die("Error loading products: " . $conn->error);
}

// Store all rows so we can loop over them twice
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

$status = $_GET['status'] ?? '';
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

    <style>
        * {
            box-sizing: border-box;
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f8dce8;
            color: #292929;
        }

        /* NAVIGATION BAR */
        .navbar {
            background: #fff;
            border-bottom: 2px solid #333;
            padding: 12px 35px;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .navbar-brand img {
            width: 55px;
            height: 55px;
            object-fit: contain;
        }

        .navbar-brand span {
            font-size: 24px;
            font-weight: 700;
            color: #292929;
        }

        .nav-link {
            color: #333;
            font-weight: 600;
            margin-left: 15px;
            transition: color 0.3s ease;
        }

        .nav-link:hover,
        .nav-link.active {
            color: #159b42;
        }

        .navbar-toggler {
            border: 1px solid #333;
        }

        /* ALERT MESSAGES */
        .alert {
            border-radius: 10px;
        }

        /* PAGE HEADING */
        .page-heading {
            text-align: center;
            padding: 45px 15px 20px;
        }

        .page-heading h1 {
            font-size: 34px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .page-heading p {
            color: #666;
            font-size: 16px;
            margin-bottom: 0;
        }

        /* PRODUCTS SECTION */
        .products-container {
            width: 100%;
            padding: 25px 40px 55px;
        }

        /* FOUR PRODUCT CARDS PER ROW (HORIZONTAL) */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 25px;
            width: 100%;
            align-items: stretch;
        }

        .product-item {
            width: 100%;
            min-width: 0;
        }

        /* PRODUCT CARD */
        .product-card {
            width: 100%;
            height: 100%;
            min-height: 320px;
            background: #fff;
            border: 1px solid #333;
            border-radius: 22px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        /* PRODUCT IMAGE */
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: contain;
            padding: 18px;
        }

        /* PRODUCT INFORMATION */
        .product-info {
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-grow: 1;
        }

        .product-price {
            font-size: 20px;
            font-weight: 700;
            color: #159b42;
            white-space: nowrap;
        }

        .product-name {
            font-size: 16px;
            font-weight: 600;
            text-align: right;
            overflow-wrap: anywhere;
        }

        /* ADD TO CART BUTTON */
        .product-button {
            width: calc(100% - 30px);
            margin: 10px 15px 15px;
            padding: 12px;
            background: #25c95b;
            color: #111;
            border: none;
            border-radius: 25px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.3s ease, color 0.3s ease;
        }

        .product-button:hover {
            background: #159b42;
            color: #fff;
        }

        /* EMPTY PRODUCTS MESSAGE */
        .empty-products {
            grid-column: 1 / -1;
            text-align: center;
            padding: 50px 15px;
            background: #fff;
            border-radius: 20px;
            border: 1px dashed #ccc;
        }

        /* ORDER MODAL */
        .modal-content {
            background: #f8dce8;
            border: none;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            border-bottom: none;
            padding: 18px 20px 10px;
        }

        .modal-title {
            font-weight: 700;
        }

        .modal-body {
            padding: 15px 20px 20px;
        }

        .modal-product-card {
            background: #fff;
            border: 1px solid #333;
            border-radius: 20px;
            padding: 15px;
        }

        .modal-product-image {
            display: block;
            width: 100%;
            height: 160px;
            object-fit: contain;
        }

        .modal-product-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin: 12px 5px 20px;
            font-weight: 600;
        }

        /* FORM INPUTS */
        .input-group {
            margin-bottom: 14px;
        }

        .input-group-text {
            min-width: 115px;
            background: #f3f3f3;
            font-weight: 600;
        }

        .form-control {
            min-width: 0;
            padding: 10px;
        }

        .form-control:focus {
            border-color: #25c95b;
            box-shadow: 0 0 0 0.2rem rgba(37, 201, 91, 0.15);
        }

        /* SUBMIT BUTTON */
        .submit-button {
            width: 100%;
            padding: 13px;
            margin-top: 5px;
            background: #25c95b;
            border: none;
            border-radius: 25px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.3s ease, color 0.3s ease;
        }

        .submit-button:hover {
            background: #159b42;
            color: #fff;
        }

        /* ABOUT AND CONTACT */
        .information-section {
            background: #fff;
            padding: 45px 20px;
            margin-top: 25px;
        }

        .information-section h2 {
            text-align: center;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .information-section p {
            max-width: 750px;
            margin: 10px auto;
            text-align: center;
            line-height: 1.8;
            color: #555;
        }

        .information-section a {
            color: #159b42;
            text-decoration: none;
        }

        .information-section a:hover {
            text-decoration: underline;
        }

        /* FOOTER */
        footer {
            background: #292929;
            color: #fff;
            text-align: center;
            padding: 25px 15px;
        }

        footer a {
            color: #72ed97;
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }

        /* TABLET RESPONSIVENESS */
        @media (max-width: 991px) {
            .products-container {
                padding: 20px 25px 40px;
            }

            .products-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 15px;
            }

            .product-card {
                min-height: 300px;
            }

            .product-image {
                height: 180px;
            }

            .product-price {
                font-size: 16px;
            }

            .product-name {
                font-size: 14px;
            }

            .product-info {
                padding: 10px;
                flex-wrap: wrap;
            }

            .nav-link {
                margin-left: 0;
            }
        }

        /* MOBILE RESPONSIVENESS */
        @media (max-width: 576px) {
            .navbar {
                padding: 10px 15px;
            }

            .navbar-brand img {
                width: 45px;
                height: 45px;
            }

            .navbar-brand span {
                font-size: 20px;
            }

            .page-heading {
                padding: 30px 15px 15px;
            }

            .page-heading h1 {
                font-size: 26px;
            }

            .page-heading p {
                font-size: 14px;
            }

            .products-container {
                padding: 15px 12px 35px;
            }

            .products-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 12px;
            }

            .product-card {
                min-height: 260px;
            }

            .product-image {
                height: 150px;
                padding: 10px;
            }

            .product-info {
                padding: 10px;
                flex-direction: column;
                align-items: center;
                gap: 5px;
            }

            .product-price {
                font-size: 17px;
            }

            .product-name {
                font-size: 14px;
                text-align: center;
            }

            .product-button {
                width: calc(100% - 20px);
                margin: 8px 10px 12px;
                padding: 10px;
                font-size: 13px;
            }

            .information-section {
                padding: 35px 18px;
            }

            .information-section h2 {
                font-size: 24px;
            }

            .modal-body {
                padding: 10px;
            }

            .modal-product-card {
                padding: 12px;
            }

            .input-group-text {
                min-width: 105px;
                font-size: 13px;
            }
        }
    </style>
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

            <div
                class="collapse navbar-collapse justify-content-end"
                id="navbarNav">

                <ul class="navbar-nav">

                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            Home
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#about">
                            About Us
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#contact">
                            Contact Us
                        </a>
                    </li>

                </ul>
            </div>
        </div>
    </nav>

    <!-- ORDER STATUS MESSAGES -->
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

                    $productId    = (int) ($row['Productid'] ?? $row['ProductID'] ?? $row['ProductId'] ?? $row['id'] ?? 0);
                    $productName  = $row['Fruits'] ?? 'Unknown Product';
                    $productPrice = (float) ($row['Price'] ?? 0);
                    $productImage = $row['Image'] ?? 'default';
                    $modalId      = "product-modal-" . ($index + 1);
                ?>

                    <!-- PRODUCT CARD -->
                    <div class="product-item">
                        <div class="product-card">
                            <img
                                src="images/<?= htmlspecialchars($productImage, ENT_QUOTES, 'UTF-8') ?>.png"
                                alt="<?= htmlspecialchars($productName, ENT_QUOTES, 'UTF-8') ?>"
                                class="product-image"
                                loading="lazy"
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

                <?php endforeach; ?>

            <?php endif; ?>

        </div>
    </section>

    <!-- PRODUCT ORDER MODALS -->
    <?php foreach ($products as $index => $row):
        $productId    = (int) ($row['Productid'] ?? $row['ProductID'] ?? $row['ProductId'] ?? $row['id'] ?? 0);
        $productName  = $row['Fruits'] ?? 'Unknown Product';
        $productPrice = (float) ($row['Price'] ?? 0);
        $productImage = $row['Image'] ?? 'default';
        $modalId      = "product-modal-" . ($index + 1);
    ?>

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
                                src="images/<?= htmlspecialchars($productImage, ENT_QUOTES, 'UTF-8') ?>.png"
                                alt="<?= htmlspecialchars($productName, ENT_QUOTES, 'UTF-8') ?>"
                                class="modal-product-image"
                            >
                            <div class="modal-product-info">
                                <span class="fs-5">₱<?= number_format($productPrice, 2) ?></span>
                                <span class="fs-5"><?= htmlspecialchars($productName, ENT_QUOTES, 'UTF-8') ?></span>
                            </div>

                            <form action="insert.php" method="POST" class="order-form" data-price="<?= htmlspecialchars((string) $productPrice, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="product_id" value="<?= $productId ?>">

                                <div class="input-group">
                                    <span class="input-group-text">Price</span>
                                    <input type="text" class="form-control" value="₱<?= number_format($productPrice, 2) ?>" readonly>
                                </div>

                                <div class="input-group">
                                    <span class="input-group-text">Quantity</span>
                                    <input type="number" class="form-control quantity-input" name="Qty" min="1" max="999" step="1" value="1" required>
                                </div>

                                <div class="input-group">
                                    <span class="input-group-text">Total</span>
                                    <input type="text" class="form-control total-display" value="₱<?= number_format($productPrice, 2) ?>" readonly>
                                </div>

                                <div class="input-group">
                                    <span class="input-group-text">Buyer's Name</span>
                                    <input type="text" class="form-control" name="User" maxlength="100" placeholder="Enter your name" required>
                                </div>

                                <div class="input-group">
                                    <span class="input-group-text">Mobile Number</span>
                                    <input type="tel" class="form-control" name="Contact" maxlength="20" pattern="[0-9+\-\s()]{7,20}" placeholder="Enter mobile number" required>
                                </div>

                                <button type="submit" class="submit-button">Submit Order</button>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php endforeach; ?>

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
        document.querySelectorAll(".order-form").forEach(function(form) {
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