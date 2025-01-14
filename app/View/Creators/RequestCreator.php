<?php

namespace App\View\Creators;

use Illuminate\Http\Request;
use Illuminate\View\View;

class RequestCreator
{
    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * 绑定视图数据.
     *
     * @param  \Illuminate\View\View  $view
     * @return void
     */
    public function create(View $view)
    {
        $view->with('request', $this->request);
    }
}
