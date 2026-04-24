<?php

namespace App\Http\Controllers;

use App\Traits\BaseApiResponse;
use App\Traits\Blockchain;
use App\Traits\OCR;
use App\Traits\TelegramNotification;
use App\Traits\UploadImage;

abstract class Controller
{
    //
    use BaseApiResponse, OCR, UploadImage, TelegramNotification, Blockchain;
}
