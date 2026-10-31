<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Support\StoreLookbook;
use Illuminate\View\View;

class LookbookController extends Controller
{
    public function show(): View
    {
        $store = current_store();
        $lookbook = StoreLookbook::forStore($store);

        return view('storefront.lookbook', [
            'store' => $store,
            'title' => $lookbook['title'],
            'lede' => $lookbook['lede'],
            'looks' => array_values(array_filter(
                $lookbook['looks'],
                fn (array $look) => count($look['products']) > 0
            )),
        ]);
    }
}
