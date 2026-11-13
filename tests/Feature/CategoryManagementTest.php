<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can create a child category under a parent', function () {
    $admin = User::factory()->admin()->create();
    $root = Category::query()->create([
        'name' => 'Mesin',
        'slug' => 'mesin',
        'sort_order' => 1,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.categories.store'), [
            'name' => 'Oli Mesin',
            'parent_id' => $root->id,
            'sort_order' => 2,
        ])
        ->assertRedirect(route('admin.categories.index'));

    $child = Category::query()->where('name', 'Oli Mesin')->firstOrFail();

    expect((int) $child->parent_id)->toBe((int) $root->id)
        ->and($child->sort_order)->toBe(2);
});

test('category parent cannot be its own descendant', function () {
    $admin = User::factory()->admin()->create();
    $root = Category::query()->create([
        'name' => 'Mesin',
        'slug' => 'mesin',
        'sort_order' => 1,
    ]);
    $child = Category::query()->create([
        'name' => 'Oli Mesin',
        'slug' => 'oli-mesin',
        'parent_id' => $root->id,
        'sort_order' => 1,
    ]);

    $this->actingAs($admin)
        ->put(route('admin.categories.update', $root), [
            'name' => 'Mesin',
            'parent_id' => $child->id,
            'sort_order' => 1,
        ])
        ->assertSessionHasErrors('parent_id');

    expect($root->fresh()->parent_id)->toBeNull();
});

test('category name must be unique within the same parent', function () {
    $admin = User::factory()->admin()->create();
    $root = Category::query()->create([
        'name' => 'Mesin',
        'slug' => 'mesin',
        'sort_order' => 1,
    ]);

    Category::query()->create([
        'name' => 'Oli Mesin',
        'slug' => 'oli-mesin',
        'parent_id' => $root->id,
        'sort_order' => 1,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.categories.store'), [
            'name' => 'Oli Mesin',
            'parent_id' => $root->id,
            'sort_order' => 2,
        ])
        ->assertSessionHasErrors('name');
});
