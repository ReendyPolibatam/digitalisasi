<?php

use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
});

test('admin can view the user list', function () {
    $staff = User::factory()->create(['role' => 'staff']);

    $this->actingAs($this->admin)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('Kelola Pengguna')
        ->assertSee($staff->email);
});

test('staff cannot access user management', function () {
    $staff = User::factory()->create(['role' => 'staff']);

    $this->actingAs($staff)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

test('admin can create a user', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.users.store'), [
            'name' => 'Staff Baru',
            'email' => 'staff.baru@example.com',
            'role' => 'staff',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertRedirect(route('admin.users.index'));

    $user = User::where('email', 'staff.baru@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->role)->toBe('staff')
        ->and(Hash::check('password123', $user->password))->toBeTrue();
});

test('creating a user requires valid data', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.users.store'), [
            'name' => '',
            'email' => $this->admin->email,
            'role' => 'owner',
            'password' => 'password123',
            'password_confirmation' => 'different',
        ])
        ->assertSessionHasErrors(['name', 'email', 'role', 'password']);
});

test('admin can update a user without changing the password', function () {
    $staff = User::factory()->create(['role' => 'staff']);
    $oldPassword = $staff->password;

    $this->actingAs($this->admin)
        ->put(route('admin.users.update', $staff), [
            'name' => 'Nama Baru',
            'email' => $staff->email,
            'role' => 'admin',
            'password' => '',
            'password_confirmation' => '',
        ])
        ->assertRedirect(route('admin.users.index'));

    $staff->refresh();

    expect($staff->name)->toBe('Nama Baru')
        ->and($staff->role)->toBe('admin')
        ->and($staff->password)->toBe($oldPassword);
});

test('admin cannot demote their own account', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.users.update', $this->admin), [
            'name' => $this->admin->name,
            'email' => $this->admin->email,
            'role' => 'staff',
        ])
        ->assertSessionHas('error');

    expect($this->admin->fresh()->role)->toBe('admin');
});

test('admin can delete a user without documents', function () {
    $staff = User::factory()->create(['role' => 'staff']);

    $this->actingAs($this->admin)
        ->delete(route('admin.users.destroy', $staff))
        ->assertRedirect(route('admin.users.index'));

    $this->assertModelMissing($staff);
});

test('admin cannot delete themselves', function () {
    $this->actingAs($this->admin)
        ->delete(route('admin.users.destroy', $this->admin))
        ->assertSessionHas('error');

    $this->assertModelExists($this->admin);
});

test('admin cannot delete a user who still has documents', function () {
    $staff = User::factory()->create(['role' => 'staff']);
    Document::create([
        'file_name' => 'dokumen.pdf',
        'file_path' => 'documents/dokumen.pdf',
        'user_id' => $staff->id,
    ]);

    $this->actingAs($this->admin)
        ->delete(route('admin.users.destroy', $staff))
        ->assertSessionHas('error');

    $this->assertModelExists($staff);
});
