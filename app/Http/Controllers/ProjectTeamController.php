<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectTeamController extends Controller
{
    /**
     * Display the team management page for a project.
     */
    public function index(Project $project)
    {
        return view('projects.team.index', compact('project'));
    }
}
