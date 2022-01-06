<?php

namespace App\Http\Requests;

use Dingo\Api\Contract\Http\Validator;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 *   $request->headers->set('accept','application/vnd.webapp.v2+json');
 * return $next($request);
 */
class ClientRequest implements Validator
{
    protected $standardsTree;

    protected $subtype;


    protected $version;


    protected $format;

    public function __construct()
    {
        $this->standardsTree = config('api.standardsTree');
        $this->subtype       = config('api.subtype');
        $this->version       = config('api.version');
        $this->format        = config('api.defaultFormat');

    }

    public function validate(Request $request)
    {
        if ($request->is("api/client/*") || $request->is("api/payment/*")) {
            $pattern = '/application\/' . $this->standardsTree . '\.(' . $this->subtype . ')\.([\w\d\.\-]+)\+([\w]+)/';
            if (!preg_match($pattern, $request->header('accept'), $matches)) {
                $default = 'application/' . $this->standardsTree . '.' . $this->subtype . '.' . $this->version . '+' . $this->format;
                preg_match($pattern, $default, $matches);
            } else {
                list($subtype, $version, $format) = array_slice($matches, 1);
                $default = 'application/' . $this->standardsTree . '.' . $subtype . '.' . $version . '+' . $format;
            }
            $request->headers->set('accept', $default);
        }

        return true;
    }
}
