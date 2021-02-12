<?php
    #definition
    function sent_test(){
        $myfile = fopen("../log/notifications.log", "a+");
        if($myfile){
            $txt = date("H:i:s");
            fwrite($myfile, $txt);
            fclose($myfile);
        }
    }

    #exec
    sent_test();
?>