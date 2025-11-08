<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\User\UserRegisterAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class RegisteredUserController extends Controller
{
    public function __construct(
        private readonly UserRegisterAction $userRegisterAction,
    ) {}

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $this->userRegisterAction->handle(
            firstName: $request->input('first_name'),
            lastName: $request->input('last_name'),
            email: $request->input('email'),
            password: $request->input('password'),
        );

        return redirect('/');
    }
}
