<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Validator;

class UserController extends Controller
{
    /**
     * @return array
     */
    protected function validationRules($user)
    {
        return [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'min:1',
        ];
    }

    public function getSearch(Request $request)
    {
        $search = $request->get('search');
        $exceptIds = $request->get('exceptIds');

        $query = null;

        if ($search) {
            $query = User::where(function ($query) use ($search) {
                $query = $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");

                return $query;
            });
        } else {
            $query = User::take(100)->orderBy('rating', 'desc');
        }

        if ($exceptIds)
            $query = $query->whereNotIn('id', $exceptIds);

        $users = $query->get();

        return response()->json($users);
    }

    public function getRole(Request $request)
    {
        $user = $request->user();
        $role = User::ROLE_GUEST;

        if ($user) {
            $role = $user->isAdmin() ? User::ROLE_ADMIN : User::ROLE_USER;
        }

        return response()->json($role);
    }

    public function getOne(Request $request)
    {
        $user = User::findMe();

        if ($request->wantsJson()) {
            return response()->json($user);
        }
    }

    public function getIndex(Request $request)
    {
        $users = User::paginate(1000);

        if ($request->wantsJson()) {
            return response()->json($users);
        }
    }

    public function putUpdate(Request $request, $id)
    {
        $user = User::where('id', $id)->first();

        if (!$user)
            abort(404);

        $this->validate($request, $this->validationRules($user));
        $user->fill($request->all());
        $user->save();

        if ($request->wantsJson()) {
            return response()->json($user);
        }
    }
}