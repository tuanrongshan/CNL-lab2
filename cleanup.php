<?php
    $db = mysqli_connect("localhost", "radius", "changeme", "radius");

    while (true) {
        $queries = [
            "CREATE TEMPORARY TABLE del 
            AS (
                SELECT DISTINCT tmp.username
                FROM (
                    SELECT ra.username, SUM(ra.acctsessiontime) AS total_session_time
                    FROM radacct ra
                    GROUP BY ra.username
                ) tmp
                JOIN radcheck rc ON rc.username = tmp.username
                WHERE rc.attribute = 'Max-All-Session' 
                AND tmp.total_session_time > rc.`value`
            )",
            "DELETE FROM radcheck WHERE username IN (SELECT username FROM del)",
            "DROP TEMPORARY TABLE del",
            "CREATE TEMPORARY TABLE del 
            AS (
                SELECT DISTINCT tmp.username
                FROM (
                    SELECT ra.username, SUM(ra.acctinputoctets + ra.acctoutputoctets) AS total_traffic
                    FROM radacct ra
                    GROUP BY ra.username
                ) tmp
                JOIN radcheck rc ON rc.username = tmp.username
                WHERE rc.attribute = 'Max-All-Traffic' 
                AND tmp.total_traffic > rc.`value`
            )",
            "DELETE FROM radcheck WHERE username IN (SELECT username FROM del)",
            "DROP TEMPORARY TABLE del",
        ];
        foreach ($queries as $sql) {
            $good = mysqli_query($db, $sql);
        };
        sleep(5);
    }
?>