<?php
    $db = mysqli_connect("localhost", "radius", "changeme", "radius");

    while (true) {
        $result = mysqli_query(
            $db, 
            "DELETE rc FROM radcheck rc
            JOIN radacct ra ON rc.username = ra.username
            WHERE rc.attribute = 'Max-All-Session'
            AND SUM(ra.acctsessiontime) > rc.value"
        );
        $result = mysqli_query(
            $db, 
            "DELETE rc FROM radcheck rc
            JOIN radacct ra ON rc.username = ra.username
            WHERE rc.attribute = 'Max-All-Traffic'
            AND SUM(ra.acctinputoctets + ra.acctoutputoctets) > rc.value"
        );
        sleep(5);
    }
?>