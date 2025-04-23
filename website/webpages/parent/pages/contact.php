<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
    <!-- Include the header component -->
    <?php include_once '../components/header.php'; ?>
    <!--profile Style-->
    <link rel="stylesheet" href="contect.css" />
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        <!-- Include the bars component -->
        <?php include_once '../components/bars.php'; ?>

        <div class="contact-section content-wrapper" style="margin-top: 50px;">
            <h2 style="text-align: center;"> Get in Touch</h2>
            <div class="contact-cards">
                <div class="card teacher">
                    <img src="https://img.icons8.com/ios/50/teacher.png" alt="Teacher Icon" class="ss">
                    <h3> Teacher</h3>
                    <table>
                        <tr>
                            <td><strong>📞</strong></td>
                            <td><a href="tel:0799999999">0799999999</a></td>
                        </tr>
                        <tr>
                            <td><strong>📧</strong></td>
                            <td><a href="mailto:Email@domain.com">Email@domain.com</a></td>
                        </tr>
                    </table>
                </div>

                <div class="card school">
                    <img src="https://img.icons8.com/ios/50/school.png" alt="School Icon" class="ss">
                    <h3>School</h3>
                    <table>
                        <tr>
                            <td><strong>📞</strong></td>
                            <td><a href="tel:0799999999">0799999999</a></td>
                        </tr>
                        <tr>
                            <td><strong>📧</strong></td>
                            <td><a href="mailto:Email@domain.com">Email@domain.com</a></td>
                        </tr>
                        <tr>
                            <td><strong>🌐</strong></td>
                            <td><a href="https://www.madrasati.com">www.madrasati.com</a></td>
                        </tr>
                        <tr>
                            <td><strong>📍</strong></td>
                            <td><a href="#">School Location </a></td>
                        </tr>
                    </table>
                </div>

                <div class="card admin">
                    <img src="https://img.icons8.com/ios/50/administrator-male.png" alt="Admin Icon" class="ss">
                    <h3>Admin</h3>
                    <table>
                        <tr>
                            <td><strong>📞</strong></td>
                            <td><a href="tel:0799999999">0799999999</a></td>
                        </tr>
                        <tr>
                            <td><strong>📧</strong></td>
                            <td><a href="mailto:Email@domain.com">Email@domain.com</a></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <!-- /.content-wrapper -->

        <!-- Include the footer component -->
        <?php include_once '../components/footer.php'; ?>
    </div>
    <!-- ./wrapper -->

    <!-- // Include the scripts component -->
    <?php include_once '../components/scripts.php'; ?>
    <!-- // Include the charts data component -->
    <?php include_once '../components/chartsData.php'; ?>
</body>

</html>