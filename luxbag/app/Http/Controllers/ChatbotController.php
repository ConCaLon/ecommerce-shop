<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    // ─── Intent Keywords ─────────────────────────────────────────────
    private array $bagStyleKeywords = [
        'công sở'   => ['công sở', 'văn phòng', 'office', 'làm việc', 'đi làm'],
        'du lịch'   => ['du lịch', 'đi chơi', 'dã ngoại', 'travel', 'picnic'],
        'dạ tiệc'   => ['dạ tiệc', 'tiệc', 'party', 'sự kiện', 'event', 'đám cưới', 'gala'],
        'đi học'    => ['đi học', 'học sinh', 'sinh viên', 'school', 'campus'],
        'thể thao'  => ['thể thao', 'gym', 'sport', 'tập luyện'],
        'mini'      => ['mini', 'nhỏ', 'xinh', 'compact', 'bé'],
        'to'        => ['to', 'lớn', 'rộng', 'big', 'large'],
        'da'        => ['da thật', 'da bò', 'da cá sấu', 'leather', 'genuine', 'da thật'],
    ];

    private array $bagTypeKeywords = [
        'tote'      => ['tote', 'túi tote'],
        'clutch'    => ['clutch', 'ví cầm tay', 'cầm tay'],
        'crossbody' => ['crossbody', 'đeo chéo', 'chéo'],
        'backpack'  => ['backpack', 'ba lô', 'balô', 'balo'],
        'shoulder'  => ['shoulder', 'đeo vai', 'qua vai'],
        'bucket'    => ['bucket', 'bucket bag', 'túi xô'],
        'hobo'      => ['hobo', 'túi hobo'],
        'satchel'   => ['satchel', 'túi hộp'],
        'wallet'    => ['ví', 'wallet', 'ví da'],
    ];

    private array $greetingKeywords = [
        'xin chào', 'hello', 'hi', 'chào', 'hey',
        'chào bạn', 'alo', 'good morning', 'good evening',
    ];

    private array $thankKeywords = [
        'cảm ơn', 'thanks', 'thank you', 'cám ơn', 'tks', 'thk',
    ];

    private array $helpKeywords = [
        'giúp', 'tư vấn', 'gợi ý', 'suggest', 'help', 'recommend',
        'muốn', 'cần', 'tìm', 'kiếm', 'mua',
    ];

    // ─── Main Handler ─────────────────────────────────────────────────
    public function message(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $enabled = Setting::get('chatbot_enabled', '1');
        if (!$enabled) {
            return response()->json(['message' => 'Chatbot hiện không hoạt động.', 'products' => []]);
        }

        $userMsg   = trim($request->input('message'));
        $lower     = mb_strtolower($userMsg, 'UTF-8');
        $maxItems  = (int) Setting::get('chatbot_max_products', 3);
        $botName   = Setting::get('chatbot_name', 'LuxBot');

        // ── Greeting ──────────────────────────────────────────────────
        if ($this->matchesAny($lower, $this->greetingKeywords)) {
            return response()->json([
                'message'  => Setting::get('chatbot_greeting', 'Xin chào! Tôi có thể giúp bạn tìm túi xách phù hợp. Hãy cho tôi biết ngân sách hoặc loại túi bạn muốn nhé!'),
                'products' => [],
                'tips'     => $this->getQuickReplies(),
            ]);
        }

        // ── Thank you ─────────────────────────────────────────────────
        if ($this->matchesAny($lower, $this->thankKeywords)) {
            return response()->json([
                'message'  => 'Rất vui được giúp bạn! 😊 Nếu bạn cần tư vấn thêm, cứ hỏi tôi nhé!',
                'products' => [],
            ]);
        }

        // ── Extract intent ────────────────────────────────────────────
        $budget   = $this->extractBudget($lower);
        $style    = $this->extractStyle($lower);
        $bagType  = $this->extractBagType($lower);
        $brand    = $this->extractBrand($lower);
        $category = $this->extractCategory($lower);
        $material = $this->extractMaterial($lower);
        $color    = $this->extractColor($lower);

        // Nếu không tìm được intent rõ ràng
        $hasIntent = $budget || $style || $bagType || $brand || $category;
        if (!$hasIntent && !$this->matchesAny($lower, $this->helpKeywords)) {
            return response()->json([
                'message'  => 'Tôi có thể tư vấn túi xách cho bạn! Hãy cho tôi biết:
📦 **Loại túi**: tote, clutch, đeo chéo, ba lô...
💰 **Ngân sách**: ví dụ "dưới 1 triệu", "tầm 2-3 triệu"
🎨 **Phong cách**: công sở, đi chơi, dạ tiệc...
🏷️ **Thương hiệu** yêu thích',
                'products' => [],
                'tips'     => $this->getQuickReplies(),
            ]);
        }

        // ── Query products ────────────────────────────────────────────
        $query = Product::query()
            ->with(['primaryImage', 'images', 'category', 'brand'])
            ->active()
            ->inStock();

        // Filter by budget
        if ($budget) {
            if ($budget['min'] > 0) {
                $query->where('price', '>=', $budget['min']);
            }
            if ($budget['max'] > 0) {
                $query->where('price', '<=', $budget['max']);
            }
        }

        // Filter by brand
        if ($brand) {
            $query->whereHas('brand', function ($q) use ($brand) {
                $q->where('name', 'LIKE', "%{$brand}%");
            });
        }

        // Filter by category slug or name
        if ($category) {
            $query->whereHas('category', function ($q) use ($category) {
                $q->where('name', 'LIKE', "%{$category}%")
                  ->orWhere('slug', 'LIKE', "%{$category}%");
            });
        }

        // Filter by bag type (keyword search in name/description)
        if ($bagType) {
            $query->where(function ($q) use ($bagType) {
                $q->where('name', 'LIKE', "%{$bagType}%")
                  ->orWhere('description', 'LIKE', "%{$bagType}%")
                  ->orWhere('short_description', 'LIKE', "%{$bagType}%");
            });
        }

        // Filter by material
        if ($material) {
            $query->where(function ($q) use ($material) {
                $q->where('material', 'LIKE', "%{$material}%")
                  ->orWhere('name', 'LIKE', "%{$material}%")
                  ->orWhere('description', 'LIKE', "%{$material}%");
            });
        }

        // Filter by style (search in name/description)
        if ($style) {
            $query->where(function ($q) use ($style) {
                $q->where('name', 'LIKE', "%{$style}%")
                  ->orWhere('description', 'LIKE', "%{$style}%")
                  ->orWhere('short_description', 'LIKE', "%{$style}%");
            });
        }

        $products = $query->orderByDesc('avg_rating')
                          ->orderByDesc('sold_count')
                          ->orderByDesc('is_featured')
                          ->limit($maxItems)
                          ->get();

        // Nếu không tìm được với filter đầy đủ, thử giảm bớt
        if ($products->isEmpty() && ($bagType || $style || $material)) {
            $products = Product::query()
                ->with(['primaryImage', 'images', 'category', 'brand'])
                ->active()
                ->inStock()
                ->when($budget, function ($q) use ($budget) {
                    if ($budget['min'] > 0) $q->where('price', '>=', $budget['min']);
                    if ($budget['max'] > 0) $q->where('price', '<=', $budget['max']);
                })
                ->when($brand, function ($q) use ($brand) {
                    $q->whereHas('brand', fn($b) => $b->where('name', 'LIKE', "%{$brand}%"));
                })
                ->orderByDesc('avg_rating')
                ->orderByDesc('sold_count')
                ->limit($maxItems)
                ->get();
        }

        // ── Build response message ─────────────────────────────────────
        $message = $this->buildResponseMessage($products, $budget, $style, $bagType, $brand, $category);

        return response()->json([
            'message'  => $message,
            'products' => $products->map(fn($p) => $this->formatProduct($p)),
            'tips'     => $products->isEmpty() ? $this->getQuickReplies() : [],
        ]);
    }

    // ─── Budget Extraction ────────────────────────────────────────────
    private function extractBudget(string $text): ?array
    {
        $text = str_replace([' ', ','], ['', ''], $text);

        // Chuẩn hóa đơn vị
        $text = preg_replace_callback('/(\d+(?:\.\d+)?)\s*(tr|triệu|trieu|m)/u', function ($m) {
            return (int)($m[1] * 1_000_000);
        }, $text);
        $text = preg_replace_callback('/(\d+(?:\.\d+)?)\s*(k|nghìn|nghin|nghiền|ng)/u', function ($m) {
            return (int)($m[1] * 1_000);
        }, $text);

        $nums = [];
        preg_match_all('/\d{4,}/', $text, $matches);
        foreach ($matches[0] as $n) {
            $nums[] = (int)$n;
        }

        if (empty($nums)) return null;

        sort($nums);

        // Khoảng giá "từ X đến Y" hoặc "X - Y"
        if (count($nums) >= 2) {
            return ['min' => $nums[0], 'max' => $nums[count($nums) - 1]];
        }

        $val = $nums[0];

        // Từ khoá xác định max/min
        if (preg_match('/dưới|tối đa|không quá|dưới|max|under|below/', $text)) {
            return ['min' => 0, 'max' => $val];
        }
        if (preg_match('/trên|từ|ít nhất|min|over|above/', $text)) {
            return ['min' => $val, 'max' => 0];
        }
        if (preg_match('/tầm|khoảng|around|about/', $text)) {
            return ['min' => (int)($val * 0.8), 'max' => (int)($val * 1.2)];
        }

        // Default: xem như "tầm"
        return ['min' => (int)($val * 0.7), 'max' => (int)($val * 1.3)];
    }

    // ─── Style / Occasion Extraction ──────────────────────────────────
    private function extractStyle(string $text): ?string
    {
        foreach ($this->bagStyleKeywords as $style => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($text, $kw)) {
                    return $style;
                }
            }
        }
        return null;
    }

    // ─── Bag Type Extraction ──────────────────────────────────────────
    private function extractBagType(string $text): ?string
    {
        foreach ($this->bagTypeKeywords as $type => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($text, $kw)) {
                    return $type;
                }
            }
        }
        return null;
    }

    // ─── Brand Extraction ─────────────────────────────────────────────
    private function extractBrand(string $text): ?string
    {
        $brands = Brand::active()->pluck('name')->toArray();
        foreach ($brands as $brand) {
            if (str_contains($text, mb_strtolower($brand, 'UTF-8'))) {
                return $brand;
            }
        }
        return null;
    }

    // ─── Category Extraction ──────────────────────────────────────────
    private function extractCategory(string $text): ?string
    {
        $categories = Category::active()->pluck('name')->toArray();
        foreach ($categories as $cat) {
            if (str_contains($text, mb_strtolower($cat, 'UTF-8'))) {
                return $cat;
            }
        }
        return null;
    }

    // ─── Material Extraction ──────────────────────────────────────────
    private function extractMaterial(string $text): ?string
    {
        $materials = ['da thật', 'da bò', 'da cá sấu', 'vải canvas', 'canvas', 'da pu', 'da tổng hợp', 'nylon', 'lưới'];
        foreach ($materials as $m) {
            if (str_contains($text, $m)) {
                return $m;
            }
        }
        return null;
    }

    // ─── Color Extraction ─────────────────────────────────────────────
    private function extractColor(string $text): ?string
    {
        $colors = ['đen', 'trắng', 'nâu', 'kem', 'xanh', 'đỏ', 'hồng', 'vàng', 'xám', 'be', 'nude', 'camel', 'navy'];
        foreach ($colors as $c) {
            if (str_contains($text, $c)) return $c;
        }
        return null;
    }

    // ─── Helpers ──────────────────────────────────────────────────────
    private function matchesAny(string $text, array $keywords): bool
    {
        foreach ($keywords as $kw) {
            if (str_contains($text, $kw)) return true;
        }
        return false;
    }

    private function buildResponseMessage($products, $budget, $style, $bagType, $brand, $category): string
    {
        if ($products->isEmpty()) {
            $parts = [];
            if ($budget) {
                $min = $budget['min'] > 0 ? number_format($budget['min'], 0, ',', '.') . 'đ' : '';
                $max = $budget['max'] > 0 ? number_format($budget['max'], 0, ',', '.') . 'đ' : '';
                if ($min && $max) $parts[] = "ngân sách **{$min} - {$max}**";
                elseif ($max)    $parts[] = "ngân sách **dưới {$max}**";
                elseif ($min)   $parts[] = "ngân sách **từ {$min}**";
            }
            if ($bagType) $parts[] = "loại **{$bagType}**";
            if ($style)   $parts[] = "phong cách **{$style}**";
            if ($brand)   $parts[] = "thương hiệu **{$brand}**";
            if ($category) $parts[] = "danh mục **{$category}**";

            $filter = !empty($parts) ? ' với ' . implode(', ', $parts) : '';
            return "😅 Hiện tôi chưa tìm thấy sản phẩm phù hợp{$filter}.\n\nBạn có thể thử lại với ngân sách rộng hơn hoặc loại túi khác nhé!";
        }

        $count = $products->count();
        $parts = [];
        if ($budget) {
            $min = $budget['min'] > 0 ? number_format($budget['min'], 0, ',', '.') . 'đ' : '';
            $max = $budget['max'] > 0 ? number_format($budget['max'], 0, ',', '.') . 'đ' : '';
            if ($min && $max) $parts[] = "ngân sách **{$min} - {$max}**";
            elseif ($max)    $parts[] = "ngân sách **dưới {$max}**";
            elseif ($min)   $parts[] = "ngân sách **từ {$min}**";
        }
        if ($bagType) $parts[] = "loại **{$bagType}**";
        if ($style)   $parts[] = "phong cách **{$style}**";
        if ($brand)   $parts[] = "thương hiệu **{$brand}**";
        if ($category) $parts[] = "danh mục **{$category}**";

        $filter = !empty($parts) ? ' phù hợp với ' . implode(', ', $parts) : ' nổi bật';
        return "✨ Tôi tìm thấy **{$count} mẫu túi**{$filter} cho bạn:";
    }

    private function formatProduct(Product $product): array
    {
        return [
            'id'          => $product->id,
            'name'        => $product->name,
            'price'       => $product->formatted_price,
            'sale_price'  => $product->is_on_sale ? number_format($product->sale_price, 0, ',', '.') . 'đ' : null,
            'is_on_sale'  => $product->is_on_sale,
            'discount'    => $product->discount_percent,
            'image'       => $product->thumbnail,
            'url'         => route('products.show', $product->slug),
            'rating'      => $product->avg_rating,
            'brand'       => $product->brand?->name,
            'category'    => $product->category?->name,
        ];
    }

    private function getQuickReplies(): array
    {
        return [
            'Túi dưới 500.000đ',
            'Túi công sở',
            'Túi đeo chéo',
            'Túi tầm 1-2 triệu',
            'Túi dạ tiệc',
            'Ba lô thời trang',
        ];
    }
}
