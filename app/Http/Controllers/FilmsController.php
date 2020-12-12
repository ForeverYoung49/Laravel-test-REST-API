<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Films;
use App\Models\PackGenres;
use App\Models\Genre;
use Validator;

class FilmsController extends Controller
{

    public function index(Request $request)
    {
        $sort = 'id';
        $how = 'ASC';
        if($request->has(['colName'])){
            $sort = 'films.'.$request->colName;
        }
        if($request->has(['how'])){
            $how = $request->how;
        }
        $films = Films::join('pack_genres','films.id','=','pack_genres.film_id')
            ->join('genres','genres.id','=','pack_genres.genre_id')
            ->select('films.id','films.title','films.description','films.img','films.release',Genre::raw('group_concat(`genres`.`name`) as genre'))
            ->groupBy('films.id','films.title','films.description','films.img','films.release')
            ->orderBy($sort,$how);
        if($request->has(['genre'])){
            $genre = PackGenres::join('genres','genres.id','=','pack_genres.genre_id')
                ->where('genres.name','=',$request->genre)
                ->select('pack_genres.film_id')
                ->get();
            $films = $films->whereIn('films.id',$genre)->simplePaginate(10);
        }
        else {
            $films = $films->simplePaginate(10);
        }
        
        if(blank($films))
            $films = json_encode('Not found');
        return response()->json($films, 200);
    }

    public function store(Request $request)
    {   
        $rules = [
            'title' => 'required|min:1|max:30',
            'description' => 'required|min:1',
            'img' => ['required','regex:/(\d)+|.|(?:jpeg|jpg|png|gif)/'],
            'release' => 'required|date_format:Y-m-d',
            'genres' => 'required|array',
            'genres.*' => 'int'
        ];

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        Films::create([
            'title' => $request->title,
            'description' => $request->description,
            'img' => $request->img,
            'release' => $request->release
        ]);

        $maxId = Genre::select('id')->max('id');
        $id = Films::select('id')->max('id');
        
        $genres = Genre::select('id')
            ->whereIn('id',$request->genres)
            ->get();
        foreach($genres as $g){
            PackGenres::create([
                'film_id' => $id,
                'genre_id' => $g->id
            ]);
        }

        $newFilm = Films::join('pack_genres','films.id','=','pack_genres.film_id')
            ->join('genres','genres.id','=','pack_genres.genre_id')
            ->select('films.id','films.title','films.description','films.img','films.release',Genre::raw('group_concat(`genres`.`name`) as genre'))
            ->groupBy('films.id','films.title','films.description','films.img','films.release')
            ->where('films.id','=',$id)
            ->first();

        return response()->json($newFilm, 201);;
    }

    public function show($id)
    {
        $films = Films::join('pack_genres','films.id','=','pack_genres.film_id')
            ->join('genres','genres.id','=','pack_genres.genre_id')
            ->select('films.id','films.title','films.description','films.img','films.release',Genre::raw('group_concat(`genres`.`name`) as genre'))
            ->where('films.id','=',$id)
            ->groupBy('films.id','films.title','films.description','films.img','films.release')
            ->first();
        if(is_null($films)){
            return response()->json('Not found', 404);
        }

        return response()->json($films, 200);
    }

    public function update(Request $request, $id)
    {
        $film = Films::find($id);
        if(is_null($film)){
            return response()->json('Not found', 404);
        }

        $rules = [
            'title' => 'min:1|max:30',
            'description' => 'min:1',
            'img' => ['regex:/(\d)+|.|(?:jpeg|jpg|png|gif)/'],
            'release' => 'date_format:Y-m-d'
        ];

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }
        $film->update($request->all());
        return response()->json($film, 200);
    }

    public function destroy($id)
    {
        $film = Films::find($id);
        if(is_null($film)){
            return response()->json('Not found', 404);
        }
        Films::destroy($id);
        return response()->json('Film was deleted', 204);
    }
}
