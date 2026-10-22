<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (empty($roles)) {
            return $next($request);
        }

        if (in_array($user->role, $roles, true)) {
            return $next($request);
        }

        // Special redirect handling for Customer according to Blueprint:
        if ($user->isCustomer()) {
            if ($request->is('products*')) {
                return redirect()->route('catalog.index')->with('warning', 'Akses khusus Staff & Admin. Anda diarahkan ke Katalog Produk.');
            }
            if ($request->is('sales*')) {
                return redirect()->route('orders.my')->with('warning', 'Akses khusus Staff & Admin. Anda diarahkan ke Riwayat Belanja Anda.');
            }
        }

        abort(403, 'Akses ditolak: Anda tidak memiliki izin untuk mengakses halaman ini.');
    }
}
