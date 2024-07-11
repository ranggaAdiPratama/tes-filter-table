<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    // SECTION laman produk
    /* -------------------------------------------------------------------------- */
    /*                                LAMAN PRODUK                                */
    /* -------------------------------------------------------------------------- */
    // NOTE GET /auth/master-data/products
    public function index(): View
    {
        $categories = DB::table('categories')
            ->whereNull('deleted_at')
            ->orderBy('order', 'asc')
            ->get();

        $data = [
            'categories'    => $categories,
            'script'        => 'components.scripts.BE.masterData.product',
            'title'         => 'Produk'
        ];

        return view('BE.masterData.product', $data);
    }
    // !SECTION laman produk
    // SECTION hapus data
    /* -------------------------------------------------------------------------- */
    /*                                 HAPUS DATA                                 */
    /* -------------------------------------------------------------------------- */
    // NOTE DELETE /auth/master-data/products/{id}
    public function destroy($id)
    {
        try {
            DB::table('products')->where('id', $id)->update([
                'deleted_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);

            return response()->json([
                'msg'       => 'Produk berhasil dihapus',
                'status'    => true,
            ]);
        } catch (Exception $e) {
            if (env('APP_ENV') == 'local') {
                dd($e);
            }

            return response()->json([
                'e'         => $e,
                'msg'       => 'Error',
                'status'    => false,
            ]);
        }
    }
    // !SECTION hapus data
    // SECTION get server
    /* -------------------------------------------------------------------------- */
    /*                                 GET SERVER                                 */
    /* -------------------------------------------------------------------------- */
    // NOTE /auth/master-data/products/server/{id}
    public function getServer($id)
    {
        try {
            $servers = DB::table('product_servers')
                ->where('product_id', $id)
                ->orderBy('id', 'asc')
                ->get();

            $textValue = '';
            $value = [];

            if (count($servers) > 0) {
                foreach ($servers as $server) {
                    $textValue = $server->server . ',';

                    $value[] = [
                        'server'    => $server->server,
                    ];
                }

                $textValue = substr($textValue, 0, -1);
            }

            return response()->json([
                'textValue' => $textValue,
                'value'     => $value,
                'status'    => true,
            ]);
        } catch (Exception $e) {
            if (env('APP_ENV') == 'local') {
                dd($e);
            }

            return response()->json([
                'e'         => $e,
                'msg'       => 'Error',
                'status'    => false,
            ]);
        }
    }
    // !SECTION get server
    // SECTION ambil data
    /* -------------------------------------------------------------------------- */
    /*                                 AMBIL DATA                                 */
    /* -------------------------------------------------------------------------- */
    // NOTE GET /auth/master-data/products/{id}
    public function show(Request $request, $id)
    {
        try {
            if (is_numeric($id)) {
                $data = DB::table('products')
                    ->where('id', $id)
                    ->first();

                $data->category_id = str_replace(' ', '', $data->category_id);
                $data->category_id = explode(',', $data->category_id);

                return response()->json($data);
            } else {
                $data = DB::table('products')->whereNull('deleted_at');

                if ($request->category_id && $request->category_id !== null) {
                    $data->where('category_id', 'like', '%' . $request->category_id . '%');
                }

                return DataTables::of($data)
                    ->editColumn(
                        'picture',
                        function ($row) {
                            $data = [
                                'id'    => $row->id,
                                'name'  => $row->name,
                                'path'  => asset('assets/images/games/' . $row->picture)
                            ];

                            return view('components.anchor.image', $data);
                        }
                    )
                    ->addColumn(
                        'categories',
                        function ($row) {
                            $categoryofProduct = explode(',', $row->category_id);

                            $categories = '';

                            for ($i = 0; $i < count($categoryofProduct); $i++) {
                                $name = DB::table('categories')
                                    ->where('id', $categoryofProduct[$i])
                                    ->first()
                                    ->name;

                                $categories .= $name . ', ';
                            }

                            $categories = substr($categories, 0, -2);

                            return $categories;
                        }
                    )
                    ->addColumn(
                        'action',
                        function ($row) {
                            $data   = [
                                'id'    => $row->id,
                            ];

                            return view('components.buttons.masterData.product', $data);
                        }
                    )
                    ->addIndexColumn()
                    ->make(true);
            }
        } catch (Exception $e) {
            if (env('APP_ENV') == 'local') {
                dd($e);
            }

            return response()->json([
                'e'         => $e,
                'msg'       => 'Error',
                'status'    => false,
            ]);
        }
    }
    // !SECTION ambil data
    // SECTION simpan data
    /* -------------------------------------------------------------------------- */
    /*                                 SIMPAN DATA                                */
    /* -------------------------------------------------------------------------- */
    // NOTE POST /auth/master-data/products
    public function store(Request $request)
    {
        // TODO
        // 1. validasi
        // 2. store
        try {
            // SECTION validasi
            $productExist = DB::table('products')
                ->where('name', $request->name)
                ->first();

            if ($request->name == null) {
                return response()->json([
                    'msg'       => 'Mohon isi nama produk',
                    'status'    => false,
                ]);
            } elseif ($productExist) {
                return response()->json([
                    'msg'       => 'Produk sudah terdaftar',
                    'status'    => false,
                ]);
            } elseif (count($request->category_id) == 0) {
                return response()->json([
                    'msg'       => 'Mohon pilih kategori',
                    'status'    => false,
                ]);
            } elseif ($request->description == null) {
                return response()->json([
                    'msg'       => 'Mohon isi deskripsi',
                    'status'    => false,
                ]);
            } elseif ($request->file('picture') == null) {
                return response()->json([
                    'msg'       => 'Mohon upload gambar',
                    'status'    => false,
                ]);
            }
            // !SECTION validasi
            // SECTION store data
            DB::transaction(function () use ($request) {
                $process = $request->process ?? 'instant';

                if ($request->file('banner')) {
                    $ext  = $request->file('banner')->getClientOriginalExtension();

                    $banner  = 'banner' . time() . '.' . $ext;

                    $destination    = base_path('public/assets/images/games/banner');

                    if (env('APP_ENV') !== 'local') {
                        $destination = getDevelopmentPublicPath() . '/assets/images/games/banner';
                    }

                    $request->file('banner')->move($destination, $banner);
                } else {
                    $banner = 'default.webp';
                }

                if ($request->file('hint_picture')) {
                    $ext  = $request->file('hint_picture')->getClientOriginalExtension();

                    $hint_picture  = 'hint' . time() . '.' . $ext;

                    $destination    = base_path('public/assets/images/games/hint');

                    if (env('APP_ENV') !== 'local') {
                        $destination = getDevelopmentPublicPath() . '/assets/images/games/hint';
                    }

                    $request->file('hint_picture')->move($destination, $banner);
                } else {
                    $hint_picture = null;
                }

                $ext  = $request->file('picture')->getClientOriginalExtension();

                $picture  = 'picture' . time() . '.' . $ext;

                $destination    = base_path('public/assets/images/games');

                if (env('APP_ENV') !== 'local') {
                    $destination = getDevelopmentPublicPath() . '/assets/images/games';
                }

                $request->file('picture')->move($destination, $picture);

                $category_id = implode(',', $request->category_id);

                DB::table('products')->insert([
                    'banner'        => $banner,
                    'category_id'   => $category_id,
                    'created_at'    => date('Y-m-d H:i:s'),
                    'description'   => $request->description,
                    'hint'          => $request->hint,
                    'hint_picture'  => $hint_picture,
                    'id_term'       => $request->id_term,
                    'name'          => $request->name,
                    'picture'       => $picture,
                    'process'       => $process,
                    'sending'       => 'Otomatis',
                    'slug'          => Str::slug($request->name)
                ]);
            });
            // !SECTION store data
            return response()->json([
                'msg'       => 'Produk berhasil ditambahkan',
                'status'    => true,
            ]);
        } catch (Exception $e) {
            if (env('APP_ENV') == 'local') {
                dd($e);
            }

            return response()->json([
                'e'         => $e,
                'msg'       => 'Error',
                'status'    => false,
            ]);
        }
    }
    // !SECTION simpan data
    // SECTION update data
    /* -------------------------------------------------------------------------- */
    /*                                 UPDATE DATA                                */
    /* -------------------------------------------------------------------------- */
    // NOTE POST /auth/master-data/products/{id}
    public function update(Request $request, $id)
    {
        // TODO
        // 1. validasi
        // 2. store
        try {
            // SECTION validasi
            $productExist = DB::table('products')
                ->where('name', $request->name)
                ->where('id', '<>', $id)
                ->first();

            if ($request->name == null) {
                return response()->json([
                    'msg'       => 'Mohon isi nama produk',
                    'status'    => false,
                ]);
            } elseif ($productExist) {
                return response()->json([
                    'msg'       => 'Produk sudah terdaftar',
                    'status'    => false,
                ]);
            } elseif (count($request->category_id) == 0) {
                return response()->json([
                    'msg'       => 'Mohon pilih kategori',
                    'status'    => false,
                ]);
            } elseif ($request->description == null) {
                return response()->json([
                    'msg'       => 'Mohon isi deskripsi',
                    'status'    => false,
                ]);
            }
            // !SECTION validasi
            // SECTION store data
            DB::transaction(function () use ($request, $id) {
                $oldData = DB::table('products')
                    ->where('id', $id)
                    ->first();

                $description = $request->description;

                if (substr($description, 0, 11) == '<p><br></p>') {
                    $description = substr($description, 11);
                }

                $hint = $request->hint;

                if (!$hint) {
                    $hint =  $oldData->hint;
                } elseif (substr($hint, 0, 11) == '<p><br></p>') {
                    $hint = substr($hint, 11);
                }

                $banner             = $oldData->banner;
                $hint_picture       = $oldData->hint_picture;
                $picture            = $oldData->picture;
                $process            = $request->process ?? 'instant';

                if ($request->file('banner')) {
                    if ($banner !== 'default.webp') {
                        $pleaseRemove = base_path('public/assets/images/games/banner/' . $banner);

                        if (env('APP_ENV') !== 'local') {
                            $pleaseRemove = getDevelopmentPublicPath() . '/assets/images/games/banner/' . $banner;
                        }


                        if (file_exists($pleaseRemove)) {
                            unlink($pleaseRemove);
                        }
                    }

                    $ext  = $request->file('banner')->getClientOriginalExtension();

                    $banner  = 'banner' . time() . '.' . $ext;

                    $destination    = base_path('public/assets/images/games/banner');

                    if (env('APP_ENV') !== 'local') {
                        $destination = getDevelopmentPublicPath() . '/assets/images/games/banner';
                    }

                    $request->file('banner')->move($destination, $banner);
                }

                if ($request->file('hint_picture')) {
                    if ($hint_picture !== null && $hint_picture) {
                        $pleaseRemove = base_path('public/assets/images/games/hint/' . $hint_picture);

                        if (env('APP_ENV') !== 'local') {
                            $pleaseRemove = getDevelopmentPublicPath() . '/assets/images/games/hint/' . $hint_picture;
                        }


                        if (file_exists($pleaseRemove)) {
                            unlink($pleaseRemove);
                        }
                    }

                    $ext  = $request->file('hint_picture')->getClientOriginalExtension();

                    $hint_picture  = 'hint' . time() . '.' . $ext;

                    $destination    = base_path('public/assets/images/games/hint');

                    if (env('APP_ENV') !== 'local') {
                        $destination = getDevelopmentPublicPath() . '/assets/images/games/hint';
                    }

                    $request->file('hint_picture')->move($destination, $hint_picture);
                }

                if ($request->file('picture')) {
                    $pleaseRemove = base_path('public/assets/images/games/' . $picture);

                    if (env('APP_ENV') !== 'local') {
                        $destination = getDevelopmentPublicPath() . 'assets/images/games/' . $picture;
                    }

                    if (file_exists($pleaseRemove)) {
                        unlink($pleaseRemove);
                    }

                    $ext  = $request->file('picture')->getClientOriginalExtension();

                    $picture  = 'picture' . time() . '.' . $ext;

                    $destination    = base_path('public/assets/images/games');

                    if (env('APP_ENV') !== 'local') {
                        $destination = getDevelopmentPublicPath() . '/assets/images/game';
                    }

                    $request->file('picture')->move($destination, $picture);
                }

                $category_id = implode(',', $request->category_id);

                DB::table('products')->where('id', $id)->update([
                    'banner'        => $banner,
                    'category_id'   => $category_id,
                    'created_at'    => date('Y-m-d H:i:s'),
                    'description'   => $description,
                    'hint'          => $hint,
                    'hint_picture'  => $hint_picture,
                    'id_term'       => $request->id_term ?? $oldData->id_term,
                    'name'          => $request->name,
                    'picture'       => $picture,
                    'process'       => $process,
                    'slug'          => Str::slug($request->name)
                ]);
            });
            // !SECTION store data
            return response()->json([
                'msg'       => 'Produk berhasil disunting',
                'status'    => true,
            ]);
        } catch (Exception $e) {
            if (env('APP_ENV') == 'local') {
                dd($e);
            }

            return response()->json([
                'e'         => $e,
                'msg'       => 'Error',
                'status'    => false,
            ]);
        }
    }
    // !SECTION update data
    // SECTION update server
    /* -------------------------------------------------------------------------- */
    /*                                UPDATE SERVER                               */
    /* -------------------------------------------------------------------------- */
    public function updateServer(Request $request, $id)
    {
        try {
            $servers = $request->serverName;

            $servers = explode(',', $servers);

            DB::transaction(function () use ($servers, $id) {
                DB::table('product_servers')->where('product_id', $id)->delete();

                for ($i = 0; $i < count($servers); $i++) {
                    DB::table('product_servers')->insert([
                        'created_at'    => date('Y-m-d H:i:s'),
                        'product_id'    => $id,
                        'server'        => $servers[$i]
                    ]);
                }
            });

            return response()->json([
                'msg'       => 'Server produk berhasil disunting',
                'status'    => true,
            ]);
        } catch (Exception $e) {
            if (env('APP_ENV') == 'local') {
                dd($e);
            }

            return response()->json([
                'e'         => $e,
                'msg'       => 'Error',
                'status'    => false,
            ]);
        }
    }
    // !SECTION update server
}
