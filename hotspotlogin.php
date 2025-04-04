<?php
# File: hotspotlogin.php
# working with chillispot_0.97
# last change 2004-10-01
# this is forked from original chillispot.org's hotspotlogin.cgi by Kanne
# uamsecret enabled by Cedric
# logoff when closing logoff window added by Lorenzo Allori <lallori_A.T_medici.org>
# Shared secret used to encrypt challenge with. Prevents dictionary attacks.
# You should change this to your own shared secret.

$uamsecret = "uamsecret";

# Uncomment the following line if you want to use ordinary user-password
# for radius authentication. Must be used together with $uamsecret.

$userpassword=1;

$loginpath = "/hotspotlogin.php";
$db = mysqli_connect("localhost", "radius", "changeme", "radius");

exec("php cleanup.php > /dev/null 2>&1 &");

# possible Cases:       
# attempt to login                          login=login
# 1: Login successful                       res=success
# 2: Login failed                           res=failed
# 3: Logged out                             res=logoff
# 4: Tried to login while already logged in res=already
# 5: Not logged in yet                      res=notyet
#11: Popup                                  res=popup1
#12: Popup                                  res=popup2
#13: Popup                                  res=popup3
# 0: It was not a form request              res=""
#Read query parameters which we care about
# $_GET['res'];
# $_GET['challenge'];
# $_GET['uamip'];
# $_GET['uamport'];
# $_GET['reply'];
# $_GET['userurl'];
# $_GET['timeleft'];
# $_GET['redirurl'];
#Read form parameters which we care about
# $_GET['username'];
# $_GET['password'];
# $_GET['chal'];
# $_GET['login'];
# $_GET['logout'];
# $_GET['prelogin'];
# $_GET['res'];
# $_GET['uamip'];
# $_GET['uamport'];
# $_GET['userurl'];
# $_GET['timeleft'];
# $_GET['redirurl'];

$titel = '';
$headline = '';
$bodytext = '';
$body_onload = '';
$footer_text = '<center>
                  <a href="http://freenet.surething.biz/catalog2/index.php">[HELP]</a> 
                  <a href="http://freenet.surething.biz/catalog2/product_info.php?products_id=34">[terms and conditions]</a>
                  <a href="?action=register">[Register]</a>
                  <a href="?action=admin">[Admin]</a>
                </center>';
         
