<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Obter dados do usuário autenticado
     */
    public function profile(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    /**
     * Atualizar perfil do usuário autenticado
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:users,email,' . $user->id],
            'username' => ['sometimes', 'string', 'unique:users,username,' . $user->id],
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Perfil atualizado com sucesso.',
            'data' => $user,
        ]);
    }

    /**
     * Alterar senha do usuário autenticado
     */
    public function changePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // Verificar se a senha atual está correta
        if (!Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'A senha atual está incorreta.',
            ]);
        }

        // Atualizar a senha
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'message' => 'Senha alterada com sucesso.',
        ]);
    }

    /**
     * Renovar o token (manter conectado / refresh token)
     * Cria um novo token e revoga o anterior para evitar token hijacking
     */
    public function refreshToken(Request $request): JsonResponse
    {
        $user = $request->user();

        // Obter o token atual
        $currentToken = $user->currentAccessToken();

        if (!$currentToken) {
            return response()->json([
                'message' => 'Nenhum token ativo encontrado.',
            ], 401);
        }

        // Criar um novo token
        $newToken = $user->createToken('api-token')->plainTextToken;

        // Revogar o token antigo para segurança
        $currentToken->delete();

        return response()->json([
            'message' => 'Token renovado com sucesso.',
            'token' => $newToken,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Deletar a conta do usuário autenticado
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        $user = $request->user();

        // Validar a senha
        $validated = $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (!Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'A senha está incorreta.',
            ]);
        }

        // Deletar todos os tokens do usuário
        $user->tokens()->delete();

        // Deletar o usuário
        $user->delete();

        return response()->json([
            'message' => 'Conta deletada com sucesso.',
        ]);
    }
}

