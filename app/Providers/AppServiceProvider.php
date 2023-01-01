<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\LengthAwarePaginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    public function boot()
    {
        //pagination
        if (!Collection::hasMacro('paginate')) {
            Collection::macro('paginate', function ($perPage, $total = null, $page = null, $pageName = 'page') {
                $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);
                return new LengthAwarePaginator(
                    $this->forPage($page, $perPage),
                    $total ?: $this->count(),
                    $perPage,
                    $page,
                    [
                        'path' => LengthAwarePaginator::resolveCurrentPath(),
                        'pageName' => $pageName,
                    ]
                );
            });
        }

        // DB::listen(function ($query) {
        //     $date = date('Y-m-d');
        //     $dateTime = date('Y-m-d H:m:s');
        //     File::append(
        //         storage_path("/logs/$date.log"),
        //         $dateTime . "::" . $query->sql . ' [' . implode(', ', $query->bindings) . ']' . PHP_EOL
        //     );
        // });
    }
}
