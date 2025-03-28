<?php
    ob_start();
    session_start();
    $db = mysqli_connect("localhost", "radius", "changeme", "radius");

    $msg = '';
    // Check user login
    if (isset($_POST['login']) && !empty($_POST['username']) && !empty($_POST['password'])) {
        $username = mysqli_real_escape_string($db, $_POST['username']);
        $password = mysqli_real_escape_string($db, $_POST['password']);
        // Find (user, password) in database
        $result = mysqli_query(
            $db,
            "SELECT * FROM radcheck WHERE username = '$username' AND value = '$password'"
        );
        if (mysqli_num_rows($result) == 1) {
            $_SESSION['username'] = $username;
            $result = mysqli_query(
                $db, 
                "SELECT 
                    SUM(acctsessiontime) AS total_time, 
                    SUM(acctinputoctets + acctoutputoctets) AS total_traffic
                FROM radacct WHERE username = '$username'"
            );
            $row = mysqli_fetch_assoc($result);    
            $msg = "Logged in. Current traffic usage: " . ($row['total_traffic']) . " bytes, time usage: " . ($row['total_time']) . " sec";
        } else {
            $msg = 'Failed. Wrong username or password.';
        }
    }

    if (isset($_GET['action']) && $_GET['action'] == 'logout') {
        if (isset($_SESSION["username"])) {
            unset($_SESSION["username"]);
        }
        $msg = 'Logged out.';
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
            <button type="submit" name="login">Login</button>
        </section>
    </form>

    <p style="margin-left: 2rem; display: inline-block;"> 
        <a href = "?action=logout" tite = "Logout">Log out</a>
    </p>
    <p style="margin-left: 2rem; display: inline-block;"> 
        <a href = "register.php" tite = "Register">Register</a>
    </p>
    <p style="margin-left: 2rem; display: inline-block;"> 
        <a href = "admin.php" tite = "Admin">Admin</a>
    </p>
    </div> 
</body>
</html>
