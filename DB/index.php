<?php
session_start();
require '../conn.php';

try {
    $stmt = $pdo->prepare("
    SELECT COUNT(*) AS new_appointments
    FROM appointment
    WHERE status = 'New'");

    $stmt->execute();
    $row = $stmt->fetch();
    $count = $row['new_appointments'];


} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
//---------

try {
    $stmt = $pdo->query("SELECT * FROM appointment ORDER BY appointmentDate DESC, appointmentTime ASC");
    $appointments = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
//-------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['status'])) {
    $id = intval($_POST['id']);
    $status = $_POST['status'];

    $stmt = $pdo->prepare("UPDATE appointment SET status = :status WHERE id = :id");
    $stmt->execute([':status' => $status, ':id' => $id]);

    if (!empty($_SERVER['HTTP_REFERER'])) {
        header("Location: " . $_SERVER['HTTP_REFERER']);
    } else {
        header("Location: index.php"); // fallback
    }
    exit;
}

//---------------


$weekCounts = array_fill(0, 7, 0); // Default: 0 for each day

$stmt = $pdo->prepare("
    SELECT 
        DAYOFWEEK(appointmentDate) AS day,
        COUNT(*) AS total
    FROM appointment
    WHERE WEEK(appointmentDate) = WEEK(CURDATE()) AND YEAR(appointmentDate) = YEAR(CURDATE())
    GROUP BY DAYOFWEEK(appointmentDate)
");
$stmt->execute();
$rows = $stmt->fetchAll();

foreach ($rows as $row) {
    $dayIndex = ($row['day'] + 5) % 7; // Convert MySQL Sunday=1 to Monday=0
    $weekCounts[$dayIndex] = $row['total'];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Salon Inoka Dashboard</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Roboto:wght@500;700&display=swap"
        rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
</head>

<body>
    <!-- Spinner Start -->
    <div id="spinner"
        class="show bg-dark position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!-- Spinner End -->

    <!-- Content Start -->

    <!-- Navbar Start -->
    <nav
        class="navbar navbar-expand bg-secondary navbar-dark sticky-top px-4 py-0 d-flex justify-content-between w-100">
        <div><a href="index.html" class="navbar-brand ms-4 ms-lg-0">
                <img src="../img/logo.png" class="p-2 " alt="">
            </a></div>
        <div> <!-- Log Out button aligned to the left -->
            <a class="btn btn-primary rounded-0 py-1 px-lg-4 me-auto" data-bs-toggle="modal"
                data-bs-target="#bookingModal">
                Log Out
            </a>
        </div>
    </nav>
    <!-- Navbar End -->

    <!-- Sale & Revenue Start -->
    <div class="container-fluid pt-4 px-4">
        <div class="row g-4">

            <div class="col-sm-6 col-xl-3">
                <div class="bg-secondary rounded d-flex align-items-center justify-content-between p-4">
                    <i class="fa fa-chart-bar fa-3x text-primary"></i>
                    <div class="ms-3">
                        <p class="mb-2">New Appointments</p>
                        <h6 class="mb-0"><?= $count ?></h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Sale & Revenue End -->

    <!-- Recent Sales Start -->
    <div class="container-fluid pt-4 px-4">
        <div class="bg-secondary text-center rounded p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h6 class="mb-0">New Appointments</h6>
            </div>
            <div class="table-responsive">
                <table class="table text-start align-middle table-bordered table-hover mb-0">
                    <thead>
                        <tr class="text-white">
                            <th scope="col">Date</th>
                            <th scope="col">Time</th>
                            <th scope="col">Customer</th>
                            <th scope="col">Service</th>
                            <th scope="col">Status</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <?php if ($appointments): ?>
                                <?php foreach ($appointments as $row): ?>
                                <tr>

                                    <td><?= date('M d', strtotime($row['appointmentDate'])) ?></td>

                                    <td><?= date('h:i A', strtotime($row['appointmentTime'])) ?></td>
                                    <td><?= $row['customerName'] ?></td>
                                    <td><?= $row['service'] ?></td>
                                    <td>
                                        <?php
                                        $status = $row['status'];
                                        $badgeClass = match ($status) {
                                            'New' => 'text-success',
                                            'Completed' => 'text-danger',
                                            default => 'text-warning'
                                        };
                                        ?>
                                        <span class="<?= $badgeClass ?> fw-bold"><?= $status ?></span>
                                    </td>
                                    <td>
                                        <form method="POST" action="index.php" class="d-flex">
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <select name="status" class="form-select form-select-sm me-1"
                                                onchange="this.form.submit()">
                                                <option value="New" <?= $row['status'] === 'New' ? 'selected' : '' ?>>New</option>
                                                <option value="Checked" <?= $row['status'] === 'Checked' ? 'selected' : '' ?>>
                                                    Checked</option>
                                                <option value="Completed" <?= $row['status'] === 'Completed' ? 'selected' : '' ?>>
                                                    Completed</option>
                                            </select>
                                        </form>
                                    </td>

                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">No appointments found.</td>
                            </tr>
                        <?php endif; ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Recent Sales End -->


    <!-- Sales Chart Start -->
    <div class="container-fluid pt-4 px-4">
        <div class="row g-4">
            <div class="col-12">
                <div class="bg-secondary text-center rounded p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h6 class="mb-0">Daily Appointments</h6>
                    </div>
                    <canvas id="worldwide-sales"></canvas>
                </div>
            </div>

        </div>
    </div>
    <!-- Sales Chart End -->
    <!-- Content End -->


    <!-- Back to Top -->
    <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/chart/chart.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
</body>

</html>