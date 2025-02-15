<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Traits\FlashAlert;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    use FlashAlert;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $articles = Article::paginate(10);
        return view('pages.article.index', compact('articles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.article.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string']
        ]);

        request()->user()->articles()->create($request->all());

        return redirect()->route('article.index')->with($this->alertCreated());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $article = Article::findOrFail($id);

            if (
                request()->user()->hasRole(['superadmin', 'admin']) ||
                request()->user()->isAbleToAndOwns('articles-update', $article)
            ) {
                return view('pages.article.edit', compact('article'));
            } else {
                return redirect()->route('article.index')->with($this->permissionDenied());
            }
        } catch (ModelNotFoundException $e) {
            return redirect()->route('article.index')->with($this->alertNotFound());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $article = Article::findOrFail($id);

            if (
                request()->user()->hasRole(['superadmin', 'admin']) ||
                request()->user()->isAbleToAndOwns('articles-update', $article)
            ) {
                $this->validate($request, [
                    'title' => ['required', 'string', 'max:255'],
                    'body' => ['required', 'string']
                ]);

                $article->update($request->all());

                return redirect()->route('article.index')->with($this->alertUpdated());
            } else {
                return redirect()->route('article.index')->with($this->permissionDenied());
            }

        } catch (ModelNotFoundException $e) {
            return redirect()->route('article.index')->with($this->alertNotFound());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $article = Article::findOrFail($id);

            if (
                request()->user()->hasRole('superadmin') ||
                request()->user()->isAbleToAndOwns('articles-delete', $article)
            ) {
                $article->delete();

                return redirect()->route('article.index')->with($this->alertDeleted());
            } else {
                return redirect()->route('article.index')->with($this->permissionDenied());
            }

        } catch (ModelNotFoundException $e) {
            return redirect()->route('article.index')->with($this->alertNotFound());
        }
    }
}
