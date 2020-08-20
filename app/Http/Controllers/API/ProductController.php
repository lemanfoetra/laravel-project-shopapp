<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Products\AddProductRequest;
use App\Http\Requests\Products\EditProductRequest;
use App\Http\Resources\Product\AllProductResources;
use App\Models\ProductModel;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use File;


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





    public function edit(EditProductRequest $request, $product)
    {
        $data['users_id']   = Auth::user()->id;
        $data['name']       = $request->name;
        $data['price']      = $request->price;
        $data['description'] = $request->description;
        $data['updated_at'] = date('Y-m-d H:i:s');

        // saving file
        $file       = $request->file('image');
        if ($file != null && $file != '') {
            $path       = 'uploads/products/image';
            $fileName   = Carbon::now()->timestamp . "_" . uniqid() . "." . $file->getClientOriginalExtension();
            $file->move($path, $fileName);

            // file is found img_url make this
            $data['img_url']  = url($path . '/' . $fileName);
        }

        $result = DB::table('products')->where('id', $product)->update($data);
        if ($result > 0) {
            return $this->responseSuccess('Update Product Berhasil');
        }
        return $this->responseFail('Update Gagal');
    }





    public function delete(ProductModel $product)
    {
        // deleting image file
        $result = File::delete(str_replace(url('/').'/', '', $product->img_url));

        $result = DB::table('products')->where('id', $product->id)->delete();
        if ($result > 0) {
            return $this->responseSuccess('Delete Product Berhasil', $product);
        }
        return $this->responseFail('Delete Gagal');
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
