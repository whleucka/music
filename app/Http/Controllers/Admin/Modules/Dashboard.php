<?php

use Echo\Interface\Admin\Module;

class Dashboard implements Module
{
    public function __construct()
    {
        echo "hello, from dashboard";
    } 
}
