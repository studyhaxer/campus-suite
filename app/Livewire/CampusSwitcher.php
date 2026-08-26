<?php

namespace App\Livewire;

use Livewire\Component;

class CampusSwitcher extends Component
{
    public function switchTo($campusId)
    {
        session(['current_campus_id' => $campusId]);
        return redirect(request()->header('Referer'));
    }
    public function render()
    {
        $user = auth()->user();
        $campuses = $user->hasRole('Manager') ? $user->organization?->campuses ?? collect() : $user->campuses;
        return view('livewire.campus-switcher', ['campuses' => $campuses, 'currentCampusId' => session('current_campus_id'),]);
    }
}
