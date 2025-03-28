<?php
    ob_start();
    session_start();
    $db = mysqli_connect("localhost", "radius", "radpass", "radius");

    $msg = '';
    // Register
    if (isset($_POST['register']) && !empty($_POST['username']) && !empty($_POST['password'])) {
        $username = mysqli_real_escape_string($db, $_POST['username']);
        $password = mysqli_real_escape_string($db, $_POST['password']);
        // Find (user, password) in database
        $result = mysqli_query(
            $db,
            "SELECT * FROM radcheck WHERE username = '$username'"
        );
        // Check if user already exists
        if (!mysqli_num_rows($result)) {
            $result = mysqli_query(
                $db, 
                "INSERT INTO radcheck (username, attribute, value)
                VALUES ('$username', 'Cleartext-Password', '$password')"
            );
            if ($result) {
                $msg = "Registered.";
            } else {
                $msg = "Failed. Please retry.";
            }
        } else {
            $msg = 'Invalid username.';
        }
    } else if (isset($_POST['register']) && (empty($_POST['username']) || empty($_POST['password']))) {
        $msg = 'Empty username or password.';
    }
?>
<html lang = "en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="loginstyle.css">
    <title>Login</title>
</head>
<body>
    <h2 style="margin-left:10rem; margin-top:5rem;">HotSpot Login</h2> 
    <h4 style="margin-left:10rem; color:red;"><?php echo $msg; ?></h4>
    <br/><br/>
    <form action = "<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
        <div>
            <label for="username">Username:</label>
            <input type="text" name="username" id="name">
        </div>
        <div>
            <label for="password">Password:</label>
            <input type="password" name="password" id="password">
        </div>
        <section style="margin-left:2rem;">
            <button type="submit" name="register">Register</button>
        </section>
    </form>

    <p style="margin-left: 2rem;"> 
        <a href = "hotspotlogin.php" tite = "Logout">Log in</a> 
    </p>
    </div> 
</body>
</html>
