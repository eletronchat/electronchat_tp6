<?php

namespace app\api\middleware;

class After
{
    public function handle($request, \Closure $next)
    {
      $response = $next($request);
      return json(reset($response));
    }
}
