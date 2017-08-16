<?php

namespace App\Http\Controllers;

use App\Actuality;
use App\Category;
use App\Preference;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Response;

class ApiCategoriesController extends Controller
{
    public function index()
    {
        $categories = Category::select('categories.name', 'categories.color', 'categories.id', 'actualities.category_id', DB::raw('count(actualities.category_id) as totalActualities'))
            ->leftJoin('actualities', 'actualities.category_id', '=', 'categories.id')
            ->whereNull('actualities.actuality_id')
            ->groupBy('categories.name', 'categories.color', 'categories.id', 'actualities.category_id')
            ->orderBy('categories.name')
            ->get();

        $preferences = Preference::where('user_id', Auth::guard('api')->user()->id)
            ->get();

        $myPref = [];

        foreach ($categories as $category) {
            foreach ($preferences as $preference) {
                if ($category->id == $preference->category_id) {
                    $myPref[] = $category->id;
                }
            }
        }

        foreach ($categories as $category) {
            if (in_array($category->id, $myPref)) {
                $category->preference = true;
            } else {
                $category->preference = false;
            }
        }

        return $this->response(200, $categories);
    }
}
