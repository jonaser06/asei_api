<?php

interface iModule {
    public function getdata( $data = '', $table = '');
    public function setdata( $data = '', $table = '');
    public function deldata( $data = '', $table = '');
    public function upddata( $data = '', $where = '', $table = '');
}

?>