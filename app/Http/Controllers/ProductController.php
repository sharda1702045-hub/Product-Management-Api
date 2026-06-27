<?php

namespace App\Http\Controllers;

class ProductController extends Controller
{
    /**
     * Display the products list page.
     */
    public function index()
    {
        return view('products.index');
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit($id)
    {
        return view('products.edit', compact('id'));
    }
}
