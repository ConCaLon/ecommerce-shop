<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Banner;
use App\Models\ShippingMethod;
use App\Models\Setting;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Roles ────────────────────────────────────────────────
        $roles = ['super-admin', 'admin', 'manager', 'staff', 'customer'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // ─── Permissions ──────────────────────────────────────────
        $permissions = [
            'manage-products', 'manage-categories', 'manage-brands',
            'manage-orders', 'manage-customers', 'manage-coupons',
            'manage-banners', 'manage-reports', 'manage-settings', 'manage-reviews',
            'manage-staff',
        ];
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // super-admin & admin: toàn quyền
        Role::findByName('super-admin')->syncPermissions(Permission::all());
        Role::findByName('admin')->syncPermissions(Permission::all());

        // manager: toàn quyền (bao gồm quản lý nhân viên)
        Role::findByName('manager')->syncPermissions(Permission::all());

        // staff: chỉ quản lý sản phẩm, danh mục, thương hiệu, đơn hàng, đánh giá
        Role::findByName('staff')->syncPermissions([
            'manage-products',
            'manage-categories',
            'manage-brands',
            'manage-orders',
            'manage-reviews',
        ]);

        // ─── Admin User ───────────────────────────────────────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@luxbag.vn'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('Admin@123456'),
                'phone' => '0901234567',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('super-admin');

        // Demo customer
        $customer = User::firstOrCreate(
            ['email' => 'khachhang@example.com'],
            [
                'name' => 'Nguyễn Văn An',
                'password' => Hash::make('Password@123'),
                'phone' => '0987654321',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        $customer->assignRole('customer');

        // ─── Categories ───────────────────────────────────────────
        $catData = [
            ['name' => 'Túi Xách Nữ', 'slug' => 'tui-xach-nu'],
            ['name' => 'Túi Xách Nam', 'slug' => 'tui-xach-nam'],
            ['name' => 'Ba Lô', 'slug' => 'ba-lo'],
            ['name' => 'Ví Da', 'slug' => 'vi-da'],
            ['name' => 'Túi Công Sở', 'slug' => 'tui-cong-so'],
            ['name' => 'Túi Du Lịch', 'slug' => 'tui-du-lich'],
        ];

        $categories = [];
        foreach ($catData as $cat) {
            $categories[$cat['slug']] = Category::firstOrCreate(
                ['slug' => $cat['slug']],
                array_merge($cat, ['status' => 'active'])
            );
        }

        // Sub-categories
        $subCats = [
            ['name' => 'Túi Tote', 'slug' => 'tui-tote', 'parent' => 'tui-xach-nu'],
            ['name' => 'Túi Clutch', 'slug' => 'tui-clutch', 'parent' => 'tui-xach-nu'],
            ['name' => 'Túi Shoulder', 'slug' => 'tui-shoulder', 'parent' => 'tui-xach-nu'],
            ['name' => 'Ba Lô Nam', 'slug' => 'ba-lo-nam', 'parent' => 'ba-lo'],
            ['name' => 'Ba Lô Nữ', 'slug' => 'ba-lo-nu', 'parent' => 'ba-lo'],
        ];
        foreach ($subCats as $sub) {
            Category::firstOrCreate(
                ['slug' => $sub['slug']],
                [
                    'name' => $sub['name'],
                    'slug' => $sub['slug'],
                    'parent_id' => $categories[$sub['parent']]->id,
                    'status' => 'active',
                ]
            );
        }

        // ─── Brands ───────────────────────────────────────────────
        $brands = [
            ['name' => 'LuxBag Original', 'slug' => 'luxbag-original'],
            ['name' => 'Hera Bags', 'slug' => 'hera-bags'],
            ['name' => 'Viva Style', 'slug' => 'viva-style'],
            ['name' => 'Urban Carry', 'slug' => 'urban-carry'],
            ['name' => 'Elegance', 'slug' => 'elegance'],
        ];
        $brandModels = [];
        foreach ($brands as $brand) {
            $brandModels[$brand['slug']] = Brand::firstOrCreate(
                ['slug' => $brand['slug']],
                array_merge($brand, ['status' => 'active'])
            );
        }

        // ─── Products ─────────────────────────────────────────────
        $products = [
            [
                'name' => 'Túi Tote Da Bò Cao Cấp LuxBag',
                'slug' => 'tui-tote-da-bo-cao-cap-luxbag',
                'sku' => 'LB-TOTE-001',
                'category' => 'tui-xach-nu',
                'brand' => 'luxbag-original',
                'price' => 1850000,
                'sale_price' => 1480000,
                'stock' => 50,
                'material' => 'Da bò thật',
                'short_description' => 'Túi tote da bò thật cao cấp, thiết kế sang trọng phù hợp đi làm và đi chơi.',
                'description' => 'Túi tote da bò thật cao cấp với thiết kế tối giản nhưng sang trọng. Chất liệu da bò được xử lý đặc biệt, bền đẹp theo thời gian. Túi có ngăn lớn chứa laptop 13", ngăn phụ và túi nhỏ tiện lợi. Đây là lựa chọn hoàn hảo cho phụ nữ hiện đại.',
                'is_featured' => true,
                'status' => 'active',
                'colors' => ['Đen', 'Nâu', 'Kem'],
                'sizes' => ['Nhỏ', 'Vừa', 'Lớn'],
            ],
            [
                'name' => 'Túi Clutch Da PU Thời Trang',
                'slug' => 'tui-clutch-da-pu-thoi-trang',
                'sku' => 'LB-CLUTCH-001',
                'category' => 'tui-xach-nu',
                'brand' => 'hera-bags',
                'price' => 650000,
                'sale_price' => null,
                'stock' => 80,
                'material' => 'Da PU cao cấp',
                'short_description' => 'Túi clutch thanh lịch, phù hợp dự tiệc và đi chơi buổi tối.',
                'description' => 'Túi clutch da PU cao cấp với thiết kế thanh lịch và sang trọng. Phù hợp dự tiệc, đám cưới, đi chơi buổi tối. Có dây đeo tháo rời tiện lợi.',
                'is_featured' => true,
                'status' => 'active',
                'colors' => ['Đen', 'Đỏ', 'Vàng Kim'],
                'sizes' => ['Standard'],
            ],
            [
                'name' => 'Ba Lô Laptop Chống Nước Urban',
                'slug' => 'ba-lo-laptop-chong-nuoc-urban',
                'sku' => 'LB-BALO-001',
                'category' => 'ba-lo',
                'brand' => 'urban-carry',
                'price' => 1250000,
                'sale_price' => 980000,
                'stock' => 120,
                'material' => 'Vải canvas chống nước',
                'short_description' => 'Ba lô laptop 15.6", chống nước, nhiều ngăn tiện lợi.',
                'description' => 'Ba lô laptop chống nước thiết kế hiện đại với ngăn chứa laptop 15.6", ngăn phụ, ngăn USB sạc ngoài. Chất liệu canvas cao cấp, bền đẹp.',
                'is_featured' => true,
                'status' => 'active',
                'colors' => ['Đen', 'Xám', 'Navy'],
                'sizes' => ['Standard'],
            ],
            [
                'name' => 'Túi Xách Công Sở Da Thật Elegance',
                'slug' => 'tui-xach-cong-so-da-that-elegance',
                'sku' => 'LB-CS-001',
                'category' => 'tui-cong-so',
                'brand' => 'elegance',
                'price' => 2200000,
                'sale_price' => 1750000,
                'stock' => 30,
                'material' => 'Da thật',
                'short_description' => 'Túi công sở da thật, phong cách chuyên nghiệp.',
                'description' => 'Túi xách công sở da thật cao cấp với thiết kế chuyên nghiệp. Đủ rộng chứa tài liệu A4, laptop 13".',
                'is_featured' => false,
                'status' => 'active',
                'colors' => ['Đen', 'Nâu'],
                'sizes' => ['Standard'],
            ],
            [
                'name' => 'Ví Da Nam Dáng Dài Viva Style',
                'slug' => 'vi-da-nam-dang-dai-viva-style',
                'sku' => 'LB-VI-001',
                'category' => 'vi-da',
                'brand' => 'viva-style',
                'price' => 450000,
                'sale_price' => null,
                'stock' => 200,
                'material' => 'Da bò',
                'short_description' => 'Ví da nam dáng dài, nhiều ngăn đựng thẻ.',
                'description' => 'Ví da nam dáng dài cao cấp với nhiều ngăn đựng thẻ, ngăn tiền mặt rộng rãi. Da bò thật, bền đẹp.',
                'is_featured' => false,
                'status' => 'active',
                'colors' => ['Đen', 'Nâu', 'Nâu đỏ'],
                'sizes' => ['Standard'],
            ],
            [
                'name' => 'Túi Du Lịch Vali Mềm Hera',
                'slug' => 'tui-du-lich-vali-mem-hera',
                'sku' => 'LB-DL-001',
                'category' => 'tui-du-lich',
                'brand' => 'hera-bags',
                'price' => 890000,
                'sale_price' => 750000,
                'stock' => 45,
                'material' => 'Vải Oxford',
                'short_description' => 'Túi du lịch vali mềm dung tích lớn, chống thấm nước.',
                'description' => 'Túi du lịch vali mềm với dung tích 40L, chống thấm nước. Bánh xe xoay 360°, khóa số an toàn.',
                'is_featured' => true,
                'status' => 'active',
                'colors' => ['Đen', 'Xanh Navy', 'Đỏ'],
                'sizes' => ['20 inch', '24 inch', '28 inch'],
            ],
        ];

        foreach ($products as $data) {
            $product = Product::firstOrCreate(
                ['slug' => $data['slug']],
                [
                    'category_id' => $categories[$data['category']]->id,
                    'brand_id' => $brandModels[$data['brand']]->id,
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'sku' => $data['sku'],
                    'price' => $data['price'],
                    'sale_price' => $data['sale_price'],
                    'stock' => $data['stock'],
                    'material' => $data['material'],
                    'short_description' => $data['short_description'],
                    'description' => $data['description'],
                    'is_featured' => $data['is_featured'],
                    'status' => $data['status'],
                    'avg_rating' => rand(35, 50) / 10,
                    'review_count' => rand(5, 50),
                    'sold_count' => rand(10, 200),
                ]
            );

            // Create variants
            foreach ($data['colors'] as $color) {
                foreach ($data['sizes'] as $size) {
                    if ($size === 'Standard') {
                        ProductVariant::firstOrCreate(
                            ['product_id' => $product->id, 'color' => $color, 'size' => null],
                            ['stock' => rand(5, 30), 'status' => 'active']
                        );
                    } else {
                        ProductVariant::firstOrCreate(
                            ['product_id' => $product->id, 'size' => $size, 'color' => $color],
                            ['stock' => rand(5, 30), 'status' => 'active']
                        );
                    }
                }
            }
        }

        // ─── Shipping Methods ─────────────────────────────────────
        $shippingMethods = [
            ['name' => 'Giao hàng tiêu chuẩn', 'price' => 30000, 'estimated_days' => '3-5 ngày'],
            ['name' => 'Giao hàng nhanh', 'price' => 50000, 'estimated_days' => '1-2 ngày'],
            ['name' => 'Giao hàng hỏa tốc', 'price' => 80000, 'estimated_days' => 'Trong ngày'],
            ['name' => 'Miễn phí vận chuyển', 'price' => 0, 'estimated_days' => '3-5 ngày'],
        ];
        foreach ($shippingMethods as $method) {
            ShippingMethod::firstOrCreate(['name' => $method['name']], $method);
        }

        // ─── Settings ─────────────────────────────────────────────
        $settings = [
            ['key' => 'site_name', 'value' => 'LuxBag', 'group' => 'general'],
            ['key' => 'site_tagline', 'value' => 'Túi Xách Cao Cấp - Phong Cách Đẳng Cấp', 'group' => 'general'],
            ['key' => 'site_email', 'value' => 'contact@luxbag.vn', 'group' => 'general'],
            ['key' => 'site_phone', 'value' => '+84 377277542', 'group' => 'general'],
            ['key' => 'site_address', 'value' => '123 Đường Lê Lợi, Quận 1, TP.HCM', 'group' => 'general'],
            ['key' => 'free_shipping_threshold', 'value' => '500000', 'group' => 'shipping'],
            ['key' => 'order_email_notification', 'value' => '1', 'group' => 'email'],
            ['key' => 'facebook_url', 'value' => 'https://facebook.com/luxbag', 'group' => 'social'],
            ['key' => 'instagram_url', 'value' => 'https://instagram.com/luxbag', 'group' => 'social'],
        ];
        foreach ($settings as $setting) {
            Setting::firstOrCreate(['key' => $setting['key']], $setting);
        }

        $this->command->info('✅ LuxBag seeding hoàn tất!');
        $this->command->info('👤 Admin: admin@luxbag.vn / Admin@123456');
        $this->command->info('👤 Customer: khachhang@example.com / Password@123');
    }
}
