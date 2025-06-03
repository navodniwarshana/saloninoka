<?php

session_start();
require 'conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerName = $_POST['customerName'];
    $service = $_POST['service'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $status = 'New';

    // Save values to session
    $_SESSION['customerName'] = $customerName;
    $_SESSION['appointmentDate'] = $date;

    $stmt = $pdo->prepare("INSERT INTO appointment (appointmentDate, customerName, appointmentTime, service, status)
                           VALUES (:date, :customerName, :time, :service, :status)");
    $stmt->execute([
        ':date' => $date,
        ':customerName' => $customerName,
        ':time' => $time,
        ':service' => $service,
        ':status' => $status
    ]);


}

$appointmentText = "No appointment found.";

if (!empty($_SESSION['customerName'])) {
    $customerName = $_SESSION['customerName'];

    $stmt = $pdo->prepare("
        SELECT service, status, appointmentDate, appointmentTime
        FROM appointment
        WHERE customerName = :name
        ORDER BY appointmentDate DESC, appointmentTime DESC
        LIMIT 1
    ");
    $stmt->execute([':name' => $customerName]);
    $row = $stmt->fetch();

    if ($row) {
        $appointmentText = "Service: {$row['service']} | Status: {$row['status']} | Date: " .
            date('M d', strtotime($row['appointmentDate'])) . " | Time: " .
            date('h:i A', strtotime($row['appointmentTime']));
    }
}



$selectedDate = $_POST['date'] ?? '';
$bookedTimes = [];

if (!empty($selectedDate)) {
    $stmt = $pdo->prepare("SELECT appointmentTime FROM appointment WHERE appointmentDate = :date AND status != 'Cancelled'");
    $stmt->execute([':date' => $selectedDate]);
    $bookedTimes = array_column($stmt->fetchAll(), 'appointmentTime');
}

// All possible time slots
$allTimes = [
    "09:00",
    "09:15",
    "09:30",
    "09:45",
    "10:00",
    "10:15",
    "10:30",
    "10:45",
    "11:00",
    "11:15",
    "11:30",
    "11:45",
    "12:00",
    "12:15",
    "12:30",
    "12:45",
    "13:00",
    "13:15",
    "13:30",
    "13:45",
    "14:00",
    "14:15",
    "14:30",
    "14:45",
    "15:00",
    "15:15",
    "15:30",
    "15:45",
    "16:00",
    "16:15",
    "16:30"
];
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Salon Inoka</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&family=Oswald:wght@600&display=swap"
        rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
    <style>
        html {
            scroll-behavior: smooth;
        }
    </style>
</head>

<body>
    <!-- Spinner Start -->
    <div id="spinner"
        class="show bg-dark position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-grow text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!-- Spinner End -->



    <!-- Navbar Start -->
    <!-- Sections -->
    <section id="home">
        <nav class="navbar navbar-expand-lg bg-secondary navbar-dark sticky-top py-lg-0 px-lg-5 wow fadeIn">
            <a href="index.html" class="navbar-brand ms-4 ms-lg-0">
                <img src="img/logo.png" alt="">
            </a>
            <button type="button" class="navbar-toggler me-4" data-bs-toggle="collapse"
                data-bs-target="#navbarCollapse">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <!-- Navbar -->
                <div class="navbar-nav ms-auto p-4 p-lg-0">
                    <a href="#home" class="nav-item nav-link active">HOME</a>
                    <a href="#services" class="nav-item nav-link">OUR SERVICES</a>
                    <a href="#contact" class="nav-item nav-link">CONTACT</a>
                    <a class=" py-2 px-lg-4 d-lg-none " data-bs-toggle="modal" data-bs-target="#bookingModal">Book
                        Appointment</a>


                </div>
                <a class="navbar-nav btn btn-primary rounded-0 py-2 px-lg-4 d-none d-lg-block" data-bs-toggle="modal"
                    data-bs-target="#bookingModal">Book Appointment</a>


            </div>
        </nav>
        <!-- Navbar End -->

        <!-- ✅ Success Alert -->
        <div id="success-alert" class="alert alert-success d-none" role="alert">
            ✅ Appointment booked successfully!
        </div>

        <!-- Carousel Start -->
        <div class="container-fluid p-0 mb-5 wow fadeIn" data-wow-delay="0.1s">
            <div id="header-carousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img class="w-100" src="img/carousel-1.jpg" alt="Image">
                        <div class="carousel-caption d-flex align-items-center justify-content-center text-start">
                            <div class="mx-sm-5 px-5" style="max-width: 900px;">
                                <h1 class="display-2 text-white text-uppercase mb-4 animated slideInDown">Where Your
                                    Hair
                                    Dreams Come True</h1>
                                <h4 class="text-white text-uppercase mb-4 animated slideInDown"><i
                                        class="fa fa-map-marker-alt text-primary me-3"></i>No.00 Dodawatta, Noori.
                                </h4>
                                <h4 class="text-white text-uppercase mb-4 animated slideInDown"><i
                                        class="fa fa-phone-alt text-primary me-3"></i>+94 71 123 4567</h4>

                            </div>
                        </div>

                    </div>

                    <div class="carousel-item">
                        <img class="w-100" src="img/carousel-2.jpg" alt="Image">
                        <div class="carousel-caption d-flex align-items-center justify-content-center text-start">
                            <div class="mx-sm-5 px-5" style="max-width: 900px;">
                                <h1 class="display-2 text-white text-uppercase mb-4 animated slideInDown">Redefining
                                    Men’s Style, One Cut at a Time</h1>
                                <h4 class="text-white text-uppercase mb-4 animated slideInDown"><i
                                        class="fa fa-map-marker-alt text-primary me-3"></i>No.00 Dodawatta, Noori.
                                </h4>
                                <h4 class="text-white text-uppercase mb-4 animated slideInDown"><i
                                        class="fa fa-phone-alt text-primary me-3"></i>+94 71 123 4567</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#header-carousel"
                    data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#header-carousel"
                    data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>
        <!-- Carousel End -->
    </section>

    <!-- Service Start -->
    <section id="services">
        <div class="container-xxl py-5">
            <div class="container">
                <div class="text-center mx-auto mb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 600px;">
                    <p class="d-inline-block bg-secondary text-primary py-1 px-4">Our Services</p>
                    <h1 class="text-uppercase">What We Offer</h1>
                </div>
                <div class="row g-4">
                    <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                        <div class="service-item position-relative overflow-hidden bg-secondary d-flex h-100 p-5 ps-0">
                            <div class="bg-dark d-flex flex-shrink-0 align-items-center justify-content-center"
                                style="width: 60px; height: 60px;">
                                <img class="img-fluid" src="img/haircut.png" alt="">
                            </div>
                            <div class="ps-4">
                                <h3 class="text-uppercase mb-3">Haircut</h3>
                                <p>Get a precise, stylish cut tailored to your look and personality.</p>
                                <span class="text-uppercase text-primary">From Rs: 250/=</span>
                            </div>
                            <a class="btn btn-square" data-bs-toggle="modal" data-bs-target="#bookingModal"><i
                                    class="fa fa-plus text-primary"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.3s">
                        <div class="service-item position-relative overflow-hidden bg-secondary d-flex h-100 p-5 ps-0">
                            <div class="bg-dark d-flex flex-shrink-0 align-items-center justify-content-center"
                                style="width: 60px; height: 60px;">
                                <img class="img-fluid" src="img/beard-trim.png" alt="">
                            </div>
                            <div class="ps-4">
                                <h3 class="text-uppercase mb-3">Beard Trim</h3>
                                <p>Shape and define your beard with expert precision.
                                </p>
                                <span class="text-uppercase text-primary">From rs: 150/=</span>
                            </div>
                            <a class="btn btn-square" data-bs-toggle="modal" data-bs-target="#bookingModal"><i
                                    class="fa fa-plus text-primary"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.5s">
                        <div class="service-item position-relative overflow-hidden bg-secondary d-flex h-100 p-5 ps-0">
                            <div class="bg-dark d-flex flex-shrink-0 align-items-center justify-content-center"
                                style="width: 60px; height: 60px;">
                                <img class="img-fluid" src="img/mans-shave.png" alt="">
                            </div>
                            <div class="ps-4">
                                <h3 class="text-uppercase mb-3">Hair Coloring</h3>
                                <p>Add depth, style, or a bold new look with professional hair coloring.</p>
                                <span class="text-uppercase text-primary">From rs: 500/=</span>
                            </div>
                            <a class="btn btn-square" data-bs-toggle="modal" data-bs-target="#bookingModal"><i
                                    class="fa fa-plus text-primary"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                        <div class="service-item position-relative overflow-hidden bg-secondary d-flex h-100 p-5 ps-0">
                            <div class="bg-dark d-flex flex-shrink-0 align-items-center justify-content-center"
                                style="width: 60px; height: 60px;">
                                <img class="img-fluid" src="img/hair-dyeing.png" alt="">
                            </div>
                            <div class="ps-4">
                                <h3 class="text-uppercase mb-3">Head Massage</h3>
                                <p>Relax and unwind with a rejuvenating head massage that relieves stress, improves
                                    blood circulation, and promotes healthy hair growth. </p>
                                <span class="text-uppercase text-primary">From $15</span>
                            </div>
                            <a class="btn btn-square" data-bs-toggle="modal" data-bs-target="#bookingModal"><i
                                    class="fa fa-plus text-primary"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.3s">
                        <div class="service-item position-relative overflow-hidden bg-secondary d-flex h-100 p-5 ps-0">
                            <div class="bg-dark d-flex flex-shrink-0 align-items-center justify-content-center"
                                style="width: 60px; height: 60px;">
                                <img class="img-fluid" src="img/mustache.png" alt="">
                            </div>
                            <div class="ps-4">
                                <h3 class="text-uppercase mb-3">Child Hair Care</h3>
                                <p>Stylists ensure a safe, comfortable experience with styles suited for kids of all
                                    ages.</p>
                                <span class="text-uppercase text-primary">From rs: 200/=</span>
                            </div>
                            <a class="btn btn-square" data-bs-toggle="modal" data-bs-target="#bookingModal"><i
                                    class="fa fa-plus text-primary"></i></a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
    <!-- Service End -->

    <section id="contact">
        <!-- Contact Us -->
        <div class="container-xxl py-5">
            <div class="container">
                <div class="text-center mx-auto mb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 600px;">
                    <p class="d-inline-block bg-secondary text-primary py-1 px-4">Contact Us</p>
                    <h1 class="text-uppercase">We're here to help, reach out anytime!</h1>
                </div>
                <!-- Contact Start -->
                <div class="container-xxl py-5">
                    <div class="container">
                        <div class="row g-0">
                            <div class="col-lg-6 wow fadeIn" data-wow-delay="0.1s">
                                <div class="bg-secondary p-5 rounded-4 shadow-lg text-light">
                                    <h1 class="text-uppercase mb-4 text-danger">SALON INOKA</h1>
                                    <p class="mb-4">
                                        <strong>Salon Inoka</strong> – Gent's Hair Salon, nestled in the heart of
                                        <strong>Dodawatta, Deraniyagala</strong>, Sri Lanka, is a beacon of style and
                                        tradition. For over two generations, we've been dedicated to crafting impeccable
                                        men's grooming experiences, blending classic techniques with modern trends.
                                        <br><br>
                                        Our heritage of excellence is now infused with fresh energy, catering to the new
                                        wave of youth seeking sophistication and style. Step into Salon Inoka and
                                        discover the epitome of timeless elegance and contemporary flair in men's
                                        hairstyling.
                                    </p>

                                    <div class="row g-3 pt-3 border-top border-light mt-4">
                                        <div class="col-md-6">
                                            <h5 class="text-light mb-2"><i class="bi bi-telephone-fill"></i> Contact
                                                Number</h5>
                                            <p class="mb-3"><a href="tel:+94711234567"
                                                    class="text-danger text-decoration-none">+94 71 123 4567</a></p>

                                            <h5 class="text-light mb-2"><i class="bi bi-envelope-fill"></i> Email</h5>
                                            <p class="mb-0"><a href="mailto:saloninokanoori@gmail.com"
                                                    class="text-danger text-decoration-none">saloninokanoori@gmail.com</a>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6 wow fadeIn" data-wow-delay="0.5s">
                                <div class="h-100" style="min-height: 400px;">
                                    <iframe class="google-map w-100 h-100"
                                        src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d393.8604958887691!2d80.4076954!3d6.9475932!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae3a1e9439e5ec9%3A0xa31557db991bac8c!2sInoka%20saloon!5e1!3m2!1sen!2slk!4v1748775882015!5m2!1sen!2slk"
                                        frameborder="0" allowfullscreen="" aria-hidden="false" tabindex="0"
                                        style="filter: grayscale(100%) invert(92%) contrast(83%); border: 0;"></iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Contact End -->
            </div>
        </div>
        <!-- Contact Us -->


        <!-- Working Hours Start -->

        <div class="container-xxl py-5">
            <div class="container">
                <div class="row g-0">
                    <div class="col-lg-6 wow fadeIn" data-wow-delay="0.1s">
                        <div class="h-100">
                            <img class="img-fluid h-100" src="img/open.jpg" alt="">
                        </div>
                    </div>
                    <div class="col-lg-6 wow fadeIn" data-wow-delay="0.5s">
                        <div class="bg-secondary h-100 d-flex flex-column justify-content-center p-5">
                            <p class="d-inline-flex bg-dark text-primary py-1 px-4 me-auto">Working Hours</p>
                            <h1 class="text-uppercase mb-4">We’re ready to make you look smart and sharp.</h1>
                            <div>
                                <div class="d-flex justify-content-between border-bottom py-2">
                                    <h6 class="text-uppercase mb-0">Monday</h6>
                                    <span class="text-uppercase">09 AM - 06 PM</span>
                                </div>
                                <div class="d-flex justify-content-between border-bottom py-2">
                                    <h6 class="text-uppercase mb-0">Tuesday</h6>
                                    <span class="text-uppercase">09 AM - 06 PM</span>
                                </div>
                                <div class="d-flex justify-content-between border-bottom py-2">
                                    <h6 class="text-uppercase mb-0">Wednesday</h6>
                                    <span class="text-uppercase">09 AM - 06 PM</span>
                                </div>
                                <div class="d-flex justify-content-between border-bottom py-2">
                                    <h6 class="text-uppercase mb-0">Thursday</h6>
                                    <span class="text-uppercase">09 AM - 06 PM</span>
                                </div>
                                <div class="d-flex justify-content-between border-bottom py-2">
                                    <h6 class="text-uppercase mb-0">Friday</h6>
                                    <span class="text-uppercase">09 AM - 06 PM</span>
                                </div>
                                <div class="d-flex justify-content-between border-bottom py-2">
                                    <h6 class="text-uppercase mb-0">Saturday</h6>
                                    <span class="text-uppercase">09 AM - 06 PM</span>
                                </div>
                                <div class="d-flex justify-content-between border-bottom py-2">
                                    <h6 class="text-uppercase mb-0">Sunday</h6>
                                    <span class="text-uppercase">09 AM - 06 PM</span>
                                </div>
                                <!-- <div class="d-flex justify-content-between py-2">
                                    <h6 class="text-uppercase mb-0">Now</h6>
                                    <span class="text-uppercase text-success">Open</span>
                                    <span class="text-uppercase text-primary">Closed</span>
                                </div> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Working Hours End -->

    <!-- Footer Start -->
    <div class="container-fluid bg-secondary text-light footer mt-5 pt-5 wow fadeIn" data-wow-delay="0.1s">
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-lg-4 col-md-6">
                    <h4 class="text-uppercase mb-4">Get In Touch</h4>
                    <div class="d-flex align-items-center mb-2">

                        <div class="btn-square bg-dark flex-shrink-0 me-3">
                            <span class="fa fa-map-marker-alt text-primary"></span>
                        </div>

                        <span class="">No.00 Dodawatta, Noori.</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <div class="btn-square bg-dark flex-shrink-0 me-3">
                            <span class="fa fa-phone-alt text-primary"></span>
                        </div>
                        <span>+94 71 123 4567</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="btn-square bg-dark flex-shrink-0 me-3">
                            <span class="fa fa-envelope-open text-primary"></span>
                        </div>
                        <span>saloninokanoori@gmail.com</span>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <h4 class="text-uppercase mb-4">Quick Links</h4>
                    <a class="btn btn-link" href="#home">Home</a>
                    <a class="btn btn-link" href="#services">Our Services</a>
                    <a class="btn btn-link" href="#contact">Contact Us</a>
                    <a class="btn btn-link" href="#appointment">Appointment</a>
                </div>
                <div class="col-lg-4 col-md-6">
                    <h4 class="text-uppercase mb-4">Follow Us On</h4>

                    <div class="d-flex pt-1 m-n1">
                        <a class="btn btn-lg-square btn-dark text-primary m-1" href="#"><i
                                class="fab fa-facebook-f"></i></a>
                        <a class="btn btn-lg-square btn-dark text-primary m-1" href="#"><i
                                class="fab fa-instagram"></i></a>
                        <a class="btn btn-lg-square btn-dark text-primary m-1" href="#"><i
                                class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="copyright">
                <div class="row ">
                    <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                        &copy; <span id="year"></span> <a class="" href="index.php">Salon Inoka</a>, All Rights
                        Reserved.
                    </div>

                    <div class="col-md-6 text-center text-md-end">
                        Designed By <a class="border-bottom"
                            href="https://navodniwarshana.github.io/portfolio/">NNProjrcts</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer End -->



    <!-- Back to Top -->
    <a href="#" class="btn btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>

    <!-- Appointment Start -->
    <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="">
        <div class="container modal-dialog modal-lg modal-dialog-centered">
            <div class="row g-0 justify-content-center modal-content bg-dark text-white border-0 rounded-4">
                <div class="col-12 wow fadeIn modal-header border-0" data-wow-delay="0.1s">

                    <div class="bg-secondary p-5 rounded-4 shadow">
                        <div class="d-flex justify-content-between">
                            <p class="d-inline-block bg-dark text-primary py-1 px-4">Book Appointment</p>

                            <button type="button" class="btn p-2 border-0" aria-label="Close" data-bs-toggle="modal"
                                data-bs-target="#bookingModal">
                                <i class="bi bi-x-circle fs-5"></i>
                            </button>
                        </div>

                        <h1 class="text-uppercase mb-4">Secure your spot with ease!</h1>
                        <p class="mb-4">Note: Bookings are subject to availability. Please arrive 5–10 minutes early to
                            ensure a smooth experience.</p>
                        <p class="text-light">
                            Your Appointment: <span class="text-danger"><?= $appointmentText ?></span>
                        </p>

                        <form action="index.php" method="POST">
                            <div class="row g-3">
                                <!-- Your Name -->
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="text" class="form-control bg-transparent" id="name"
                                            name="customerName" placeholder="Enter Your Name" required>
                                        <label for="name">Your Name</label>
                                    </div>
                                </div>

                                <!-- Service Dropdown -->
                                <div class="col-12">
                                    <div class="form-floating">
                                        <select class="form-select bg-transparent" id="service" name="service" required>
                                            <option value="">Select Service</option>
                                            <option>Haircut</option>
                                            <option>Beard Trim</option>
                                            <option>Hair Coloring</option>
                                            <option>Head Massage</option>
                                            <option>Child Hair Care</option>
                                        </select>
                                        <label for="service">Select Service</label>
                                    </div>
                                </div>

                                <!-- Date Picker -->
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="date" class="form-control bg-transparent" id="date" name="date"
                                            required>
                                        <label for="date">Select Date</label>
                                    </div>
                                </div>

                                <!-- Time Picker (booked slots disabled via JS) -->
                                <div class="col-12">
                                    <div class="form-floating">
                                        <select class="form-select bg-transparent text-light" id="time" name="time"
                                            required>
                                            <option value="">Select Time</option>
                                            <?php foreach ($allTimes as $time): ?>
                                                <option value="<?= $time ?>" <?= in_array($time, $bookedTimes) ? 'disabled' : '' ?>>
                                                    <?= $time ?>    <?= in_array($time, $bookedTimes) ? ' (Booked)' : '' ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <label for="time">Select Time</label>
                                    </div>
                                </div>


                                <!-- Submit Button -->
                                <div class="col-12">
                                    <button class="btn btn-primary w-100 py-3" type="submit">Book Appointment</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--  -->

    <!-- JavaScript to Populate and Disable Booked Times -->
    <script>
        const bookedTimes = ["10:00", "13:00", "15:30"]; // Booked slots (can be dynamic)
        const allTimes = [
            "09:00", "09:15", "09:30", "09:45",
            "10:00", "10:15", "10:30", "10:45",
            "11:00", "11:15", "11:30", "11:45",
            "12:00", "12:15", "12:30", "12:45",
            "13:00", "13:15", "13:30", "13:45",
            "14:00", "14:15", "14:30", "14:45",
            "15:00", "15:15", "15:30", "15:45",
            "16:00", "16:15", "16:30"
        ];

        const timeSelect = document.getElementById("time");

        function updateTimeOptions() {
            timeSelect.innerHTML = "";
            allTimes.forEach(time => {
                const option = document.createElement("option");
                option.value = time;
                option.textContent = time;
                if (bookedTimes.includes(time)) {
                    option.disabled = true;
                    option.textContent += " (Booked)";
                }
                timeSelect.appendChild(option);
            });
        }

        updateTimeOptions();
    </script>
    <script>
        document.querySelectorAll('a.nav-link[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    window.scrollTo({
                        top: target.offsetTop,
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>

    <script>
        document.getElementById("year").textContent = new Date().getFullYear();
    </script>

    <!--  -->

    <!-- ✅ JS: AJAX & Success Fade -->
    <script>
        document.getElementById('appointmentForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('appointmentSave.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.text())
                .then(response => {
                    if (response.trim() === 'success') {
                        // Show success alert
                        const alert = document.getElementById('success-alert');
                        alert.classList.remove('d-none');
                        alert.classList.add('show');

                        setTimeout(() => {
                            alert.classList.remove('show');
                            alert.classList.add('d-none');
                        }, 3000);

                        // Reset form
                        document.getElementById('appointmentForm').reset();
                    }
                });
        });
    </script>
    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
</body>

</html>