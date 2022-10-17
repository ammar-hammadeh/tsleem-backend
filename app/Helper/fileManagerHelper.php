<?php

namespace App\Helper;

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
            $file = $request_file->getClientOriginalName();
            $fileName = '' . time() . '_' . $file . '';
            $file = $request_file->storeAs($customer_id, $fileName, $storage);
            // } else {
            // return 'no image found';
            // }

            return $fileName;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
