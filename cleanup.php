<?php
    $db = mysqli_connect("localhost", "radius", "changeme", "radius");

    while (true) {
        $result = mysqli_query(
            $db, 
            "DELETE rc FROM radcheck rc
            JOIN radacct ra ON rc.username = ra.username
            WHERE rc.attribute = 'Max-All-Traffic'
            AND ra.acctsessiontime > rc.value"
        );
        sleep(5);
    }
?>