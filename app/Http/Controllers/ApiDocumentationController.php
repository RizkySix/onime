<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ApiDocumentationController extends Controller
{
      /**
     * Get all anime API documentation.
     */
    public function doc_get_all() : View
    {
        return view('documentation.get-all');
    }
}