$footer_textz  = '';                 
# attempt to login
if (isset($_GET['login']) && $_GET['login'] == 'login') {
  session_start();
  $_SESSION['login_user'] = $_GET['UserName'];
  $hexchal = pack ("H32", $_GET['chal']);
  if (isset ($uamsecret)) {
    $newchal = pack ("H*", md5($hexchal . $uamsecret));
  } else {
    $newchal = $hexchal;
  }
  $response = md5("\0" . $_GET['Password'] . $newchal);
  $newpwd = pack("a32", $_GET['Password']);
  $pappassword = implode ("", unpack("H32", ($newpwd ^ $newchal)));

  $titel = 'Logging in to HotSpot'; 
  $headline = 'Logging in to HotSpot';
  $bodytext = ''; 
  print_header();
  if ((isset ($uamsecret)) && isset($userpassword)) {
    print '<meta http-equiv="refresh" content="0;url=http://' . $_GET['uamip'] . ':' . $_GET['uamport'] . '/logon?username=' . $_GET['UserName'] . '&password=' . $pappassword . '">';
  } else {
    print '<meta http-equiv="refresh" content="0;url=http://' . $_GET['uamip'] . ':' . $_GET['uamport'] . '/logon?username=' . $_GET['UserName'] . '&response=' . $response . '&userurl=' . $_GET['userurl'] . '">';
  }
   print_body();
   print_footer();
}
# 15: Register
if (isset($_POST['register']) && $_POST['register'] == 'register') {
  session_start();
  $result = 15;
  $titel = 'Register failed';
  $headline = 'Register failed';
  if (!empty($_POST['UserName']) && !empty($_POST['Password'])) {
    $username = mysqli_real_escape_string($db, $_POST['UserName']);
    $password = mysqli_real_escape_string($db, $_POST['Password']);
    // Find (user, password) in database
    $sql = mysqli_query(
      $db,
      "SELECT * FROM radcheck WHERE username = '$username'"
    );
    // Check if user already exists
    if (!mysqli_num_rows($sql)) {
      $sql1 = mysqli_query(
        $db, 
        "INSERT INTO radcheck (username, attribute, op, `value`)
        VALUES ('$username', 'Cleartext-Password', ':=', '$password')"
      );
      $sql2 = mysqli_query(
        $db, 
        "INSERT INTO radusergroup (username, groupname)
        VALUES ('$username', 'user')"
      );
      if ($sql1 && $sql2) {
        $titel = "Registered";
        $headline = 'Registered for HotSpot successfully';
        $bodytext = 'Please reopen the window and login through ChilliSpot daemon<br>';
      } else {
        $bodytext = "please retry<br>";
      }
    } else {
      $bodytext = "username already exists<br>";
    }
  } else {
    $bodytext = "username and password cannot be empty<br>";
  }
  print_header();
  print_body();
  print_footer();
}
# 17: Modify
if (isset($_POST['modify']) && $_POST['modify'] == 'modify') {
  session_start();
  $result = 17;
  $titel = 'Set limits failed';
  $headline = 'Set limits failed';
  if (!empty($_POST['UserName'])) {
    $username = mysqli_real_escape_string($db, $_POST['UserName']);
    $traffic_lim = isset($_POST['TrafficLim']) ? intval($_POST['TrafficLim']) : 0;
    $time_lim = isset($_POST['TimeLim']) ? intval($_POST['TimeLim']) : 0;
    // Find user in database
    $sql = mysqli_query(
      $db,
      "SELECT * FROM radcheck WHERE username = '$username'"
    );
    // Check if user attr already exists
    if (mysqli_num_rows($sql)) {
      $sql1 = '';
      $sql2 = '';
      if ($traffic_lim) {
        $exist = mysqli_query(
          $db,
          "SELECT * FROM radcheck WHERE username = '$username' AND attribute = 'Max-All-Traffic'"
        );
        if (!mysqli_num_rows($exist)) {
          $sql1 = mysqli_query(
            $db,
            "INSERT INTO radcheck (username, attribute, op, `value`)
            VALUES ('$username', 'Max-All-Traffic', ':=', $traffic_lim)"
          );
        } else {
          $sql1 = mysqli_query(
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
          $sql2 = mysqli_query(
            $db,
            "INSERT INTO radcheck (username, attribute, op, `value`)
            VALUES ('$username', 'Max-All-Session', ':=', $time_lim)"
          );
        } else {
          $sql2 = mysqli_query(
            $db, 
            "UPDATE radcheck SET `value` = $time_lim
            WHERE attribute = 'Max-All-Session' AND username = '$username'"
          );
        }
      }
      if ($sql1 && $sql2) {
        $titel = "Modified";
        $headline = 'Modified successfully';
        $bodytext = 'Please reopen the window and login through ChilliSpot daemon<br>';
      } else {
        $bodytext = "please retry<br>";
      }
    } else {
      $bodytext = "username not exists<br>";
    }
  } else {
    $bodytext = "username cannot be empty<br>";
  }
  print_header();
  print_body();
  print_footer();
}
# 14: Register page
if (isset($_GET['action']) && $_GET['action'] == 'register') {
  session_start();
  $result = 14;
  $titel = 'Register for HotSpot';
  $headline = 'Register for HotSpot';
  $bodytext = 'please register<br>';
  print_header();
  print_body();
  print_register_form();
  print_footer();
}
# 16: Admin page
if (isset($_GET['action']) && $_GET['action'] == 'admin') {
  session_start();
  $result = 16;
  $titel = 'HotSpot Admin';
  $headline = 'Admin: Usage limits for HotSpot';
  $bodytext = 'please set limits<br>';
  print_header();
  print_body();
  print_modify_form();
  print_footer();
}
# 1: Login successful
if ($_GET['res'] == 'success') {
  session_start();
  $result = 1;
  $titel = 'Logged in to HotSpot';
  $headline = 'Logged in to HotSpot';

  $username = $_SESSION['login_user'];
  $sql = mysqli_query(
      $db, 
      "SELECT 
          SUM(acctsessiontime) AS total_time, 
          SUM(acctinputoctets + acctoutputoctets) AS total_traffic
      FROM radacct WHERE username = '$username'"
  );
  $row = mysqli_fetch_assoc($sql);    
  $msg = "Current traffic usage: " . ($row['total_traffic']) . " bytes. Time usage: " . ($row['total_time']) . " sec";

  $bodytext = 'Welcome<br><br>' . $msg;
  $body_onload = 'onLoad="javascript:popUp(' . $loginpath . '?res=popup&uamip=' . $_GET['uamip'] . '&uamport=' . $_GET['uamport'] . '&timeleft='  . $_GET['timeleft'] . ')"';
  print_header();
  print_body();
  if ($reply) { 
    print '<center>' . $reply . '</BR></BR></center>';
  }
  print '<center><a href="http://' . $_GET['uamip'] . ':' . $_GET['uamport'] . '/logoff">Logout</a></center>';
  print_footer();
}
# 2: Login failed
if ($_GET['res'] == 'failed') {
  $result = 2;
  $titel = 'HotSpot Login Failed';
  $headline = 'HotSpot Login Failed';
  $bodytext = 'Sorry, try again<br>';
  print_header();
  print_body();
  if ($_GET['reply']) {
    print '<center>' . $_GET['reply'] . '</center>';
  }
  print_login_form();
  print_footer();
}
# 3: Logged out
if ($_GET['res'] == 'logoff') {
  $result = 3;
  $titel = 'Logged out from HotSpot';
  $headline = 'Logged out from HotSpot';
  $bodytext = '<a href="http://' . $_GET['uamip'] . ':' . $_GET['uamport'] . '/prelogin">Login</a>';
  print_header();
  print_body();
  print_footer();
}
# 4: Tried to login while already logged in
if ($_GET['res'] == 'already') {
  $result = 4;
  $titel = 'Already logged in to HotSpot';
  $headline = 'Already logged in to HotSpot';
  $bodytext = '<a href="http://' . $_GET['uamip'] . ':' . $_GET['uamport'] . '/logoff">Logout</a>';
  print_header();
  print_body();
  print_footer();
}
# 5: Not logged in yet
if ($_GET['res'] == 'notyet') {
  $result = 5;
  $titel = 'Logged out from HotSpot';
  $headline = 'Logged out from HotSpot';
  $bodytext = 'please log in<br>';
  print_header();
  print_body();
  print_login_form();
  print_footer();
}
#11: Popup1
if ($_GET['res'] == 'popup1') {
  $result = 11;
  $titel = 'Logging into HotSpot';
  $headline = 'Logged in to HotSpot';
  $bodytext = 'please wait...';
  print_header();
  print_body();
  print_footer();
}
#12: Popup2
if ($_GET['res'] == 'popup2') {
  session_start();
  $result = 12;
  $titel = 'Do not close this Window!';
  $headline = 'Logged in to HotSpot';
  $bodytext = '<a href="http://' . $_GET['uamip'] . ':' . $_GET['uamport'] . '/logoff">Logout</a>';
  print_header();
  print_bodyz();
  print_footer();
}
#13: Popup3
if ($_GET['res'] == 'popup3') {
  $result = 13;
  $titel = 'Logged out from HotSpot';
  $headline = 'Logged out from HotSpot';
  $bodytext = '<a href="http://' . $_GET['uamip'] . ':' . $_GET['uamport'] . '/prelogin">Login</a>';
  print_header();
  print_body();
  print_footer();
}
# 0: It was not a form request
# Send out an error message
if ($_GET['res'] == "") {
  $result = 0;
  $titel = 'What do you want here?';
  $headline = 'HotSpot Login Failed';
  $bodytext = 'Login must be performed through ChilliSpot daemon!';
  print_header();
  print_body();
  print_footer();
}
# functions
function print_header(){
  global $titel, $loginpath;
  $uamip = $_GET['uamip'];
  $uamport = $_GET['uamport'];
  print "
  <html>
    <head>
      <title>$titel</title>
        <meta http-equiv=\"Cache-control\" content=\"no-cache\">
        <meta http-equiv=\"Pragma\" content=\"no-cache\">
        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=ISO-8859-1\">
        <SCRIPT LANGUAGE=\"JavaScript\">
    var blur = 0;
    var starttime = new Date();
    var startclock = starttime.getTime();
    var mytimeleft = 0;
    function doTime() {
      window.setTimeout( \"doTime()\", 1000 );
      t = new Date();
      time = Math.round((t.getTime() - starttime.getTime())/1000);
      if (mytimeleft) {
        time = mytimeleft - time;
        if (time <= 0) {
          window.location = \"$loginpath?res=popup3&uamip=$uamip&uamport=$uamport\";
        }
      }
      if (time < 0) time = 0;
      hours = (time - (time % 3600)) / 3600;
      time = time - (hours * 3600);
      mins = (time - (time % 60)) / 60;
      secs = time - (mins * 60);
      if (hours < 10) hours = \"0\" + hours;
      if (mins < 10) mins = \"0\" + mins;
      if (secs < 10) secs = \"0\" + secs;
      title = \"Online time: \" + hours + \":\" + mins + \":\" + secs;
      if (mytimeleft) {
        title = \"Remaining time: \" + hours + \":\" + mins + \":\" + secs;
      }
      if(document.all || document.getElementById){
         document.title = title;
      }
     else {   
        self.status = title;
      }
    }
    function popUp(URL) {
      if (self.name != \"chillispot_popup\") {
        chillispot_popup = window.open(URL, 'chillispot_popup', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=1,width=350,height=300');
      }
    }
    function doOnLoad(result, URL, userurl, redirurl, timeleft) {
     if (timeleft) {
        mytimeleft = timeleft;
      }
      if ((result == 1) && (self.name == \"chillispot_popup\")) {
        doTime();
      }
     if ((result == 1) && (self.name != \"chillispot_popup\")) {
        chillispot_popup = window.open(URL, 'chillispot_popup', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=1,width=350,height=300');
      }
      if ((result == 2) || result == 5) {
        document.form1.UserName.focus()
      }
      if ((result == 2) && (self.name != \"chillispot_popup\")) {
        chillispot_popup = window.open('', 'chillispot_popup', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=1,width=400,height=200');
        chillispot_popup.close();
      }
      if ((result == 12) && (self.name == \"chillispot_popup\")) {
        doTime();
        if (redirurl) {
          opener.location = redirurl;
        }
        else if (opener.home) {
          opener.home();
        }
        else {
          opener.location = \"about:home\";
        }
        self.focus();
        blur = 0;
      }
      if ((result == 13) && (self.name == \"chillispot_popup\")) {
        self.focus();
        blur = 1;
      }
    }
    function doOnBlur(result) {
      if ((result == 12) && (self.name == \"chillispot_popup\")) {
        if (blur == 0) {
          blur = 1;
          self.focus();
        }
      }
    }
    function popup_logoff(url, name)
    {
    MyNewWindow=window.open(\"http://\"+url,name);
    }
  </script>";
}
function print_body(){
  global $headline, $bodytext, $body_onload,$result, $loginpath;
  $uamip = $_GET['uamip'];
  $uamport = $_GET['uamport'];
  $userurl = $_GET['userurl'];
  $redirurl = $_GET['redirurl'];
  $userurldecode = $_GET['userurl'];
  $redirurldecode = $_GET['redirurl'];
  $timeleft = $_GET['timeleft'];
  print "
  </head>
    <body onLoad=\"javascript:doOnLoad($result, '$loginpath?res=popup2&uamip=$uamip&uamport=$uamport&userurl=$userurl&redirurl=$redirurl&timeleft=$timeleft','$userurldecode', '$redirurldecode', '$timeleft')\" onBlur = \"javascript:doOnBlur($result)\" bgColor = '#c0d8f4'>
      <h1 style=\"text-align: center;\">$headline</h1>
      <center>$bodytext</center><br>";
# begin debugging
  print '<center>THE INPUT (for debugging):<br>';
    foreach ($_GET as $key => $value) {
      print $key . '=' . $value . '<br>';
    }
  print '<br></center>';
# end debugging
}
function print_bodyz(){
  global $headline, $bodytext, $body_onload, $result, $loginpath;
  $uamip = $_GET['uamip'];
  $uamport = $_GET['uamport'];
  $userurl = $_GET['userurl'];
  $redirurl = $_GET['redirurl'];
  $userurldecode = $_GET['userurl'];
  $redirurldecode = $_GET['redirurl'];
  $timeleft = $_GET['timeleft'];
  print "
  </head>
    <body onLoad=\"javascript:doOnLoad($result, '$loginpath?res=popup2&uamip=$uamip&uamport=$uamport&userurl=$userurl&redirurl=$redirurl&timeleft=$timeleft','$userurldecode', '$redirurldecode', '$timeleft')\" onBlur = \"javascript:doOnBlur($result)\" bgColor = '#c0d8f4' onUnLoad = \"javascript:popup_logoff('192.168.182.1:3990/logoff','Error')\">
      <h1 style=\"text-align: center;\">$headline</h1>
      <center>$bodytext</center><br><br>
      <center>Do not close this window</center>
      <center>otherwise you'll be logged out immediately</center>";
# begin debugging
  print '<center>THE INPUT (for debugging):<br>';
    foreach ($_GET as $key => $value) {
      print $key . '=' . $value . '<br>';
    }
  print '<br></center>';
# end debugging
}
function print_login_form(){
  global $loginpath;
  print '<FORM name="form1" METHOD="get" action="' . $loginpath . '?">
          <INPUT TYPE="HIDDEN" NAME="chal" VALUE="' . $_GET['challenge'] . '">
          <INPUT TYPE="HIDDEN" NAME="uamip" VALUE="' . $_GET['uamip'] . '">
          <INPUT TYPE="HIDDEN" NAME="uamport" VALUE="' . $_GET['uamport'] . '">
          <INPUT TYPE="HIDDEN" NAME="userurl" VALUE="' . $_GET['userurl'] . '">
          <center>
          <table border="0" cellpadding="5" cellspacing="0" style="width: 217px;">
          <tbody>
            <tr>
              <td align="right">Login:</td>
              <td><input type="text" name="UserName" size="20" maxlength="255"></td>
            </tr>
            <tr>
              <td align="right">Password:</td>
              <td><input type="password" name="Password" size="20" maxlength="255"></td>
            </tr>
            <tr>
              <td align="center" colspan="2" height="23"><input type="submit" name="login" value="login"></td>
          </tr>
        </tbody>
        </table>
        </center>
      </form>';
}
function print_register_form(){
  global $loginpath;
  print '<FORM name="form2" METHOD="post" action="' . $loginpath . '?register=register">
          <center>
          <table border="0" cellpadding="5" cellspacing="0" style="width: 217px;">
          <tbody>
            <tr>
              <td align="right">Register:</td>
              <td><input type="text" name="UserName" size="20" maxlength="255"></td>
            </tr>
            <tr>
              <td align="right">Password:</td>
              <td><input type="password" name="Password" size="20" maxlength="255"></td>
            </tr>
            <tr>
              <td align="center" colspan="2" height="23"><input type="submit" name="register" value="register"></td>
          </tr>
        </tbody>
        </table>
        </center>
      </form>';
}
function print_modify_form(){
  global $loginpath;
  print '<FORM name="form3" METHOD="post" action="' . $loginpath . '?modify=modify">
          <center>
          <table border="0" cellpadding="5" cellspacing="0" style="width: 400px;">
          <tbody>
            <tr>
              <td align="right">Username:</td>
              <td><input type="text" name="UserName" size="20" maxlength="255"></td>
            </tr>
            <tr>
              <td align="right">Traffic Limit:</td>
              <td><input type="text" name="TrafficLim" size="20" maxlength="255"></td>
            </tr>
            <tr>
              <td align="right">Time Limit:</td>
              <td><input type="text" name="TimeLim" size="20" maxlength="255"></td>
            </tr>
            <tr>
              <td align="center" colspan="2" height="23"><input type="submit" name="modify" value="modify"></td>
          </tr>
        </tbody>
        </table>
        </center>
      </form>';
}
function print_footer(){
  global $footer_text;
  print $footer_text . '</body></html>';
  exit(0);
}
function print_footerz(){
  global $footer_textz;
  print $footer_textz . '</body></html>';
  exit(0);
}
exit(0);
?>