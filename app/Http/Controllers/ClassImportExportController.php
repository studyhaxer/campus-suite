<?php

namespace App\Http\Controllers;

use App\Exports\ClassExport;
use App\Exports\ClassTemplateExport;
use App\Imports\ClassImport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

/**
 * NOTE: I don't have your Staff controller/Livewire component, so the
 * $isManager / $lockedCampusId resolution below is a best-guess placeholder
 * mirroring the constructor signatures used by StaffImport / StaffExport.
 * Swap resolveScope() out for whatever your Staff feature actually uses
 * (e.g. a trait, a form request, or a Livewire property) so both features
 * stay consistent. The two lines that matter are marked with TODO.
 */
class ClassImportExportController extends Controller
{
    public function downloadTemplate()
    {
        [$isManager, $organizationId, ] = $this->resolveScope();

        return (new ClassTemplateExport($isManager, $organizationId))
            ->download('class-import-template.xlsx');
    }

    public function export()
    {
        [$isManager, $organizationId, $lockedCampusId] = $this->resolveScope();

        return (new ClassExport($isManager, $organizationId, $lockedCampusId))
            ->download('classes-export-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        [$isManager, $organizationId, $lockedCampusId] = $this->resolveScope();

        $import = new ClassImport($organizationId, $isManager, $lockedCampusId);

        Excel::import($import, $request->file('file'));

        $errors = $import->errorMessages();

        if (! empty($errors)) {
            return back()
                ->with('import_success_count', $import->importedCount())
                ->with('import_class_count', $import->importedClassCount())
                ->with('import_errors', $errors);
        }

        return back()->with(
            'status',
            "Imported {$import->importedCount()} section(s) across {$import->importedClassCount()} class(es)."
        );
    }

    /**
     * @return array{0: bool, 1: int, 2: ?int} [isManager, organizationId, lockedCampusId]
     */
    protected function resolveScope(): array
    {
        $user = Auth::user();

        // TODO: replace with the same role/campus resolution logic your
        // StaffController (or equivalent) uses.
        $isManager = $user->hasRole('Manager');
        $organizationId = $user->organization_id;
        $lockedCampusId = $isManager ? null : $user->campuses()->value('campuses.id');

        return [$isManager, $organizationId, $lockedCampusId];
    }
}