<?php

namespace App\Http\Middleware;

use Closure;

class TrimInput
{
    public function handle($request, Closure $next)
    {
        // Perform action
        if (!$input = $request->all()) {
            $input = json_decode($request->getContent(), true);
            $input = is_array($input) ? $input : [$input];
        }

        $input = $this->trimArray($input);

        $input = array_filter($input, function ($value) {
            return $value !== '';
        });
        $request->replace($input);
        return $next($request);
    }

    /**
     * Trims a entire array recursivly.
     *
     * @param array $input
     *
     * @return array
     */
    function trimArray($input)
    {
        if (!is_array($input)) {
            return trim($input);
        }
        return array_map([$this, 'trimArray'], $input);
    }
}