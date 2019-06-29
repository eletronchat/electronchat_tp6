<?php 

use think\facade\Route;

Route::get('/index/news', function () {
    return 'k';
});

Route::get('/api/:version/menu', function() {

    return 'kk';
});
