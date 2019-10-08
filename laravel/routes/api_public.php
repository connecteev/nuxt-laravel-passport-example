<?php

// // Publicly accessible (Open) APIs
// Route::group(['prefix' => 'v1/open', 'as' => 'api.', 'namespace' => 'Api\V1\Open', 'middleware' => []], function () {
//     // Tags
//     Route::get('tags', 'TagsApiController@index');
//     //Route::get('tags/{tag}', 'TagsApiController@show'); // To use implicit route-model-binding
//     Route::get('tags/{param}', 'TagsApiController@show');
//     Route::get('userTags', 'TagsApiController@userTags');
// });
