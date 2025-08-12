# Laravel CSV Import

## 1. Create the migration / model for posts table
#### Bash
    php artisan make:model Post -m

#### posts table
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->timestamps();
        });
    }

#### Bash
    php artisan migrate

#### Post Model
    protected $fillable = ['title', 'description'];
    
## 2. Create a Controller for CSV Import
#### Bash
    php artisan make:controller PostCsvController
    
#### In app/Http/Controllers/PostCsvController.php:
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

## 3. Create the Blade view

    <!DOCTYPE html lang="en">
    <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>Import Posts CSV</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
        </head>
        <body class="container mt-5">
            @if(session('success'))
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
            @endif
    
    
            <h4 class="mb-3">Import Posts from CSV using Basic PHP Code</h4>
    
            <form action="{{ route('posts.import') }}" method="POST" enctype="multipart/form-data" class="card p-4 shadow-sm">
                @csrf
                <div class="mb-3">
                    <label for="csv_file" class="form-label">Upload CSV File</label>
                    <input type="file" name="csv_file" id="csv_file" class="form-control" accept=".csv">
                </div>
                <button type="submit" class="btn btn-primary">Import</button>
            </form>
     
            <div class="card p-4 shadow-sm">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($posts as $post)
                            <tr>
                                <td>{{ $post->id }}</td>
                                <td>{{ ucwords($post->title) }}</td>
                                <td>{{ $post->description }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </body>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    </html>

## 4. Add Routes

    use App\Http\Controllers\PostCsvController;
    
    Route::get('posts/import', [PostCsvController::class, 'showForm'])->name('posts.import.form');
    Route::post('posts/import', [PostCsvController::class, 'import'])->name('posts.import');

    </body>
    </html>
    

