<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function getBooks()
    {
        $book = Book::all();

        if ($book) {
            return response()->json([
                'success' => true,
                'message' => 'Retrieve Book Data',
                'data' => [
                    'books' => $book
                ]
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Book Not Found',
                'data' => [
                    'books' => $book
                ]
            ], 404);
        }
    }

    public function getBook($bookId)
    {
        $saveData = Book::find($bookId);

        if ($saveData) {
            return response()->json([
                'success' => true,
                'message' => 'Retrieve Book Data',
                'data' => [
                    'book' => $saveData
                ]
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Book Not Found',
            ], 404);
        }
    }

    public function createBook(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'title' => ['required'],
            'description' => ['required'],
            'author' => ['required'],
            'year' => ['required'],
            'synopsis' => ['required'],
            'stock' => ['required'],
        ]);

        if ($validation->fails()) {
            return response()->json([
                'success' => true,
                'message' => 'Incomplete book added',
            ], 400);
        }

        $body = [
            'title' => $request->title,
            'description' => $request->description,
            'author' => $request->author,
            'year' => $request->year,
            'synopsis' => $request->synopsis,
            'stock' => $request->stock,
        ];

        $newBook = Book::create($body);

        return response()->json([
            'success' => true,
            'message' => 'New book added',
            'data' => [
                'book' => $newBook,
            ],
        ], 201);
    }

    public function updateBook(Request $request, $bookId)
    {
        $validation = Validator::make($request->all(), [
            'title' => ['required'],
            'description' => ['required'],
            'synopsis' => ['required'],
        ]);

        if ($validation->fails()) {
            return response()->json([
                'success' => true,
                'message' => 'Incomplete book added',
            ], 400);
        }
        
        $book = Book::find($bookId);
        
        if (!$book) {
            return response()->json([
                'success' => false,
                'message' => 'Book not found'
            ], 404);
        }

        $book->title = $request->title;
        $book->description = $request->description;
        $book->synopsis = $request->synopsis;
        $book->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Success get all books ',
            'data' => [
                'book' => $book
            ]
        ], 200);
    }

    public function deleteBook($bookId)
    {
        $book = Book::where('id', $bookId)->first();
        if ($book) {
            $book->delete();
            return response()->json([
                'success' => true,
                'message' => 'Book Deleted'
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed Delete Book'
            ], 404);
        }
    }
}
