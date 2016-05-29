<?php
use Yaim\Mitter\ApiController;
// setup routes 
if (config('mitter.route.useMitterRoutes')) {

    Route::group(config('mitter.route.routeGroupConfig', []), function () {

        /**
         * index
         */
        Route::get('/{model}', 'BaseController@index');

        /**
         * create
         */
        Route::get('/{model}/create', 'BaseController@create');

        /**
         * store
         */
        Route::post('/{model}', 'BaseController@store');

        /**
         * show
         */
        Route::get('/{model}/{id}', 'BaseController@show');

        /**
         * edit
         */
        Route::get('/{model}/{id}/edit', 'BaseController@edit');

        /**
         * update
         */
        Route::put('/{model}/{id}', 'BaseController@update');

        /**
         * delete
         */
        Route::delete('/{model}/{id}', 'BaseController@destroy');

    });
}


if (config('mitter.api.useMitterRoutes', true)) {

    Route::group(config('mitter.api.routeGroupConfig', []), function () {

        Route::get('{model}/{action?}', function ($model, $action = null) {

            if (config("mitter.api.aliases.{$model}")) {
                $model = config("mitter.api.aliases.{$model}");
            } elseif (config("mitter.api.usePanelModelsAliases") && config("mitter.models.aliases.{$model}")) {
                $model = config("mitter.models.aliases.{$model}");
            } else {
                abort(404);
            }
            $request = request();
            $model = app($model);
            $action = studly_case($action);
            $api = new ApiController($model, $action, config('mitter.api', []));
            return $api->get($request->q, $request->page);
        });

    });
}
