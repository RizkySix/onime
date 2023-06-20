<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ApiDocumentationController extends Controller
{
    /**
     * guide documentation.
     */
    public function doc_guide() : View
    {
        return view('documentation.integration-guide');
    }
      /**
     * Get all anime API documentation.
     */
    public function doc_get_all() : View
    {
        return view('documentation.get-all');
    }
}
