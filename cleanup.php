<?php
    $db = mysqli_connect("localhost", "radius", "changeme", "radius");

    while (true) {
        $result = mysqli_query(
            $db, 
            "DELETE FROM radcheck 
            WHERE username IN (
                SELECT tmp.username
                FROM (
                    SELECT ra.username, SUM(ra.acctsessiontime) AS total_session_time
                    FROM radcheck rc
                    JOIN radacct ra ON rc.username = ra.username
                    WHERE rc.attribute = 'Max-All-Session'
                    GROUP BY ra.username
                ) tmp
                JOIN radcheck rc ON rc.username = tmp.username
                WHERE rc.attribute = 'Max-All-Session' 
                AND tmp.total_session_time > rc.`value`
            )"
        );
        $result = mysqli_query(
            $db, 
            "DELETE FROM radcheck 
            WHERE username IN (
                SELECT tmp.username
                FROM (
                    SELECT ra.username, SUM(ra.acctinputoctets + ra.acctoutputoctets) AS total_traffic
                    FROM radcheck rc
                    JOIN radacct ra ON rc.username = ra.username
                    WHERE rc.attribute = 'Max-All-Traffic'
                    GROUP BY ra.username
                ) tmp
                JOIN radcheck rc ON rc.username = tmp.username
                WHERE rc.attribute = 'Max-All-Traffic' 
                AND tmp.total_traffic > rc.`value`
            )"
        );
        sleep(5);
    }
?>