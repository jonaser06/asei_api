<?php
    date_default_timezone_set('America/Lima');

    #definition
    function sent_test(){
        $myfile = fopen("../log/notifications.txt", "a+");
        if($myfile){
            $txt = date("H:i:s").'\n';
            fwrite($myfile, $txt);
            fclose($myfile);
        }
    }

    #exec
    sent_test();
?>