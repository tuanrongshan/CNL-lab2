<?php
    ob_start();
    session_start();
    $db = mysqli_connect("localhost", "radius", "changeme", "radius");

    $msg = '';
    // Modify limits
    if (isset($_POST['submit']) && !empty($_POST['username'])) {
        $username = mysqli_real_escape_string($db, $_POST['username']);
        $traffic_lim = isset($_POST['traffic_lim']) ? intval($_POST['traffic_lim']) : 0;
        $time_lim = isset($_POST['time_lim']) ? intval($_POST['time_lim']) : 0;
        // Find user in database
        $result = mysqli_query(
            $db,
            "SELECT * FROM radcheck WHERE username = '$username'"
        );
        // Check if user attr already exists
        if (mysqli_num_rows($result)) {
            $result1 = '';
            $result2 = '';
            if ($traffic_lim) {
                $exist = mysqli_query(
                    $db,
                    "SELECT * FROM radcheck WHERE username = '$username' AND attribute = 'Max-All-Traffic'"
                );
                if (!mysqli_num_rows($exist)) {
                    $result1 = mysqli_query(
                        $db,
                        "INSERT INTO radcheck (username, attribute, op, `value`)
                        VALUES ('$username', 'Max-All-Traffic', ':=', $traffic_lim)"
                    );
                } else {
                    $result1 = mysqli_query(
                        $db,
                        "UPDATE radcheck SET `value` = $traffic_lim 
                        WHERE attribute = 'Max-All-Traffic' AND username = '$username'"
                    );
                }
            }
            if ($time_lim) {
                $exist = mysqli_query(
                    $db,
                    "SELECT * FROM radcheck WHERE username = '$username' AND attribute = 'Max-All-Session'"
                );
                if (!mysqli_num_rows($exist)) {
                    $result2 = mysqli_query(
                        $db,
                        "INSERT INTO radcheck (username, attribute, op, `value`)
                        VALUES ('$username', 'Max-All-Session', ':=', $time_lim)"
                    );
                } else {
                    $result2 = mysqli_query(
                        $db, 
                        "UPDATE radcheck SET `value` = $time_lim
                        WHERE attribute = 'Max-All-Session' AND username = '$username'"
                    );
                }
            }
            if ($result1 && $result2) {
                $msg = "Modified.";
            } else {
                $msg = "Failed. Please retry.";
            }
        } else {
            $msg = 'Invalid username.';
        }
    } else if (isset($_POST['submit']) && empty($_POST['username'])) {
        $msg = 'Empty username.';
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
            <label for="traffic">Traffic Limit:</label>
            <input type="text" name="traffic_lim" id="traffic">
        </div>
        <div>
            <label for="time">Time Limit:</label>
            <input type="text" name="time_lim" id="time">
        </div>
        <section style="margin-left:2rem;">
            <button type="submit" name="submit">Submit</button>
        </section>
    </form>

    <p style="margin-left: 2rem;"> 
        
    <p style="margin-left: 2rem; display: inline-block;"> 
        <a href = "hotspotlogin.php?action=logout" tite = "Logout">Log out</a>
    </p>
    <p style="margin-left: 2rem; display: inline-block;"> 
    <a href = "hotspotlogin.php" tite = "Login">Log in</a> 
    </p>
    </div> 
</body>
</html>