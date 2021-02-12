<?php

    function sent_test(){
        echo 'sent test';
        $myfile = fopen("testfile.txt", "a+");
        if($myfile){
            $txt = "John Doe\n";
            fwrite($myfile, $txt);
            $txt = "Jane Doe\n";
            fwrite($myfile, $txt);
            fclose($myfile);
        }
    }
    sent_test();
?>