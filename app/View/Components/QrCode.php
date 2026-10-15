<?php

namespace App\View\Components;

use App\Support\QrCodeGenerator;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class QrCode extends Component
{
    public string $dataUri;

    /**
     * Create a new component instance.
     */
    public function __construct(
        QrCodeGenerator $generator,
        public string $value,
        public int $size = 160,
    ) {
        $this->dataUri = $generator->dataUri($value, $size);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.qr-code');
    }
}
