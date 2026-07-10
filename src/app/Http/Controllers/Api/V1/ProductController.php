<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ProductsRequest;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Http\Responses\ApiResponse;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
    ) {}

    public function index(ProductsRequest $request): JsonResponse
    {
        $products = $this->productService->paginate($request->validated());

        return ApiResponse::success(
            data: ProductResource::collection($products->items()),
            meta: [
                'page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        );
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productService->create($request->validated());

        return ApiResponse::success(
            data: ProductResource::make($product),
            status: Response::HTTP_CREATED,
        );
    }

    public function show(int $id): JsonResponse
    {
        $product = $this->productService->getById($id);

        return ApiResponse::success(
            data: ProductResource::make($product),
        );
    }

    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $product = $this->productService->update($request->validated(), $id);

        return ApiResponse::success(
            data: ProductResource::make($product),
        );
    }

    public function deactivate(int $id): JsonResponse
    {
        $product = $this->productService->deactivate($id);

        return ApiResponse::success(
            data: ProductResource::make($product),
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $this->productService->delete($id);

        return ApiResponse::success();
    }
}
