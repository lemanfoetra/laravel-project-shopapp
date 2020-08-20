<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Products\AddProductRequest;
use App\Http\Resources\Product\AllProductResources;
use App\Models\ProductModel;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{

    public function __construct()
    {
        $this->middleware('jwt.verify');
    }


    public function index()
    {
        return AllProductResources::collection(ProductModel::paginate(10));
    }



    public function add(AddProductRequest $request)
    {

        // saving file
        $file       = $request->file('image');
        $path       = 'uploads/products/image';
        $fileName   = Carbon::now()->timestamp . "_" . uniqid() . "." . $file->getClientOriginalExtension();
        $file->move($path, $fileName);

        $product = new ProductModel();
        $product->users_id  = Auth::user()->id;
        $product->name      = $request->name;
        $product->price     = $request->price;
        $product->description   = $request->description;
        $product->img_url       = url($path . '/' . $fileName);
        if ($product->save()) {
            return $this->responseSuccess('Berhasil Disimpan', $product);
        }
        return $this->responseFail('Gagal Disimpan', $product);
    }








    protected function responseSuccess($message = 'Sukses', $data = '', $kode = 200)
    {
        return response()->json(
            [
                'status'    =>  'success',
                'message'   =>  $message,
                'data'      =>  $data,
            ],
            $kode
        );
    }


    protected function responseFail($message = 'Gagal', $data = '', $kode = 401)
    {
        return response()->json(
            [
                'status'    =>  'failed',
                'message'   =>  $message,
                'data'      =>  $data,
            ],
            $kode
        );
    }
}
