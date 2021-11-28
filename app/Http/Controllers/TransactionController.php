<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Book;
use App\Models\User;
use Firebase\JWT\JWT;

class TransactionController extends Controller
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

    public function showTransaction(Request $request)
    {
        $token = $request->bearerToken();
        $jwt = JWT::decode($token, env('JWT_KEY'), ['HS256']);
        $email = $jwt->sub;
        $user = User::where('email', $email)->first();
        $role = $jwt->role;

        $transactions = $role == 'admin' ? Transaction::all()
            : Transaction::where('user_id', $user->id)->get();

        if ($transactions) {
            foreach ($transactions as $transaction) {
                $data = Book::find($transaction->book_id);
                $bookArray = array(
                    'title' => $data->title,
                    'author' => $data->author
                );
                $book = (object) $bookArray;
                $transaction->book = $book;
            }
            return response()->json([
                'success' => true,
                'message' => 'Successfully get transaction',
                'data' => ['transactions' => $transactions]
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden'
            ], 403);
        }
    }

    public function CreateTransactions(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'book_id' => ['required'],
        ]);
        if ($validation->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Empty field at transaction',
            ], 400);
        }

        $bookid = $request->book_id;
        $token = $request->bearerToken();
        $jwt = JWT::decode($token, env('JWT_KEY'), ['HS256']);
        $email = $jwt->sub;
        $userid = User::where('email', $email)->first()->id;
        $deadline = date('Y-m-d H:i:s', time() + 7 * 24 * 60 * 60);
        $book = Book::find($bookid);

        if (!$book) {
            return response()->json([
                'success' => false,
                'message' => 'Book Not Found',
            ], 400);
        }

        $createdata = new Transaction([
            'user_id' => $userid,
            'book_id' => $bookid,
            'deadline' => $deadline
        ]);
        $createdata->save();

        if ($createdata) {
            return response()->json([
                'success' => true,
                'message' => 'Transaksi Berhasil',
                'data' => ['transaction' => [
                    'book' => [
                        'title' => $book->title,
                        'author' => $book->author,
                    ],
                    'deadline' => $createdata->deadline,
                    'created_at' => $createdata->created_at,
                    'updated_at' => $createdata->updated_at,
                ]]
            ], 201);
        }
    }

    public function getTransactionbyid(Request $request, $transactionId)
    {
        $token = $request->bearerToken();
        $jwt = JWT::decode($token, env('JWT_KEY'), ['HS256']);
        $email = $jwt->sub;
        $user = User::where('email', $email)->first();
        $role = $jwt->role;
        $transaction = Transaction::find($transactionId);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi Not Found'
            ], 404);
        }

        if ($role == 'admin' || $user->id == $transaction->user_id) {
            $book = Book::find($transaction->book_id);
            $user2 = User::find($transaction->user_id);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi Ditemukan',
                'data' => ['transaction' => [
                    'user' => [
                        'name' => $user2->name,
                        'email' => $user2->email,
                    ],
                    'book' => [
                        'title' => $book->title,
                        'author' => $book->author,
                        'description' => $book->description,
                        'synopsis' => $book->synopsis,
                    ],
                    'deadline' => $transaction->deadline,
                    'created_at' => $transaction->created_at,
                    'updated_at' => $transaction->updated_at,
                ]]
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi Forbidden'
            ], 403);
        }
    }

    public function editTransaction(Request $request, $transactionId)
    {
        $token = $request->bearerToken();
        $jwt = JWT::decode($token, env('JWT_KEY'), ['HS256']);
        $email = $jwt->sub;
        $user = User::where('email', $email)->first();
        $role = $jwt->role;
        $transaction = Transaction::find($transactionId);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi Not Found'
            ], 404);
        }

        if ($role == 'admin') {
            $book = Book::find($transaction->book_id);
            $user2 = User::find($transaction->user_id);
            $transaction->deadline = $request->deadline;
            $transaction->save();
            return response()->json([
                'success' => true,
                'message' => 'Transaksi Berhasil Diubah',
                'data' => ['transaction' => [
                    'user' => [
                        'name' => $user2->name,
                        'email' => $user2->email,
                    ],
                    'book' => [
                        'title' => $book->title,
                        'author' => $book->author,
                        'description' => $book->description,
                        'synopsis' => $book->synopsis,
                    ],
                    'deadline' => $transaction->deadline,
                    'created_at' => $transaction->created_at,
                    'updated_at' => $transaction->updated_at,
                ]]
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi Forbidden'
            ], 403);
        }
    }
}
