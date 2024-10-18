<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="ProTracker - Finance Tracking Tool">
    <title>proTracker</title>

    <!-- Bootstrap CSS for quick styling -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }

        .navbar {
            background-color: rgb(16 185 129);
        }

        .navbar-brand {
            font-weight: 600;
            font-size: 1.5rem;
            color: #fff;
        }

        .header-section {
            background-image: url('/img/finance.webp');
            background-size: cover;
            background-position: center;
            height: 100vh;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .header-section h1 {
            font-size: 4rem;
            font-weight: bold;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.5);
        }

        .header-section p {
            font-size: 1.2rem;
            text-shadow: 1px 1px 6px rgba(0, 0, 0, 0.5);
            color: black;
        }

        .cta-button {
            background-color: rgb(16 185 129);
            color: white;
            padding: 12px 24px;
            font-size: 1.1rem;
            border-radius: 30px;
            transition: background-color 0.3s ease;
        }

        .cta-button:hover {
            background-color: #218838;
        }

        .services-section {
            padding: 5rem 0;
        }

        .services-section h2 {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 3rem;
        }

        .service-card {
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border: none;
            transition: transform 0.3s ease;
        }

        .service-card:hover {
            transform: translateY(-10px);
        }

        .service-card img {
            height: 180px;
            width: 100%;
            object-fit: cover;
        }

        .footer {
            background-color: rgb(16 185 129);
            color: #fff;
            padding: 2rem 0;
            text-align: center;
        }

        .footer a {
            color: #fff;
            text-decoration: none;
        }
    </style>
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="/">proTracker</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/admin/login">Login</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Header Section -->
<section class="header-section">
    <div class="container text-center">
        <p>
            You can keep your finances in order, track by account, type(expense/income),
            category, advanced filtering and reporting summaries, stats and charts,
            it's simple and straight forward.
        </p>
        <a href="/admin/login" class="cta-button mt-4">Get Started</a>
    </div>
</section>

<!-- Services Section -->
<!--
<section class="services-section" id="services">
    <div class="container">
        <h2>Our Financial Services</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="card service-card">
                    <img src="https://via.placeholder.com/400x300" alt="Investment Planning">
                    <div class="card-body">
                        <h5 class="card-title">Investment Planning</h5>
                        <p class="card-text">Achieve your financial goals with our expert investment advice and personalized strategies.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card service-card">
                    <img src="https://via.placeholder.com/400x300" alt="Wealth Management">
                    <div class="card-body">
                        <h5 class="card-title">Wealth Management</h5>
                        <p class="card-text">Comprehensive wealth management solutions to grow and preserve your assets.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card service-card">
                    <img src="https://via.placeholder.com/400x300" alt="Insurance Services">
                    <div class="card-body">
                        <h5 class="card-title">Insurance Services</h5>
                        <p class="card-text">Protect your family and future with our customized insurance products.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
-->

<!-- Footer Section -->
<footer class="footer">
    <div class="container">
        <p>&copy; 2024 proTracker. All rights reserved.</p>
        <p>
            <a href="#">Privacy Policy</a> |
            <a href="#">Terms & Conditions</a>
        </p>
    </div>
</footer>

<!-- Bootstrap JS & jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
