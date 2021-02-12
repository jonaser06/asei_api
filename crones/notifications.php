<?php

    function sent_test(){
        echo 'sent test';
        $myfile = fopen("testfile.txt", "w");
        if($myfile){
            echo 'escrito';
        }
    }
    sent_test();
?>