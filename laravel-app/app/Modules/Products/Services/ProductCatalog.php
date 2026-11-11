<?php

namespace App\Modules\Products\Services;

use App\Modules\Products\Models\Product;
use Illuminate\Support\Collection;

/**
 * واجهة وحدة المنتجات لباقي الوحدات (مثل الطلبات).
 * وحدة الطلبات تسأل هذه الخدمة ولا تستعلم جدول products بنفسها.
 */
class ProductCatalog
{
    /**
     * يجلب عدة منتجات باستعلام واحد (بدل استعلام لكل منتج = مشكلة N+1).
     * يقرأ من القاعدة الأساسية دائماً: السعر الذي نحاسب به الزبون يجب أن يكون الأحدث.
     *
     * @param  array<int,int>  $ids
     * @return Collection<int,Product> مفهرسة بالـ id
     */
    public function findManyForOrder(array $ids): Collection
    {
        return Product::query()
            ->useWritePdo()
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');
    }
}
