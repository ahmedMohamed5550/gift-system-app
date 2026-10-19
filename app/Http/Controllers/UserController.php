<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    protected function authorizeOwner(): void
    {
        abort_if(! auth()->user()?->isOwner(), 403, 'عذراً، إدارة المستخدمين متاحة فقط لمالك المتجر.');
    }

    public function index(Request $request): View
    {
        $this->authorizeOwner();

        $query = User::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        if ($request->has('status') && $request->input('status') !== '') {
            $query->where('is_active', (bool) $request->input('status'));
        }

        $users = $query->latest()->paginate(10)->withQueryString();

        $stats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'employees' => User::where('role', 'employee')->count(),
            'owners' => User::where('role', 'owner')->count(),
        ];

        return view('users.index', compact('users', 'stats'));
    }

    public function create(): View
    {
        $this->authorizeOwner();

        return view('users.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorizeOwner();

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => $request->boolean('is_active'),
        ]);

        \App\Services\ActivityLogService::log(
            action: 'user_created',
            subject: $user,
            description: "تم إنشاء حساب مستخدم جديد: {$user->name} ({$user->username}) بدور {$user->role_arabic}.",
            newValues: [
                'name' => $user->name,
                'username' => $user->username,
                'role' => $user->role,
                'is_active' => $user->is_active,
            ]
        );

        return redirect()->route('users.index')->with('success', 'تم إنشاء حساب المستخدم بنجاح.');
    }

    public function edit(User $user): View
    {
        $this->authorizeOwner();

        return view('users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorizeOwner();

        if (auth()->id() === $user->id && ! $request->boolean('is_active')) {
            return back()->with('error', 'لا يمكنك إلغاء تفعيل حسابك الحالي الذي تستخدمه لتسجيل الدخول.');
        }

        $oldValues = [
            'name' => $user->name,
            'username' => $user->username,
            'role' => $user->role,
            'is_active' => $user->is_active,
        ];

        $userData = [
            'name' => $request->name,
            'username' => $request->username,
            'role' => $request->role,
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        $newValues = [
            'name' => $user->name,
            'username' => $user->username,
            'role' => $user->role,
            'is_active' => $user->is_active,
            'password_changed' => $request->filled('password'),
        ];

        \App\Services\ActivityLogService::log(
            action: 'user_updated',
            subject: $user,
            description: "تم تحديث بيانات المستخدم: {$user->name} ({$user->username})." . ($request->filled('password') ? ' (تم تغيير كلمة المرور)' : ''),
            oldValues: $oldValues,
            newValues: $newValues
        );

        return redirect()->route('users.index')->with('success', 'تم تحديث بيانات المستخدم بنجاح.');
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        $this->authorizeOwner();

        if (auth()->id() === $user->id) {
            return back()->with('error', 'لا يمكنك تغيير حالة حسابك الحالي.');
        }

        $oldStatus = $user->is_active;
        $user->is_active = ! $user->is_active;
        $user->save();

        \App\Services\ActivityLogService::log(
            action: 'user_status_toggled',
            subject: $user,
            description: $user->is_active
                ? "تم تفعيل حساب المستخدم: {$user->name} ({$user->username})."
                : "تم تعطيل حساب المستخدم: {$user->name} ({$user->username}).",
            oldValues: ['is_active' => $oldStatus],
            newValues: ['is_active' => $user->is_active]
        );

        $message = $user->is_active ? 'تم تفعيل حساب المستخدم بنجاح.' : 'تم تعطيل حساب المستخدم بنجاح.';

        return back()->with('success', $message);
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorizeOwner();
        if (auth()->id() === $user->id) {
            return back()->with('error', 'لا يمكنك حذف حسابك الحالي.');
        }

        $oldValues = [
            'name' => $user->name,
            'username' => $user->username,
            'role' => $user->role,
            'is_active' => $user->is_active,
        ];

        $user->delete();

        \App\Services\ActivityLogService::log(
            action: 'user_deleted',
            subject: $user,
            description: "تم حذف حساب المستخدم: {$user->name} ({$user->username}) مع الحفاظ على السجلات المالية والتاريخية.",
            oldValues: $oldValues
        );

        return redirect()->route('users.index')->with('success', 'تم حذف حساب المستخدم بنجاح مع الحفاظ على السجلات المالية والتاريخية.');
    }
}

