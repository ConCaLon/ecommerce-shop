<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand', 'primaryImage']);

        if ($request->filled('search')) {
            $query->where('name', 'LIKE', "%{$request->search}%")
                ->orWhere('sku', 'LIKE', "%{$request->search}%");
        }
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $products = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $categories = Category::active()->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::active()->get();
        $brands = Brand::active()->get();
        return view('admin.products.create', compact('categories', 'brands'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'category_id'       => 'required|exists:categories,id',
            'brand_id'          => 'nullable|exists:brands,id',
            'price'             => 'required|numeric|min:0',
            'sale_price'        => 'nullable|numeric|min:0|lt:price',
            'stock'             => 'required|integer|min:0',
            'status'            => 'required|in:active,inactive,draft',
            'description'       => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'material'          => 'nullable|string|max:100',
            'weight'            => 'nullable|numeric|min:0',
            'is_featured'       => 'boolean',
            'sku'               => 'nullable|string|max:100|unique:products,sku',
            'meta_title'        => 'nullable|string|max:200',
            'meta_description'  => 'nullable|string',
            'images'            => 'nullable|array',
            'images.*'          => 'image|mimes:jpg,jpeg,png,webp|max:3072',
        ]);

        $validated['slug'] = $this->uniqueSlug($request->name);
        if (empty($validated['sku'])) {
            $validated['sku'] = 'LB-' . strtoupper(Str::random(8));
        }
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_new'] = true;

        // Ensure empty sale_price is stored as null, not 0
        if (empty($validated['sale_price'])) {
            $validated['sale_price'] = null;
        }

        $product = Product::create($validated);

        // Handle image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $imageFile) {
                $filename = $this->processAndStoreImage($imageFile);
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $filename,
                    'is_primary' => $index === 0,
                    'sort_order' => $index,
                ]);
            }
        }

        return redirect()->route('admin.products.edit', $product)
            ->with('success', "Sản phẩm \"{$product->name}\" đã được tạo thành công.");
    }

    public function edit(Product $product)
    {
        $product->load(['images', 'variants', 'category', 'brand']);
        $categories = Category::active()->get();
        $brands = Brand::active()->get();
        return view('admin.products.edit', compact('product', 'categories', 'brands'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'category_id'       => 'required|exists:categories,id',
            'brand_id'          => 'nullable|exists:brands,id',
            'price'             => 'required|numeric|min:0',
            'sale_price'        => 'nullable|numeric|min:0',
            'stock'             => 'required|integer|min:0',
            'status'            => 'required|in:active,inactive,draft',
            'description'       => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'material'          => 'nullable|string|max:100',
            'is_featured'       => 'boolean',
            'meta_title'        => 'nullable|string|max:200',
            'meta_description'  => 'nullable|string',
        ]);

        $validated['is_featured'] = $request->boolean('is_featured');

        // Ensure empty sale_price is stored as null, not 0
        if (empty($validated['sale_price'])) {
            $validated['sale_price'] = null;
        }

        $product->update($validated);

        // Handle new image uploads
        if ($request->hasFile('images')) {
            $lastOrder = $product->images()->max('sort_order') ?? -1;
            foreach ($request->file('images') as $index => $imageFile) {
                $filename = $this->processAndStoreImage($imageFile);
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $filename,
                    'is_primary' => !$product->images()->exists(),
                    'sort_order' => $lastOrder + $index + 1,
                ]);
            }
        }

        return back()->with('success', 'Sản phẩm đã được cập nhật.');
    }

    public function destroy(Product $product)
    {
        // Delete images from storage
        foreach ($product->images as $image) {
            Storage::disk('public')->delete('products/' . $image->image_path);
        }
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Sản phẩm đã được xóa.');
    }

    public function toggleStatus(Product $product)
    {
        $product->update(['status' => $product->status === 'active' ? 'inactive' : 'active']);
        return response()->json(['success' => true, 'status' => $product->status]);
    }

    public function uploadImages(Request $request, Product $product)
    {
        $request->validate(['images.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:3072']);

        $lastOrder = $product->images()->max('sort_order') ?? -1;
        $uploaded = [];

        foreach ($request->file('images') as $index => $imageFile) {
            $filename = $this->processAndStoreImage($imageFile);
            $image = ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $filename,
                'is_primary' => !$product->images()->exists() && $index === 0,
                'sort_order' => $lastOrder + $index + 1,
            ]);
            $uploaded[] = ['id' => $image->id, 'url' => $image->url];
        }

        return response()->json(['success' => true, 'images' => $uploaded]);
    }

    public function destroyImage(ProductImage $image)
    {
        Storage::disk('public')->delete('products/' . $image->image_path);
        $image->delete();
        return response()->json(['success' => true]);
    }

    public function setPrimaryImage(ProductImage $image)
    {
        ProductImage::where('product_id', $image->product_id)->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);
        return response()->json(['success' => true]);
    }

    public function storeVariant(Request $request, Product $product)
    {
        $request->validate([
            'size'           => 'nullable|string|max:20',
            'color'          => 'nullable|string|max:50',
            'color_hex'      => 'nullable|string|max:7',
            'stock'          => 'required|integer|min:0',
            'price_modifier' => 'nullable|numeric',
        ]);

        $variant = ProductVariant::create(array_merge(
            $request->only('size', 'color', 'color_hex', 'stock', 'price_modifier'),
            ['product_id' => $product->id]
        ));

        return response()->json(['success' => true, 'variant' => $variant]);
    }

    public function destroyVariant(ProductVariant $variant)
    {
        $variant->delete();
        return response()->json(['success' => true]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:products,id']);
        Product::whereIn('id', $request->ids)->delete();
        return response()->json(['success' => true]);
    }

    // ─── Private Helpers ──────────────────────────────────────────
    private function processAndStoreImage($file): string
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $useWebp = function_exists('imagewebp');
        $filename = Str::random(20) . '-' . time() . ($useWebp ? '.webp' : '.jpg');

        // Read source image
        $mime = $file->getMimeType();
        $srcPath = $file->getRealPath();

        if ($mime === 'image/jpeg' || $ext === 'jpg' || $ext === 'jpeg') {
            $src = imagecreatefromjpeg($srcPath);
        } elseif ($mime === 'image/png' || $ext === 'png') {
            $src = imagecreatefrompng($srcPath);
        } elseif ($mime === 'image/webp' || $ext === 'webp') {
            $src = imagecreatefromwebp($srcPath);
        } elseif ($mime === 'image/gif' || $ext === 'gif') {
            $src = imagecreatefromgif($srcPath);
        } else {
            // Fallback: copy as-is
            $fallbackName = Str::random(20) . '-' . time() . '.' . $ext;
            Storage::disk('public')->put('products/' . $fallbackName, file_get_contents($srcPath));
            return $fallbackName;
        }

        // Resize to max width 1200px maintaining aspect ratio
        $origW = imagesx($src);
        $origH = imagesy($src);
        if ($origW > 1200) {
            $newW = 1200;
            $newH = (int) round($origH * 1200 / $origW);
        } else {
            $newW = $origW;
            $newH = $origH;
        }

        $dst = imagecreatetruecolor($newW, $newH);
        // Preserve transparency for PNG
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefilledrectangle($dst, 0, 0, $newW, $newH, $transparent);

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
        imagedestroy($src);

        // Capture output
        ob_start();
        if ($useWebp) {
            imagewebp($dst, null, 85);
        } else {
            imagejpeg($dst, null, 90);
        }
        $imageData = ob_get_clean();
        imagedestroy($dst);

        Storage::disk('public')->put('products/' . $filename, $imageData);
        return $filename;
    }

    private function uniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $count = Product::where('slug', 'LIKE', "{$slug}%")->count();
        return $count ? "{$slug}-{$count}" : $slug;
    }
}
