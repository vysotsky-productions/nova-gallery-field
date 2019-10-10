<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 10/4/2019
 * Time: 3:49 PM
 */

namespace VysotskyProductions\NovaGalleryField;

use Laravel\Nova\Http\Requests\NovaRequest;

class GalleryRequest
{
    private $request;
    /**
     * @var string
     */
    private $namespace;

    public function __construct(NovaRequest $request, string $namespace)
    {
        $this->request = $request;
        $this->namespace = $namespace;
    }

    public function get(string $key)
    {
        return $this->request[$this->namespace ."_". $key];
    }

    public function has($key)
    {
        return $this->request->has($this->namespace ."_". $key);
    }

    public function getAssocDecoded($key)
    {
        return json_decode($this->get($key), true) ?? [];
    }
    public function getDecoded($key)
    {
        return json_decode($this->get($key)) ?? [];
    }
}