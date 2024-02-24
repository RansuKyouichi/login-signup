<?php
    // Include your database connection file or create a PDO connection here
    require_once('connection.php');

    // Check if the session started, if not start it.
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    try {
        // Create an instance of the UserDatabaseConnection class
        $userDatabase = new Connection('localhost', 'tamaweap', 'root', ''); // Replace with your credentials

        // Get the PDO object from the connection
        $pdo = $userDatabase->getPDO();
        class LoggingUser
        {
            private $pdo;

            public function __construct(PDO $pdo)
            {
                $this->pdo = $pdo;
            }

            private function validateEmail($email)
            {
                $loginErrors = [];

                if (empty($email)) {
                    $loginErrors['email'] = "Email is required.";
                }

                return $loginErrors;
            }

            public function emailExists($email)
            {
                $query = "SELECT COUNT(*) as count FROM user_db WHERE email = ?";
                $statement = $this->pdo->prepare($query);
                $statement->execute([$email]);

                $result = $statement->fetch(PDO::FETCH_ASSOC);
                return $result['count'] > 0; // If count > 0, email exists
            }

            private function validatePassword($password)
            {
                $loginErrors = [];

                if (empty($password)) {
                    $loginErrors['password'] = "Password is required.";
                }

                return $loginErrors;
            }

            public function loginUser($email, $password)
            {
                $loginErrors = [];

                // Validate email
                $emailErrors = $this->validateEmail($email);
                if (!empty($emailErrors)) {
                    return $emailErrors; // Return email validation errors
                }

                // Validate password
                $passwordErrors = $this->validatePassword($password);
                if (!empty($passwordErrors)) {
                    return $passwordErrors; // Return email validation errors
                }

                $query = "SELECT * FROM user_db WHERE email = ?";
                $statement = $this->pdo->prepare($query);
                $statement->execute([$email]);
                $user = $statement->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    if (password_verify($password, $user['password'])) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['email'] = $user['email'];

                        header('Location: ../Main/index.php');
                        exit();

                    } else {
                        $loginErrors['password'] = "Incorrect password.";
                    }
                } else {
                    $loginErrors['email'] = "Email not found.";
                }

                return $loginErrors;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = isset($_POST['email']) ? $_POST['email'] : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';

            // Assuming $pdo is already initialized in this file or included from another script
            $loggingUser = new LoggingUser($pdo);

            $loginErrors = $loggingUser->loginUser($email, $password);

            // For success message or error handling
            header('Content-Type: application/json');
            echo json_encode($loginErrors);
            exit();
        } else {
            header('HTTP/1.1 400 Bad Request');
            exit('Invalid request method');
        }
    } catch (Exception $e) {
        // Handle exceptions or errors here
        echo "Error: " . $e->getMessage();
    }