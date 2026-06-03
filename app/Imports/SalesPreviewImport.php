<?php

namespace App\Imports;

class SalesPreviewImport extends SalesImport
{
    public function __construct()
    {
        parent::__construct(true, null, null);
    }
}