<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name'           => fake()->name(),
            'phone'          => fake()->unique()->phoneNumber(), // استخدام الهاتف بدلاً من الإيميل
            'password'       => static::$password ??= Hash::make('password'),
            'area'           => fake()->city(),               // توليد منطقة عشوائية
            'shop_name'      => fake()->company() . ' Shop',  // توليد اسم محل عشوائي
            'address'        => fake()->address(),
            'role'           => 2,                            // القيمة الافتراضية للمستخدم العادي
            'activated'      => false,                        // الحساب غير مفعل افتراضياً
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * حالة خاصة لتوليد حساب "أدمن" مباشرة
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 1,
            'activated' => true,
        ]);
    }
}