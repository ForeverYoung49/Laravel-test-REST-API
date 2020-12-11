<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Films;
use App\Models\PackGenres;
use App\Models\Genre;

class FilmsController extends Controller
{

    public function index()
    {
        $films = Films::join('pack_genres','films.id','=','pack_genres.film_id')
            ->join('genres','genres.id','=','pack_genres.genre_id')
            ->select('films.id','films.title','films.description','films.release',Genre::raw('group_concat(`genres`.`name`) as genre'))
            ->groupBy('films.id','films.title','films.description','films.release')
            ->paginate(2);
        return $films;
    }

    public function store(Request $request)
    {
        return PackGenres::create($request->all());
    }

    public function show($id)
    {
        $films = Films::join('pack_genres','films.id','=','pack_genres.film_id')
            ->join('genres','genres.id','=','pack_genres.genre_id')
            ->select('films.id','films.title','films.description','films.release',Genre::raw('group_concat(`genres`.`name`) as genre'))
            ->where('films.id','=',$id)
            ->groupBy('films.id','films.title','films.description','films.release')
            ->first();
        return $films;
    }

    public function update(Request $request, $id)
    {
        $film = Films::find($id);
        $film->update($request->all());
        return $film;
    }

    public function destroy($id)
    {
        return Films::destroy($id);
    }
}
