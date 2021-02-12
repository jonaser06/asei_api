<?php

    function sent_test(){
        echo 'sent test';
        $myfile = fopen("../log/notifications.txt", "a+");
        if($myfile){
            $txt = date("H:i:s");
            fwrite($myfile, $txt);
            fclose($myfile);
        }
    }
    sent_test();
?>