<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redirect;

class PaginationController extends Controller
{
    public function set_pagination_per_page( $per_page = 20 ) {

        // Keep the page size sane so a huge value cannot load whole tables at once
        $per_page = min(max((int) $per_page, 5), 100);

        session( [ 'pagination_per_page' => $per_page ] );

        return Redirect::back();
    }
}
