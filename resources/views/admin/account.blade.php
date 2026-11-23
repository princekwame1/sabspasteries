<x-layouts.admin title="Your account">
    <form method="post" action="{{ route('admin.account') }}" class="card max-w-2xl space-y-4 p-6">
        @csrf @method('put')
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="label" for="name">Name</label>
                <input id="name" name="name" value="{{ old('name', $user->name) }}" required class="input" autocomplete="name">
                @error('name')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="email">Sign-in email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required class="input" autocomplete="username">
                @error('email')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="password">New password <span class="font-normal text-ink/40">(optional)</span></label>
                <input id="password" name="password" type="password" class="input" autocomplete="new-password">
                @error('password')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="password_confirmation">Repeat new password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" class="input" autocomplete="new-password">
            </div>
        </div>
        <div class="max-w-sm">
            <label class="label" for="current_password">Current password, to confirm</label>
            <input id="current_password" name="current_password" type="password" required class="input" autocomplete="current-password">
            @error('current_password')<p class="error">{{ $message }}</p>@enderror
        </div>
        <button class="btn-primary">Save</button>
    </form>
</x-layouts.admin>
