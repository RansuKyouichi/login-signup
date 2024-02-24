<?php

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

    // Create a class for Registration of a user and its functions
    class Registration
    {
        private $pdo;

        public function __construct(PDO $pdo)
        {
            $this->pdo = $pdo;
        }

        // Checks if the full name is valid
        private function validateUserName($name)
        {
            $registrationErrors = [];

            if (empty($name)) {
                $registrationErrors['name'] = "Username is required.";
            } else {
                if (strlen($name) > 50) {
                    $registrationErrors['name'] = "Username is too long.";
                }

                // Check for a dictionary of common words and reject if the name is found
                $commonWords = ['test', 'example', 'person', 'random', 'anonymous']; // Add more words
                $nameWords = array_map('strtolower', preg_split("/[\s-]+/", strtolower($name)));

                foreach ($nameWords as $word) {
                    if (in_array($word, $commonWords)) {
                        $registrationErrors['name'] = "Please provide a valid username.";
                        break;
                    }
                }

                // Contextual validation: Example checks for common name structure (adjust to your specific needs)
                $nameParts = explode(' ', $name);
                if (count($nameParts) < 1) {
                    $registrationErrors['name'] = "Please provide a username.";
                }
            }

            return $registrationErrors;
        }

        private function userNameExists($name)
        {
            $query = "SELECT COUNT(*) as count FROM user_db WHERE userName = ?";
            $statement = $this->pdo->prepare($query);
            $statement->execute([$name]);

            $result = $statement->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0; // If count > 0, full name exists
        }

        // Check if the email exists in the database
        private function emailExists($email)
        {
            $query = "SELECT COUNT(*) as count FROM user_db WHERE email = ?";
            $statement = $this->pdo->prepare($query);
            $statement->execute([$email]);

            $result = $statement->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0; // If count > 0, email exists
        }

        private function validatePassword($password, $repeatPassword)
        {
            $registrationErrors = [];

            if (empty($password)) {
                $registrationErrors['password'] = "Password is required.";
            } else {
                // Check password length
                if (strlen($password) < 8) {
                    $registrationErrors['password'] = "Password must be at least 8 characters long.";
                }

                // / Check for combination of uppercase, lowercase letters, and numbers
                if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
                    $registrationErrors['password'] = "Password should contain at least one uppercase letter, one lowercase letter, and one number.";
                }
            }

            return $registrationErrors;
        }

        private function validateRepeatPassword($password, $repeatPassword)
        {
            $registrationErrors = [];

            if ($password !== $repeatPassword) {
                $registrationErrors['repeat_password'] = "Passwords do not match.";
            }

            return $registrationErrors;
        }

        // Register user using the variables that were used in checking if there is a form submitted
        public function registerUser($name, $email, $password, $repeatPassword)
        {
            $registrationErrors = [];

            // Validate full name
            $userNameErrors = $this->validateUserName($name);
            if (!empty($userNameErrors)) {
                return array_merge($registrationErrors, $userNameErrors);
            }

            // Check if the fullname already exists
            if ($this->userNameExists($name)) {
                return ["username" => "This username is already registered."];
            }

            // Check if the email already exists
            if ($this->emailExists($email)) {
                return ["email" => "This email is already registered."];
            }

            // Check if the password is valid
            $passwordErrors = $this->validatePassword($password, $repeatPassword);
            if (!empty($passwordErrors)) {
                return $passwordErrors;
            }

            // Check if the repeat password is valid
            $passwordRepeatErrors = $this->validateRepeatPassword($password, $repeatPassword);
            if (!empty($passwordRepeatErrors)) {
                return $passwordRepeatErrors;
            }

            try {
                // Hash the password before storing it in the database
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Insert user data into the database
                // prepared statements in pdo
                $stmt = $this->pdo->prepare("INSERT INTO user_db (userName, email, password) VALUES (?, ?, ?)");
                $success = $stmt->execute([$name, $email, $hashedPassword]);

                if ($success) {
                    // Set session variable for success
                    $_SESSION['registration_success'] = true;
                    // Return success for AJAX response
                    return ["success" => "Registration successful! You can now log in."];
                } else {
                    // Display database error
                    return ["database" => "Failed to insert data into the database."];
                }
            } catch (PDOException $e) {
                // Display database error
                return ["database" => "Database error: " . $e->getMessage()];
            }
        }
    }

    // Server for registering a user. 
    // Check if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Create an instance of the UserRegistration class
        $userRegistration = new Registration($pdo);

        // Get form data
        $name = isset($_POST['name']) ? $_POST['name'] : '';
        $email = isset($_POST['email']) ? $_POST['email'] : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $repeatPassword = isset($_POST['repeat_password']) ? $_POST['repeat_password'] : '';

        // Register the user
        $registrationErrors = $userRegistration->registerUser($name, $email, $password, $repeatPassword);

        // For success message
        header('Content-Type: application/json');
        echo json_encode($registrationErrors);
        exit();
    } else {
        // Handle invalid request method
        header('HTTP/1.1 400 Bad Request');
        exit('Invalid request method');
    }
} catch (Exception $e) {
    // Handle exceptions or errors here
    echo "Error: " . $e->getMessage();
}