<?php

namespace App\Helper;

use Illuminate\Support\Facades\Storage;

class fileManagerHelper
{
    // private $storage;
    // private $request_file;

    public function __construct()
    {
        // $this->storage = $storage;
        // $this->request_file = $request_file;
    }

    public static function storeFile($customer_id, $request_file, $storage)
    {
        $file = "";
        try {
            // if ($request->hasFile('image')) {
            // $file = $request_file->getClientOriginalName();
            // $fileName = '' . time() . '_' . $file . '';
            // $file = $request_file->storeAs($customer_id, $fileName, $storage);
            // } else {
            // return 'no image found';
            // }
            $filename = Storage::disk(env('DISK'))->put($storage.'/'.$customer_id, 
            $request_file, 'public');


            return $filename;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
