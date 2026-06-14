<?php

namespace App\Livewire\System;

use Livewire\Component;
use Livewire\Attributes\Layout;

class Config extends Component
{
    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.system.config');
    }
}
