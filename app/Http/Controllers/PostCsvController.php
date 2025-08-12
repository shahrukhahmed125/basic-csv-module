<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostCsvController extends Controller
{
    public function index()
    {
        $posts = Post::all();
        return view('post_csv.index', compact('posts'));
    }

    public function import(Request $request)
    {   
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('csv_file');

        if(($handle = fopen($file->getPathname(), 'r')) !== false)
        {
            $firstLine = fgets($handle);
            rewind($handle);

            $delimiter = str_contains($firstLine, ';') ? ';' : ',';

            $header = fgetcsv($handle, 1000, $delimiter);

            while($row = fgetcsv($handle, 1000, $delimiter))
            {
                if(count($row) < 2) continue;

                Post::create([
                    'title' => $row[0] ?? '',
                    'description' => $row[1] ?? '',
                ]);
            }
            fclose($handle);
        }

        return redirect()->back()->with('success', 'CSV Imported Successfully!');
    }
}
