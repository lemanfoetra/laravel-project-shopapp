<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\AddOrdersRequest;
use App\Http\Requests\Orders\RemoveProductRequest;
use App\Http\Resources\Orders\OrdersResources;
use App\Models\Orders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrdersController extends Controller
{


    public function __construct()
    {
        $this->middleware('jwt.verify');
    }


    /** 
     * orders list
     */
    public function index()
    {
        $orders = DB::table('orders')
            ->where('orders.users_id', Auth::user()->id)
            ->where('status', 'order')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return $this->responseSuccess('get your orders success', $orders);
    }



    /**
     * Get orders success
     */
    public function ordersPaid()
    {
        $orders = DB::table('orders')
            ->where('orders.users_id', Auth::user()->id)
            ->where('status', 'paid')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return $this->responseSuccess('get your orders success', $orders);
    }



    /**
     * Get orders by tanggal order paid
     */
    public function ordersPaidWhere($date)
    {
        $orders = DB::table('orders')
            ->where('orders.users_id', Auth::user()->id)
            ->where('status', 'paid')
            ->whereRaw("DATE_FORMAT(updated_at, '%Y-%m-%d') BETWEEN ? AND ? ", [$date, $date])
            ->orderBy('created_at', 'desc')->get();
        return $this->responseSuccess('get your orders success', $orders);
    }


    /**
     * Get orders paid (group by tanggal)
     * @return String date (YYYY-MM-DD)
     */
    public function ordersPaidAt()
    {
        $orders = DB::table('orders')
            ->select(DB::raw("distinct DATE_FORMAT(updated_at, '%Y-%m-%d') as DATE"))
            ->where('orders.users_id', Auth::user()->id)
            ->where('status', 'paid')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return $this->responseSuccess('get your orders success', $orders);
    }


    /**
     * Product order list
     */
    public function orders($id)
    {
        $orders = DB::table('orders')
            ->where('orders.id', $id)
            ->select(['products.*', 'orders.status'])
            ->join('products', 'products.id', '=', 'orders.products_id')
            ->orderBy('orders.created_at', 'desc')
            ->paginate(10);
        return $this->responseSuccess('get your orders product success', $orders);
    }




    /**
     * Add orders
     */
    public function insert(AddOrdersRequest $request)
    {
        $users_id  = Auth::user()->id;
        $orders    = Orders::where([
            'products_id' => $request->products_id,
            'users_id' => $users_id,
            'status' => 'order'
        ])->first();

        if ($orders != null) {
            $orders->quantity   = ($orders->quantity + $request->quantity);
        } else {
            $orders = new Orders();
            $orders->users_id       = $users_id;
            $orders->products_id    = $request->products_id;
            $orders->quantity       = $request->quantity;
            $orders->status         = "order";
        }
        $orders->save();
        return $this->responseSuccess('product added to orders');
    }



    /**
     * Mengubah seluruh orders ke terbayar
     */
    public function paid()
    {
        $status = DB::table('orders')
            ->where('users_id', Auth::user()->id)
            ->where('status', 'order')
            ->update(['status' => 'paid']);
        if ($status)
            return $this->responseSuccess('Paid Orders Success');
    }



    /**
     * Menghapus product di dalam orders
     */
    public function removeProduct($products_id)
    {

        $order = Orders::where([
            'products_id'   => $products_id,
            'users_id'      => Auth::user()->id,
            'status'        => 'order',
        ])->first();

        if ($order != null) {

            // kurangi quantity
            if ($order->quantity > 1) {

                $order->quantity = ($order->quantity - 1);
                if ($order->save()) {
                    return $this->responseSuccess('deleted product success');
                }
            }

            // delete orders
            if ($order->delete()) {
                return $this->responseSuccess('deleted product success');
            }
        }
        return $this->responseFail('No products were removed');
    }



    /**
     * Menghapus orders
     */
    public function remove($id)
    {
        $result = DB::table('orders')->delete($id);
        if ($result) {
            return $this->responseSuccess('order removed');
        }
        return $this->responseFail('No order were removed');
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
