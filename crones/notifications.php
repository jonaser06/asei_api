<?php

    function sent_test(){
        echo 'sent testt';
        var_dump( fopen("testfile.txt", "w"));
        // if($myfile){
        //     echo 'escrito';
        // }
    }
    sent_test();
?>