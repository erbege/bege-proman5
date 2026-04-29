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

        // Users with financials.manage or projects.view.all often need global access
        if ($user->can('financials.manage') || $user->can('projects.view.all')) {
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
                // Double check: is user an owner of this project?
                if ($user->hasRole('owner') && $project->owner_id == $user->id) {
                    return $next($request);
                }
                
                abort(403, 'Anda tidak terdaftar sebagai tim dalam proyek ini.');
            }
        }

        return $next($request);
    }
}
