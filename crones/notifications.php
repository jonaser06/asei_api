<?php

    function sent_test(){
        echo 'sent test';
        $myfile = fopen("testfile.txt", "w");
    }
    sent_test();
?>