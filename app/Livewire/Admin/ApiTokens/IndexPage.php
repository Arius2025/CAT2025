<?php

namespace App\Livewire\Admin\ApiTokens;

use Livewire\Component;

class IndexPage extends Component
{
    public function render()
    {
        $tokens = request()->user()
            ->tokens()
            ->orderByDesc('id')
            ->get(['id', 'name', 'abilities', 'last_used_at', 'created_at']);

        return view('livewire.admin.api-tokens.index-page', [
            'tokens' => $tokens,
            'newToken' => session('new_api_token'),
            'newTokenName' => session('new_api_token_name'),
        ]);
    }
}