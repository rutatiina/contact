<?php

namespace Rutatiina\Item\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ApiController extends Controller
{
	private $loadViewsFrom = 'item::api.';

    public function __construct()
    {}

    public function index()
	{
        return view($this->loadViewsFrom.'index');
    }

    public function create()
	{}

    public function store(Request $request)
	{}

    public function show($id)
	{}

    public function edit($id)
	{}

    public function update(Request $request)
	{}

    public function destroy()
	{}

}
