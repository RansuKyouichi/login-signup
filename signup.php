<?php
// Include your database connection file or create a PDO connection here
include('connection.php');

// Initialize variables
$registrationErrors = [];

// Check if the form is submitted
if (isset($_POST['submit'])) {
    // Include the process.php file to handle form processing
    include('actions.php');
}

// Check if the registration success session variable is set
if (isset($_SESSION['registration_success']) && $_SESSION['registration_success']) {
    $registrationSuccessMessage = $_SESSION['registration_success'];
    // Unset the session variable to avoid displaying the message again on page reload
    unset($_SESSION['registration_success']);
}
?>

<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- ================ ICO IMAGE ================ -->
    <link rel="icon" type="image/x-icon" href="./assets/images/white_logo.ico" />

    <!-- ============== TITLE OF THE PAGE ================= -->
    <title>Registration</title>

    <!--=============== BOXICONS ===============-->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />

    <!-- ============== FONT AWESOME ============== -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <!-- ================== Bootstrap Link ===================== -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">

    <!-- CSS LINK -->
    <link rel="stylesheet" href="registration.css">


</head>

<body class="bg-light">
    <!--==================== HEADER ====================-->
    <header class="header" id="header">

    </header>
    <!--==================== END OF HEADER ====================-->

    <!-- ================== REGISTRATION FORM =================== -->
    <main>
        <section class="mt-5">
            <div class="container-sm">
                <div class="row justify-content-center">
                    <div class="col-sm-12 col-md-8 col-lg-6">

                        <form action="" method="post" id="form" autocomplete="on" class="bg-white p-4 rounded-lg">
                            <h1 class="mb-4 text-center registration_font">Sign up</h1>

                            <div class="form-group input-control pt-2">
                                <label for="name">Username</label>
                                <input id="name" name="name" type="text" class="form-control">
                                <div class="error_message error" id="fullname-error"></div>
                            </div>

                            <div class="form-group input-control pt-2">
                                <label for="email">Email</label>
                                <input id="email" name="email" type="email" class="form-control">
                                <div class="error_message error" id="email-error"></div>
                            </div>

                            <div class="form-group input-control pt-2">
                                <label for="password">Password</label>
                                <div class="input-group">
                                    <input id="password" name="password" type="password" class="form-control">
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="error_message error" id="password-error"></div>
                            </div>

                            <div class="form-group input-control pt-2">
                                <label for="repeat_password">Repeat Password</label>
                                <div class="input-group">
                                    <input id="repeat_password" name="repeat_password" type="password" class="form-control">
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="fas fa-eye toggle-repeat_password" id="toggleRepeatPassword"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="error_message error" id="repeat_password-error"></div>
                            </div>

                            <button type="submit" name="submit" class="btn btn-primary btn-block">Sign up</button>

                            <p class="text-center mt-3">
                                Already have an account? <a href="login.php">Login</a>
                            </p>

                            <!-- Bootstrap Modal for Success Message -->
                            <div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="successModalLabel">Registration Successful!</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <?= $registrationSuccessMessage; ?>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

        </section>
    </main>
    <!-- ================== END OF REGISTRATION FORM =================== -->

    <!-- ====================== SCRIPTS ========================= -->
    <!-- Jquery Script -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>

    <!-- Bootstrap Script -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>

    <script>
        // for showing and hiding the password
        $(document).ready(function() {
            $('#togglePassword').on('click', function() {
                const passwordField = $('#password');
                const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
                passwordField.attr('type', type);
                $(this).toggleClass('fa-eye fa-eye-slash');
            });
        });

        // for showing and hiding the repeat_password
        $('#toggleRepeatPassword').on('click', function() {
            const repeatPasswordField = $('#repeat_password');
            const type = repeatPasswordField.attr('type') === 'password' ? 'text' : 'password';
            repeatPasswordField.attr('type', type);
            $(this).toggleClass('fa-eye fa-eye-slash');
        });

        // for showing error messages
        $(document).ready(function() {
            // Hide error messages on page load
            $(".error_message").hide();

            $("#form").submit(function(e) {
                e.preventDefault();

                $.ajax({
                    type: "POST",
                    url: "actions.php",
                    data: $("#form").serialize(),
                    dataType: "json",
                    success: function(response) {
                        console.log(response); // Add this line to log the response
                        $(".error_message").empty().hide(); // Update to target the correct elements and hide them

                        if (response.success) {
                            // Display the success message using Bootstrap modal
                            $("#successModal .modal-body").html(response.success);
                            $("#successModal").modal('show');
                        } else {
                            // Display errors for each field that failed validation
                            $.each(response, function(key, value) {
                                $("#" + key + "-error").html(value).show(); // Update to target the correct elements and show them
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText); // Add this line to log the error
                        // Handle AJAX error
                        alert("Error processing the registration.");
                    },
                });
            });

            // Handle redirection after modal is closed
            $('#successModal').on('hidden.bs.modal', function() {
                window.location.href = 'login.php';
            });
        });
    </script>
    <!-- ====================== END OF SCRIPTS ========================= -->

</body>

</html>