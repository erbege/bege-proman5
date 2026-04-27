<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Project;

class EnsureUserIsProjectMember
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Superadmins and Administrators can see everything
        if ($user->hasRole(['Superadmin', 'super-admin', 'administrator'])) {
            return $next($request);
        }

        // Users with financials.manage (e.g. Estimators/PMs) often need global access
        if ($user->can('financials.manage')) {
            return $next($request);
        }

        // Get project from route
        $project = $request->route('project');

        // If it's a string (ID), find the model
        if (is_string($project) || is_numeric($project)) {
            $project = Project::find($project);
        }

        if ($project instanceof Project) {
            if (!$user->isProjectMember($project)) {
                abort(403, 'Anda tidak terdaftar sebagai tim dalam proyek ini.');
            }
        }

        return $next($request);
    }
}
