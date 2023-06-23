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
    /**
     * show anime API documentation.
     */
    public function doc_show_anime() : View
    {
        return view('documentation.show-anime');
    }
     /**
     * genre anime API documentation.
     */
    public function doc_all_genre() : View
    {
        return view('documentation.all-genre');
    }
     /**
     * anime by genre anime API documentation.
     */
    public function doc_anime_by_genre() : View
    {
        return view('documentation.anime-by-genre');
    }
     /**
     * anime by alphabet list API documentation.
     */
    public function doc_anime_list() : View
    {
        return view('documentation.anime-list');
    }
     /**
     * anime vip API documentation.
     */
    public function doc_vip_anime() : View
    {
        return view('documentation.all-vip-anime');
    }
    /**
     * send anime raing API documentation.
     */
    public function doc_send_rating() : View
    {
        return view('documentation.send-rating');
    }
}
