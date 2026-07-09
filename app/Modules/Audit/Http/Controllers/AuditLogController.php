<?php

declare(strict_types=1);

namespace App\Modules\Audit\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Audit\Models\AuditLog;
use Illuminate\Contracts\View\View;

final class AuditLogController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', AuditLog::class);

        return view('modules.audit.index');
    }
}
