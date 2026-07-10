<?php

namespace App\Services;

use App\Exceptions\ApiBusinessException;
use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

readonly class ProductService
{
    public function __construct(
        private ProductRepository $productRepository,
    ) {}

    public function create(array $data): Product
    {
        return $this->productRepository->create($data);
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->productRepository->paginate($filters);
    }

    public function getById(int $id): Product
    {
        $product = $this->productRepository->findByIdOrFail($id);

        $product->setAttribute(
            'total_quantity',
            $this->productRepository->getTotalQuantity($product),
        );

        return $this->productRepository->loadStockBalances($product);
    }

    public function update(array $data, int $id): Product
    {
        $product = $this->productRepository->findByIdOrFail($id);

        return $this->productRepository->update($product, $data);
    }

    public function deactivate(int $id): Product
    {
        $product = $this->productRepository->findByIdOrFail($id);

        if ($this->productRepository->hasPositiveStock($product)) {
            throw new ApiBusinessException(
                errorCode: 'PRODUCT_HAS_STOCK',
                message: 'Product has stock and cannot be deactivated.',
            );
        }

        return $this->productRepository->update(
            $product,
            [
                'is_active' => false,
            ]
        );
    }

    public function delete(int $id): void
    {
        $product = $this->productRepository->findByIdOrFail($id);

        if ($this->productRepository->hasPositiveStock($product)) {
            throw new ApiBusinessException(
                errorCode: 'PRODUCT_CANNOT_BE_DELETED',
                message: 'Product has stock and cannot be deleted.',
            );
        }

        if ($this->productRepository->hasMovements($product)) {
            throw new ApiBusinessException(
                errorCode: 'PRODUCT_CANNOT_BE_DELETED',
                message: 'Product has movements and cannot be deleted.',
            );
        }

        $this->productRepository->delete($product);
    }
}
